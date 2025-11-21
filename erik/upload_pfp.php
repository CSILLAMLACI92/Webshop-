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

if (!isset($_FILES["profile_pic"])) {
    echo json_encode(["status" => "error", "message" => "No file"]);
    exit;
}

$file = $_FILES["profile_pic"];
$allowed = ["image/jpeg", "image/png", "image/webp"];

if (!in_array($file["type"], $allowed)) {
    echo json_encode(["status" => "error", "message" => "Invalid file"]);
    exit;
}

// uploads mappa biztos legyen
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// új fájlnév
$ext = pathinfo($file["name"], PATHINFO_EXTENSION);
$new = "pfp_" . $user_id . "_" . time() . "." . $ext;
$path = $upload_dir . $new;

// mozgatás
move_uploaded_file($file["tmp_name"], $path);

// adatbázis frissítés
$stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
$stmt->bind_param("si", $new, $user_id);
$stmt->execute();

echo json_encode([
    "status" => "ok",
    "pic" => $path . "?t=" . time()
]);
