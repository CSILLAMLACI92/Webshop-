<?php
require "connect.php";
require "jwt_helper.php";
require "profanity.php";

header("Content-Type: application/json");

$user = verify_jwt();
if (!$user) {
    echo json_encode(["error" => "Token hibás"]);
    exit;
}

$user_id = $user->id;
$product_id = $_POST["product_id"] ?? 0;
$rating = $_POST["rating"] ?? 0;
$comment = sanitize_profanity_text(trim($_POST["comment"] ?? ""));

if (!$product_id || !$comment) {
    echo json_encode(["error" => "Hiányzó adat"]);
    exit;
}

// UNIQUE ellenőrzés
$chk = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
$chk->bind_param("ii", $user_id, $product_id);
$chk->execute();
$res = $chk->get_result();

if ($res->num_rows > 0) {
    echo json_encode(["error" => "already_exists"]);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO reviews (product_id, user_id, rating, comment)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
$stmt->execute();

$act = $conn->prepare("INSERT INTO user_activity (user_id, action) VALUES (?, ?)");
if ($act) {
    $action = "review_add";
    $act->bind_param("is", $user_id, $action);
    $act->execute();
}

echo json_encode(["success" => true]);
?>
