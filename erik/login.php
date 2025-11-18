<?php

header("Content-Type: application/json");

// ==== CONNECT.PHP BETÖLTÉSE FIX PATH-AL ====
require_once __DIR__ . "/connect.php";

// ==== CONNECT DEBUG ====
file_put_contents("debug_conn.txt", "CONNECT.PHP INCLUDED\n", FILE_APPEND);
file_put_contents("debug_conn.txt", "HOST: " . ($host ?? "NULL") . "\n", FILE_APPEND);
file_put_contents("debug_conn.txt", "USER: " . ($user ?? "NULL") . "\n", FILE_APPEND);
file_put_contents("debug_conn.txt", "DBNAME: " . ($dbname ?? "NULL") . "\n", FILE_APPEND);
file_put_contents("debug_conn.txt", "PORT: " . ($port ?? "NULL") . "\n", FILE_APPEND);

// ==== JWT BETÖLTÉSE ====
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
    SELECT id, username, email, password_hash, role
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

// ==== PASSWORD CHECK ====
if (!password_verify($password, $user["password_hash"])) {
    http_response_code(401);
    echo json_encode(["error" => "Wrong password"]);
    exit;
}

// ==== JWT GENERÁLÁS ====
$token = generate_jwt([
    "id"       => $user["id"],
    "username" => $user["username"],
    "email"    => $user["email"],
    "role"     => $user["role"]
]);

// ==== VÁLASZ ====
echo json_encode([
    "message" => "Login OK",
    "token"   => $token,
    "role"    => $user["role"]
]);

exit;
