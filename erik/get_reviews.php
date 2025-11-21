<?php
header("Content-Type: application/json");
require "connect.php";

$product_id = $_GET["product_id"] ?? null;

if (!$product_id) {
    echo json_encode([]);
    exit;
}

$sql = "
SELECT r.rating, r.comment, r.created_at,
       u.username,
       u.profile_pic
FROM reviews r
JOIN users u ON r.user_id = u.id
WHERE r.product_id = ?
ORDER BY r.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$res = $stmt->get_result();

$reviews = [];

while ($row = $res->fetch_assoc()) {

    // Ha nincs profilkép → legyen default
    if (!$row["profile_pic"]) {
        $row["profile_pic"] = "uploads/default.png";
    } else {
        $row["profile_pic"] = "uploads/" . $row["profile_pic"];
    }

    $reviews[] = $row;
}

echo json_encode($reviews);
