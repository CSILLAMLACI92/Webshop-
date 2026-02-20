<?php
header("Content-Type: application/json");
require "connect.php";
require "jwt_helper.php";

$me = verify_jwt();
if (!$me) {
    echo json_encode(["error" => "Nincs token"]);
    exit;
}

$user_id = $me->id;
$product_id = $_POST["product_id"] ?? 0;

$stmt = $conn->prepare("DELETE FROM reviews WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();

echo json_encode(["success" => true]);
