<?php
header("Content-Type: application/json; charset=UTF-8");
require "connect.php";
require "jwt_helper.php";

function app_base_path() {
    $script = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");
    $serverDir = rtrim(dirname($script), "/");
    $base = dirname($serverDir);
    if ($base === "." || $base === "\\" || $base === "/") {
        return "";
    }
    return rtrim(str_replace("\\", "/", $base), "/");
}

function normalize_profile_pic_path($rawPic) {
    $profilePic = trim((string)$rawPic);
    $pl = strtolower(basename($profilePic));
    if (in_array($pl, ["default.png", "default_avatar.png", "default.avatar.jpg"], true)) {
        $profilePic = "Default.avatar.jpg";
    }

    if ($profilePic === "") {
        $pic = "/uploads/Default.avatar.jpg";
    } else if (preg_match("#^https?://#i", $profilePic)) {
        return $profilePic;
    } else if (strpos($profilePic, "/uploads/") === 0) {
        $pic = $profilePic;
    } else if (strpos($profilePic, "../uploads/") === 0) {
        $pic = "/uploads/" . ltrim(substr($profilePic, strlen("../uploads/")), "/");
    } else if (strpos($profilePic, "uploads/") === 0) {
        $pic = "/" . $profilePic;
    } else {
        $pic = "/uploads/" . ltrim($profilePic, "/");
    }

    $base = app_base_path();
    if ($base !== "" && strpos($pic, "/uploads/") === 0) {
        return $base . $pic;
    }

    return $pic;
}

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

$stmt = $conn->prepare("SELECT username, email, profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $profile_pic);
$stmt->fetch();
$stmt->close();

$pic = normalize_profile_pic_path($profile_pic);

echo json_encode([
    "status" => "ok",
    "username" => $username,
    "email" => $email,
    "pic" => $pic
], JSON_UNESCAPED_UNICODE);
