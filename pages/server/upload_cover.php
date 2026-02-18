<?php
header("Content-Type: application/json; charset=UTF-8");
ini_set("display_errors", "0");
ini_set("display_startup_errors", "0");
error_reporting(0);
set_error_handler(function () {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Szerver hiba feltöltés közben."]);
    exit;
});

require "connect.php";
require "jwt_helper.php";
require "market_schema.php";

$schemaError = ensure_market_schema($conn);
if ($schemaError) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Schema error"]);
    exit;
}

$payload = verify_jwt();
if (!$payload || !isset($payload->id)) {
    echo json_encode(["status" => "invalid_token"]);
    exit;
}

if (!isset($_FILES["cover"])) {
    echo json_encode(["status" => "error", "message" => "Nincs fájl feltöltve"]);
    exit;
}

$file = $_FILES["cover"];
$allowed = ["image/jpeg", "image/png", "image/webp"];
if (!in_array($file["type"], $allowed)) {
    echo json_encode(["status" => "error", "message" => "Érvénytelen fájltípus"]);
    exit;
}

$upload_dir = realpath(__DIR__ . "/../uploads");
if ($upload_dir === false) {
    $upload_dir = __DIR__ . "/../uploads";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0775, true);
    }
    $upload_dir = realpath($upload_dir);
}
if ($upload_dir === false) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Uploads mappa nem elerheto"]);
    exit;
}
$upload_dir = rtrim($upload_dir, "/\\") . DIRECTORY_SEPARATOR;
if (!is_writable($upload_dir)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Uploads mappa nem irhato."]);
    exit;
}

$ext = pathinfo($file["name"], PATHINFO_EXTENSION);
$new = "cover_" . (int)$payload->id . "_" . time() . "_" . bin2hex(random_bytes(3)) . "." . $ext;
$path = $upload_dir . $new;

if (!move_uploaded_file($file["tmp_name"], $path)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Fájl feltöltés sikertelen"]);
    exit;
}

$url = "/uploads/" . $new;
$stmt = $conn->prepare("INSERT INTO profile_meta (user_id, cover_url) VALUES (?, ?) ON DUPLICATE KEY UPDATE cover_url = VALUES(cover_url)");
$stmt->bind_param("is", $payload->id, $url);
$stmt->execute();

echo json_encode(["status" => "ok", "url" => $url]);
