<?php
header("Content-Type: application/json");

require_once "connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$username = trim($data["username"] ?? "");
$email = trim($data["email"] ?? "");
$password = trim($data["password"] ?? "");

if ($username === "" || $email === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["error" => "All fields required"]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username,email,password_hash,role,profile_pic,created_at) VALUES (?,?,?,'user','uploads/default_avatar.png',NOW())");
$stmt->bind_param("sss", $username, $email, $hash);

if (!$stmt->execute()) {
    http_response_code(409);
    echo json_encode(["error" => "Username or email taken"]);
    exit;
}

echo json_encode(["message" => "Registered"]);
