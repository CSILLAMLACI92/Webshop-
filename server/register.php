<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "connect.php";
require_once "email_verification.php";
require_once "jwt_helper.php";

function is_valid_username($username, &$error = "") {
    $len = function_exists("mb_strlen") ? mb_strlen($username, "UTF-8") : strlen($username);
    if ($len < 3 || $len > 32) {
        $error = "A felhasználónév 3-32 karakter legyen.";
        return false;
    }

    if (!preg_match('/^\p{L}[\p{L}\p{N}._-]*[\p{L}\p{N}]$/u', $username)) {
        $error = "A felhasználónév betűvel kezdődjön, és csak betű/szám/._- karaktereket tartalmazzon.";
        return false;
    }

    if (preg_match('/[._-]{2,}/', $username)) {
        $error = "A felhasználónévben nem lehet két speciális jel egymás mellett.";
        return false;
    }

    preg_match_all('/\p{L}/u', $username, $letters);
    if (count($letters[0]) < 3) {
        $error = "A felhasználónévben legalább 3 betű legyen.";
        return false;
    }

    if (preg_match('/(.)\1\1\1+/u', $username)) {
        $error = "Túl sok ismétlődő karakter a felhasználónévben.";
        return false;
    }

    return true;
}

// 1) JSON beolvasA?s
// Accept JSON or form-data
$contentType = strtolower($_SERVER["CONTENT_TYPE"] ?? "");
$data = null;
if (strpos($contentType, "application/json") !== false) {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
} else {
    $data = $_POST;
}

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid data"]);
    exit;
}

// 2) Inputok kiszedAďż˝se + trim
$username = trim($data["username"] ?? "");
$email    = trim($data["email"] ?? "");
$password = (string)($data["password"] ?? "");

// 3) Alap validA?ciAl
if ($username === "" || $email === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["error" => "All fields required"]);
    exit;
}

// Szigorubb username validacio (gagyi/szemet bemenet kiszurese)
$usernameErr = "";
if (!is_valid_username($username, $usernameErr)) {
    http_response_code(400);
    echo json_encode(["error" => $usernameErr]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email"]);
    exit;
}

if (!is_email_domain_allowed($email, $ALLOWED_EMAIL_DOMAINS)) {
    http_response_code(400);
    echo json_encode(["error" => "Email domain not allowed"]);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(["error" => "Password must be at least 6 characters"]);
    exit;
}

// Ensure verification columns exist (auto-migrate if possible)
$schemaError = null;
if (!ensure_email_verification_schema($conn, $schemaError)) {
    http_response_code(500);
    echo json_encode(["error" => "Missing email verification schema", "detail" => $schemaError]);
    exit;
}
// 4) DuplikA?ciAl ellenLďż˝rzAďż˝s (username vagy email)
$check = $conn->prepare("SELECT id, email, email_verified FROM users WHERE username = ? OR email = ? LIMIT 1");
if (!$check) {
    http_response_code(500);
    echo json_encode(["error" => "DB prepare failed (check)"]);
    exit;
}

$check->bind_param("ss", $username, $email);
$check->execute();
$res = $check->get_result();

if ($res && $res->num_rows > 0) {
    $existing = $res->fetch_assoc();
    $verifyRequired = isset($EMAIL_VERIFICATION_REQUIRED) ? (bool)$EMAIL_VERIFICATION_REQUIRED : false;
    if (!$verifyRequired) {
        http_response_code(409);
        echo json_encode(["error" => "Username or email taken"]);
        exit;
    }
    if (strcasecmp($existing["email"] ?? "", $email) === 0) {
    if ((int)($existing["email_verified"] ?? 0) === 1) {
        http_response_code(409);
        echo json_encode(["error" => "Email already verified"]);
        exit;
    }

    // Unverified email re-registration: refresh password too, so user can log in
    // with the latest password they just submitted after verification.
    $rehash = password_hash($password, PASSWORD_DEFAULT);
    if ($rehash === false) {
        http_response_code(500);
        echo json_encode(["error" => "Password hashing failed"]);
        exit;
    }

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash("sha256", $token);
    $expiresAt = (new DateTime("+$EMAIL_TOKEN_TTL_HOURS hours"))->format("Y-m-d H:i:s");
    $upd = $conn->prepare("UPDATE users SET password_hash = ?, email_verification_token = ?, email_verification_expires = ? WHERE id = ?");
    if ($upd) {
        $uid = (int)$existing["id"];
        $upd->bind_param("sssi", $rehash, $tokenHash, $expiresAt, $uid);
        $upd->execute();
    }

    $sent = send_verification_email($email, $token);
    if (!$sent) {
        echo json_encode([
            "success" => true,
            "message" => "Verification email could not be sent. Use manual verification link.",
            "verification_sent" => false,
            "detail" => get_last_email_error(),
            "requires_verification" => true,
            "verification_link" => get_verification_link($token)
        ]);
        exit;
    }
    echo json_encode([
        "success" => true,
        "message" => "Verification email sent.",
        "requires_verification" => true,
        "verification_link" => get_verification_link($token)
    ]);
    exit;
}

    http_response_code(409);
    echo json_encode(["error" => "Username taken"]);
    exit;
}
// 5) JelszAl hash
$hash = password_hash($password, PASSWORD_DEFAULT);
if ($hash === false) {
    http_response_code(500);
    echo json_encode(["error" => "Password hashing failed"]);
    exit;
}

// Create verification token before insert
$token = bin2hex(random_bytes(32));
$tokenHash = hash("sha256", $token);
$expiresAt = (new DateTime("+$EMAIL_TOKEN_TTL_HOURS hours"))->format("Y-m-d H:i:s");
$verifyRequired = isset($EMAIL_VERIFICATION_REQUIRED) ? (bool)$EMAIL_VERIFICATION_REQUIRED : false;

// 6) Insert
$stmt = $verifyRequired
    ? $conn->prepare("
        INSERT INTO users (username, email, password_hash, role, profile_pic, created_at, email_verified, email_verification_token, email_verification_expires)
        VALUES (?, ?, ?, 'user', 'Default.avatar.jpg', NOW(), 0, ?, ?)
    ")
    : $conn->prepare("
        INSERT INTO users (username, email, password_hash, role, profile_pic, created_at, email_verified, email_verification_token, email_verification_expires)
        VALUES (?, ?, ?, 'user', 'Default.avatar.jpg', NOW(), 1, NULL, NULL)
    ");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "DB prepare failed (insert)"]);
    exit;
}

if ($verifyRequired) {
    $stmt->bind_param("sssss", $username, $email, $hash, $tokenHash, $expiresAt);
} else {
    $stmt->bind_param("sss", $username, $email, $hash);
}

if (!$stmt->execute()) {
    // Ha mAďż˝gis DB oldalon uniq miatt borul (pl. van UNIQUE index), itt is lekezeljALk
    if ($conn->errno === 1062) {
        http_response_code(409);
        echo json_encode(["error" => "Username or email taken"]);
        exit;
    }

    http_response_code(500);
    echo json_encode(["error" => "Database error", "code" => $conn->errno]);
    exit;
}

if (!$verifyRequired) {
    $jwt = generate_jwt([
        "id" => (int)$stmt->insert_id,
        "username" => $username,
        "email" => $email,
        "role" => "user"
    ]);
    echo json_encode([
        "success" => true,
        "message" => "Registered.",
        "requires_verification" => false,
        "token" => $jwt
    ]);
    exit;
}

// Send verification email
$sent = send_verification_email($email, $token);
if (!$sent) {
    echo json_encode([
        "success" => true,
        "message" => "Registered, but email could not be sent. Use manual verification link.",
        "verification_sent" => false,
        "requires_verification" => true,
        "detail" => get_last_email_error(),
        "verification_link" => get_verification_link($token)
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Registered. Verification email sent.",
    "verification_sent" => true,
    "requires_verification" => true,
    "verification_link" => get_verification_link($token)
]);
