<?php
header("Content-Type: application/json");
require "connect.php";
require "jwt_helper.php";

// ==== JWT TOKEN CHECK ====
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

// ==== FILE CHECK ====
if (!isset($_FILES["profile_pic"]) || $_FILES["profile_pic"]["error"] !== 0) {
    echo json_encode(["status" => "no_file", "message" => "No file uploaded"]);
    exit;
}

$file = $_FILES["profile_pic"];
$allowed = ["image/jpeg", "image/png", "image/webp"];

if (!in_array($file["type"], $allowed)) {
    echo json_encode(["status" => "invalid_file", "message" => "Invalid file type"]);
    exit;
}

// ==== UPLOAD FOLDER ====
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ==== CREATE FILENAME ====
$ext = pathinfo($file["name"], PATHINFO_EXTENSION);
$newName = "pfp_" . $user_id . "_" . time() . "." . $ext;
$target_path = $upload_dir . $newName;

// ==== MOVE FILE ====
if (!move_uploaded_file($file["tmp_name"], $target_path)) {
    echo json_encode(["status" => "error", "message" => "File upload failed"]);
    exit;
}

// ==== UPDATE DATABASE ====
$stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
$stmt->bind_param("si", $newName, $user_id);
$stmt->execute();

// ==== RESPONSE ====
echo json_encode([
    "status" => "ok",
    "message" => "Profile updated",
    "pic" => $upload_dir . $newName
]);
