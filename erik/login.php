<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . "/connect.php";
require_once __DIR__ . "/jwt_helper.php";

// ==== RAW JSON ====
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$login = trim($data["login"] ?? "");
$password = trim($data["password"] ?? "");

if ($login === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["error" => "Missing login or password"]);
    exit;
}

// ==== USER LEKÉRDEZÉS ====
$stmt = $conn->prepare("
    SELECT id, username, email, password_hash, role, profile_pic
    FROM users
    WHERE username = ? OR email = ?
");
$stmt->bind_param("ss", $login, $login);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "User not found"]);
    exit;
}

$user = $res->fetch_assoc();

// ==== PASSWORD ELLENŐRZÉS ====
if (!password_verify($password, $user["password_hash"])) {
    http_response_code(401);
    echo json_encode(["error" => "Wrong password"]);
    exit;
}

// ==== JWT TOKEN GENERÁLÁS ====
$payload = [
    "id"       => $user["id"],
    "username" => $user["username"],
    "email"    => $user["email"],
    "role"     => $user["role"],
    "exp"      => time() + 60 * 60 * 24 * 30 // 30 nap
];

$token = generate_jwt($payload);

// ==== TOKEN VISSZAKÜLDÉSE A FRONTENDNEK ====
echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "token"   => $token,             // <--- EZ HIÁNYZOTT NÁLAD
    "user"    => [
        "id"         => $user["id"],
        "username"   => $user["username"],
        "email"      => $user["email"],
        "role"       => $user["role"],
        "profile_pic"=> $user["profile_pic"]
    ]
]);

exit;
