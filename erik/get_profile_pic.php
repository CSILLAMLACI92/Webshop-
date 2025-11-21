<?php
header("Content-Type: application/json");
require "connect.php";
require "jwt_helper.php";

$headers = getallheaders();
$auth = $headers["Authorization"] ?? "";

if (!$auth) {
    echo json_encode(["status" => "no_token"]);
    exit;
}

$token = str_replace("Bearer ", "", $auth);

try {
    $payload = verify_jwt($token);
    $user_id = $payload->id;
} catch (Exception $e) {
    echo json_encode(["status" => "invalid_token"]);
    exit;
}

// Felhasználó lekérése
$stmt = $conn->prepare("SELECT username, email, profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $profile_pic);
$stmt->fetch();
$stmt->close();

$pic = $profile_pic ? "uploads/" . $profile_pic : "default.png";

echo json_encode([
    "status" => "ok",
    "username" => $username,
    "email" => $email,
    "pic" => $pic
]);
