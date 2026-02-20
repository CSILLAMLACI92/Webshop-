<?php
ini_set('display_errors', 0);
error_reporting(0);
header("Content-Type: application/json; charset=utf-8");
require "connect.php";
require "profanity.php";

$product_id = intval($_GET["product_id"] ?? 0);

if ($product_id <= 0) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
SELECT 
    r.id,
    r.product_id,
    r.user_id,
    r.rating,
    r.comment,
    r.created_at,
    u.username,
    u.profile_pic
FROM reviews r
JOIN users u ON r.user_id = u.id
WHERE r.product_id = ?
ORDER BY r.created_at DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param("i", $product_id);

if (!$stmt->execute()) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}

$res = $stmt->get_result();
$reviews = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        // profile_pic normalizálás: abszolút web útvonal
        $p = trim((string)($row["profile_pic"] ?? ""));
        $pl = strtolower(basename($p));
        if (in_array($pl, ["default.png", "default_avatar.png", "default.avatar.jpg"], true)) {
            $p = "Default.avatar.jpg";
        }
        if ($p === "") {
            $row["profile_pic"] = "/uploads/Default.avatar.jpg";
        } else if (preg_match("#^https?://#i", $p)) {
            $row["profile_pic"] = $p;
        } else if (strpos($p, "/uploads/") === 0) {
            $row["profile_pic"] = $p;
        } else if (strpos($p, "../uploads/") === 0) {
            $row["profile_pic"] = "/uploads/" . ltrim(substr($p, strlen("../uploads/")), "/");
        } else if (strpos($p, "uploads/") === 0) {
            $row["profile_pic"] = "/" . $p;
        } else {
            $row["profile_pic"] = "/uploads/" . $p;
        }

        $row["comment"] = sanitize_profanity_text((string)($row["comment"] ?? ""));
        $reviews[] = $row;
    }
}

$stmt->close();

echo json_encode($reviews, JSON_UNESCAPED_UNICODE);
