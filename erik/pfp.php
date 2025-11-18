<?php
header("Content-Type: application/json");

require_once "auth.php";

$user = require_user();

if (!isset($_FILES["profile_pic"])) {
    http_response_code(400);
    echo json_encode(["message" => "No file uploaded"]);
    exit;
}

$file = $_FILES["profile_pic"];
$allowed = ["image/jpeg", "image/png", "image/webp"];

// MIME ellenőrzés
if (!in_array($file["type"], $allowed)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid image format"]);
    exit;
}

// Mappa, ha nem létezik
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Új fájlnév
$ext = pathinfo($file["name"], PATHINFO_EXTENSION);
$newName = "pfp_" . $user->id . "_" . time() . "." . $ext;

$target = $upload_dir . $newName;

// Mentés
if (!move_uploaded_file($file["tmp_name"], $target)) {
    http_response_code(500);
    echo json_encode(["message" => "Upload failed"]);
    exit;
}

// Adatbázis frissítés
global $conn;
$stmt = $conn->prepare("
    UPDATE users 
    SET profile_pic = ? 
    WHERE id = ?
");
$stmt->bind_param("si", $target, $user->id);
$stmt->execute();

echo json_encode([
    "message" => "Profile picture updated",
    "path" => $target
]);
