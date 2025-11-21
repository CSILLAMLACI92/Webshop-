<?php
header("Content-Type: application/json");

require_once "auth.php";

$user = require_user();

$stmt = $conn->prepare("SELECT id, username, email, role, profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user->id);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

// 🔥 PROFILKÉP FIX
// Ha nincs kép → default.png
// Ha van kép → "uploads/..." elé tesszük az útvonalat
$rawPic = $data["profile_pic"];
$fullPic = $rawPic ? "uploads/" . $rawPic : "uploads/default.png";

echo json_encode([
    "success" => true,
    "user" => [
        "id" => $data["id"],
        "username" => $data["username"],
        "email" => $data["email"],
        "role" => $data["role"],
        "profile_pic" => $fullPic
    ]
]);
