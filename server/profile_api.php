<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "auth.php";

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
    $rawPic = trim((string)$rawPic);
    $rawLower = strtolower(basename($rawPic));
    if (in_array($rawLower, ["default.png", "default_avatar.png", "default.avatar.jpg"], true)) {
        $rawPic = "Default.avatar.jpg";
    }

    if ($rawPic === "") {
        $fullPic = "/uploads/Default.avatar.jpg";
    } elseif (preg_match("#^https?://#i", $rawPic)) {
        return $rawPic;
    } elseif (strpos($rawPic, "/uploads/") === 0) {
        $fullPic = $rawPic;
    } elseif (strpos($rawPic, "../uploads/") === 0) {
        $fullPic = "/uploads/" . ltrim(substr($rawPic, strlen("../uploads/")), "/");
    } elseif (strpos($rawPic, "uploads/") === 0) {
        $fullPic = "/" . $rawPic;
    } else {
        $fullPic = "/uploads/" . ltrim($rawPic, "/");
    }

    $base = app_base_path();
    if ($base !== "" && strpos($fullPic, "/uploads/") === 0) {
        return $base . $fullPic;
    }

    return $fullPic;
}

$user = require_user();

$stmt = $conn->prepare("SELECT ID AS id, username, email, role, profile_pic FROM users WHERE ID=?");
$stmt->bind_param("i", $user->id);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();
$fullPic = normalize_profile_pic_path($data["profile_pic"] ?? "");

echo json_encode([
    "success" => true,
    "user" => [
        "id" => $data["id"],
        "username" => $data["username"],
        "email" => $data["email"],
        "role" => $data["role"],
        "profile_pic" => $fullPic
    ]
], JSON_UNESCAPED_UNICODE);
