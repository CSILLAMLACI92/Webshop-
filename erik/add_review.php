<?php
header("Content-Type: application/json");

require "connect.php";
require "jwt_helper.php";

// Token ellenőrzés
$headers = getallheaders();
$auth = $headers["Authorization"] ?? "";

if (!$auth) {
    echo json_encode(["error" => "no_token"]);
    exit;
}

$token = str_replace("Bearer ", "", $auth);

try {
    $payload = verify_jwt($token);
    $user_id = $payload->id; // 🔥 A bejelentkezett user ID-ja
} catch (Exception $e) {
    echo json_encode(["error" => "invalid_token"]);
    exit;
}

// Bemenet
$product_id = $_POST["product_id"] ?? null;
$rating     = $_POST["rating"] ?? null;
$comment    = $_POST["comment"] ?? "";

if (!$product_id || !$rating) {
    echo json_encode(["error" => "missing_data"]);
    exit;
}

$rating = intval($rating);
if ($rating < 1 || $rating > 5) {
    echo json_encode(["error" => "invalid_rating"]);
    exit;
}

// Mentés adatbázisba
$stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $user_id, $product_id, $rating, $comment);
$stmt->execute();

echo json_encode(["status" => "ok", "message" => "review added"]);
