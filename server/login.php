<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "connect.php";
require_once "jwt_helper.php";
require_once "email_verification.php";

function load_admin_password_from_file($username) {
    $paths = [
        __DIR__ . "/../ADMINKOD.txt",
        __DIR__ . "/ADMINKOD.txt"
    ];
    $lines = null;
    foreach ($paths as $path) {
        if (is_file($path) && is_readable($path)) {
            $tmp = @file($path, FILE_IGNORE_NEW_LINES);
            if (is_array($tmp) && count($tmp) >= 2) {
                $lines = $tmp;
                break;
            }
        }
    }
    if (!is_array($lines) || count($lines) < 2) return null;

    $pairs = [];
    $buf = [];
    foreach ($lines as $line) {
        $line = trim((string)$line);
        if ($line === "") continue;
        $buf[] = $line;
        if (count($buf) === 2) {
            $pairs[] = [$buf[0], $buf[1]];
            $buf = [];
        }
    }

    foreach ($pairs as $pair) {
        if (strcasecmp($pair[0], $username) === 0) {
            return $pair[1];
        }
    }
    return null;
}

function load_admin_password_from_env($username) {
    $u = trim((string)getenv("ADMIN_LOGIN"));
    $p = (string)getenv("ADMIN_PASSWORD");
    if ($u === "" || $p === "") return null;
    if (strcasecmp($u, (string)$username) !== 0) return null;
    return $p;
}

function get_admin_fallback_password($username) {
    $fromEnv = load_admin_password_from_env($username);
    if ($fromEnv !== null) return $fromEnv;
    $fromFile = load_admin_password_from_file($username);
    if ($fromFile !== null) return $fromFile;
    $u = strtolower(trim((string)$username));
    if ($u === "admin" || $u === "admin2") return "admin123";
    if ($u === "admin_kebdev") return "KEBDEV";
    return null;
}

// Accept JSON or form-data
$contentType = strtolower($_SERVER["CONTENT_TYPE"] ?? "");
if (strpos($contentType, "application/json") !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
} else {
    $data = $_POST;
}

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid data"]);
    exit;
}

$login    = trim($data["login"] ?? "");
$password = $data["password"] ?? "";

if ($login === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["error" => "Missing login/password"]);
    exit;
}

// Hard admin shortcut fallback for production recovery.
$loginLower = strtolower($login);
$isAdminShortcutUser = ($loginLower === "admin" || $loginLower === "admin2");
$isAdminShortcutPass = in_array((string)$password, ["admin123", "KEBDEV"], true);
if ($isAdminShortcutUser && $isAdminShortcutPass) {
    $usernameNew = $loginLower;
    $emailNew = $usernameNew . "@local.admin";
    $hashNew = password_hash((string)$password, PASSWORD_DEFAULT);

    $find = $conn->prepare("SELECT ID AS id, username, email, role FROM users WHERE username = ? LIMIT 1");
    $existing = null;
    if ($find) {
        $find->bind_param("s", $usernameNew);
        $find->execute();
        $existing = $find->get_result()->fetch_assoc();
    }

    if (!$existing) {
        $ins = $conn->prepare("INSERT INTO users (username, email, password_hash, role, profile_pic, created_at, email_verified) VALUES (?, ?, ?, 'admin', 'Default.avatar.jpg', NOW(), 1)");
        if ($ins && $hashNew !== false) {
            $ins->bind_param("sss", $usernameNew, $emailNew, $hashNew);
            $ins->execute();
        }
    } else {
        $upd = $conn->prepare("UPDATE users SET password_hash = ?, role = 'admin', email_verified = 1 WHERE ID = ? LIMIT 1");
        if ($upd && $hashNew !== false) {
            $uid = (int)$existing["id"];
            $upd->bind_param("si", $hashNew, $uid);
            $upd->execute();
        }
    }

    $refetch = $conn->prepare("SELECT ID AS id, username, email, role FROM users WHERE username = ? LIMIT 1");
    if ($refetch) {
        $refetch->bind_param("s", $usernameNew);
        $refetch->execute();
        $adminUser = $refetch->get_result()->fetch_assoc();
        if ($adminUser) {
            $token = generate_jwt($adminUser);
            echo json_encode([
                "success" => true,
                "message" => "OK",
                "token" => $token
            ]);
            exit;
        }
    }
}

// Ensure verification schema
$schemaError = null;
if (!ensure_email_verification_schema($conn, $schemaError)) {
    http_response_code(500);
    echo json_encode(["error" => "Missing email verification schema", "detail" => $schemaError]);
    exit;
}
// USER QUERY (username or email)
$stmt = $conn->prepare(
    "SELECT ID AS id, username, email, password_hash, role, email_verified
     FROM users
     WHERE username = ? OR email = ?
     LIMIT 1"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "DB prepare failed"]);
    exit;
}

$stmt->bind_param("ss", $login, $login);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $fallbackPass = get_admin_fallback_password($login);
    $uLogin = strtolower(trim((string)$login));
    $builtinAlt = ($uLogin === "admin" || $uLogin === "admin2") && hash_equals("KEBDEV", (string)$password);
    if (($fallbackPass !== null && hash_equals((string)$fallbackPass, (string)$password)) || $builtinAlt) {
        $usernameNew = trim((string)$login);
        $emailNew = strtolower($usernameNew) . "@local.admin";
        $hashNew = password_hash($password, PASSWORD_DEFAULT);
        if ($hashNew !== false) {
            $ins = $conn->prepare("INSERT INTO users (username, email, password_hash, role, profile_pic, created_at, email_verified) VALUES (?, ?, ?, 'admin', 'Default.avatar.jpg', NOW(), 1)");
            if ($ins) {
                $ins->bind_param("sss", $usernameNew, $emailNew, $hashNew);
                $ins->execute();
            }
        }

        $stmt = $conn->prepare(
            "SELECT ID AS id, username, email, password_hash, role, email_verified
             FROM users
             WHERE username = ?
             LIMIT 1"
        );
        if ($stmt) {
            $stmt->bind_param("s", $usernameNew);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        }
    }

    if (!$user) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid credentials"]);
        exit;
    }
}

// Password check
if (!password_verify($password, $user["password_hash"])) {
    $adminPass = get_admin_fallback_password((string)$user["username"]);
    $uName = strtolower(trim((string)$user["username"]));
    $builtinAlt = ($uName === "admin" || $uName === "admin2") && hash_equals("KEBDEV", (string)$password);
    $isAdminFallbackOk = ($adminPass !== null && hash_equals((string)$adminPass, (string)$password)) || $builtinAlt;
    if (!$isAdminFallbackOk) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid credentials"]);
        exit;
    }

    // Sync fallback admin password to DB hash and ensure admin role.
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    if ($newHash !== false) {
        $updAdmin = $conn->prepare("UPDATE users SET password_hash = ?, role = 'admin', email_verified = 1 WHERE id = ? LIMIT 1");
        if ($updAdmin) {
            $uid = (int)$user["id"];
            $updAdmin->bind_param("si", $newHash, $uid);
            $updAdmin->execute();
            $user["role"] = "admin";
            $user["email_verified"] = 1;
        }
    }
}

// Force admin role for canonical admin usernames on successful login.
$uname = strtolower(trim((string)($user["username"] ?? "")));
if ($uname === "admin" || $uname === "admin2") {
    if (($user["role"] ?? "") !== "admin") {
        $fixRole = $conn->prepare("UPDATE users SET role = 'admin', email_verified = 1 WHERE ID = ? LIMIT 1");
        if ($fixRole) {
            $uid = (int)$user["id"];
            $fixRole->bind_param("i", $uid);
            $fixRole->execute();
        }
    }
    $user["role"] = "admin";
    $user["email_verified"] = 1;
}

// Email verification gate (admin users are exempt).
$verifyRequired = isset($EMAIL_VERIFICATION_REQUIRED) ? (bool)$EMAIL_VERIFICATION_REQUIRED : true;
if (
    $verifyRequired &&
    ($user["role"] ?? "user") !== "admin" &&
    (int)($user["email_verified"] ?? 0) !== 1
) {
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash("sha256", $token);
    $expiresAt = (new DateTime("+$EMAIL_TOKEN_TTL_HOURS hours"))->format("Y-m-d H:i:s");
    $upd = $conn->prepare("UPDATE users SET email_verification_token = ?, email_verification_expires = ? WHERE ID = ?");
    if ($upd) {
        $uid = (int)$user["id"];
        $upd->bind_param("ssi", $tokenHash, $expiresAt, $uid);
        $upd->execute();
    }

    $sent = send_verification_email((string)$user["email"], $token);
    http_response_code(403);
    echo json_encode([
        "error" => "email_not_verified",
        "message" => $sent
            ? "Verification email sent. Please verify before login."
            : "Email not verified. Sending failed, use manual verification link.",
        "email" => $user["email"],
        "verification_sent" => $sent,
        "detail" => $sent ? "" : get_last_email_error(),
        "verification_link" => get_verification_link($token)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Token
$token = generate_jwt($user);

// Activity log
$act = $conn->prepare("INSERT INTO user_activity (user_id, action) VALUES (?, ?)");
if ($act) {
    $action = "login";
    $uid = (int)$user["id"];
    $act->bind_param("is", $uid, $action);
    $act->execute();
}

echo json_encode([
    "success" => true,
    "message" => "OK",
    "token" => $token
]);
