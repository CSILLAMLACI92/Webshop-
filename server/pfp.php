<?php
header("Content-Type: application/json; charset=UTF-8");
// Never leak PHP warnings/notices to the client
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
if (!isset($_FILES["profile_pic"])) {
    http_response_code(400);
    echo json_encode(["status" => "no_file", "message" => "Nincs fájl feltöltve"]);
    exit;
}

if ($_FILES["profile_pic"]["error"] !== 0) {
    $err = $_FILES["profile_pic"]["error"];
    $msg = "Ismeretlen feltöltési hiba";
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        $msg = "A fájl túl nagy. Növeld a feltöltési limitet a szerveren.";
    } elseif ($err === UPLOAD_ERR_PARTIAL) {
        $msg = "A fájl csak részben töltődött fel.";
    } elseif ($err === UPLOAD_ERR_NO_FILE) {
        $msg = "Nincs fájl feltöltve.";
    }
    http_response_code(400);
    echo json_encode(["status" => "upload_error", "message" => $msg, "code" => $err]);
    exit;
}

$file = $_FILES["profile_pic"];
$allowed = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp",
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file["tmp_name"]);
finfo_close($finfo);

if (!isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Csak jpg/png/webp engedélyezett"]);
    exit;
}

// ==== UPLOAD FOLDER ====
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

// ==== CREATE FILENAME ====
$ext = $allowed[$mime];
$newName = "pfp_" . $user_id . "_" . time() . "." . $ext;
$target_path = $upload_dir . basename($newName);

// ==== MOVE FILE ====
if (!move_uploaded_file($file["tmp_name"], $target_path)) {
    error_log("Upload failed to " . $target_path);
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Fájl feltöltés sikertelen"]);
    exit;
}

// ==== UPDATE DATABASE ====
$stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
$stmt->bind_param("si", $newName, $user_id);
$stmt->execute();

$act = $conn->prepare("INSERT INTO user_activity (user_id, action) VALUES (?, ?)");
if ($act) {
    $action = "profile_pic_update";
    $act->bind_param("is", $user_id, $action);
    $act->execute();
}

// ==== RESPONSE ====
echo json_encode([
    "status" => "ok",
    "message" => "Profilkép frissítve",
    "pic" => "/uploads/" . $newName
]);
