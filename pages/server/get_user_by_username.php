<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "connect.php";

$username = trim($_GET["username"] ?? "");
if ($username === "") {
    http_response_code(400);
    echo json_encode(["error" => "Missing username"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, username, profile_pic FROM users WHERE username = ? LIMIT 1");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "DB prepare failed"]);
    exit;
}
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "User not found"]);
    exit;
}
$row = $res->fetch_assoc();

// Normalize profile pic
$rawPic = trim((string)($row["profile_pic"] ?? ""));
$rawLower = strtolower(basename($rawPic));
if (in_array($rawLower, ["default.png", "default_avatar.png", "default.avatar.jpg"], true)) {
    $rawPic = "Default.avatar.jpg";
}
if ($rawPic === "") {
    $fullPic = "/uploads/Default.avatar.jpg";
} elseif (preg_match("#^https?://#i", $rawPic)) {
    $fullPic = $rawPic;
} elseif (strpos($rawPic, "/uploads/") === 0) {
    $fullPic = $rawPic;
} elseif (strpos($rawPic, "../uploads/") === 0) {
    $fullPic = "/uploads/" . ltrim(substr($rawPic, strlen("../uploads/")), "/");
} else {
    $fullPic = "/uploads/" . $rawPic;
}

echo json_encode([
    "success" => true,
    "user" => [
        "id" => (int)$row["id"],
        "username" => $row["username"],
        "profile_pic" => $fullPic
    ]
]);
