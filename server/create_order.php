<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "connect.php";
require_once "jwt_helper.php";
require_once "orders_helper.php";

$user = verify_jwt();
if (!$user) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid data"]);
    exit;
}

$items = $data["items"] ?? [];
if (!is_array($items) || count($items) === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Empty cart"]);
    exit;
}

$schemaError = null;
if (!ensure_orders_schema($conn, $schemaError)) {
    http_response_code(500);
    echo json_encode(["error" => "DB schema error", "detail" => $schemaError]);
    exit;
}

$total = 0;
foreach ($items as $it) {
    $price = (int)($it["ar"] ?? 0);
    $qty = (int)($it["db"] ?? 1);
    if ($price < 0 || $qty < 1) continue;
    $total += $price * $qty;
}

$uid = (int)$user->id;
$stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'created')");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "DB insert failed"]);
    exit;
}

$stmt->bind_param("ii", $uid, $total);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "DB insert failed"]);
    exit;
}

$orderId = $conn->insert_id;
$itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, unit_price, quantity) VALUES (?, ?, ?, ?)");
if (!$itemStmt) {
    http_response_code(500);
    echo json_encode(["error" => "DB insert failed"]);
    exit;
}

foreach ($items as $it) {
    $name = trim((string)($it["nev"] ?? ""));
    $price = (int)($it["ar"] ?? 0);
    $qty = (int)($it["db"] ?? 1);
    if ($name === "" || $price < 0 || $qty < 1) continue;
    $itemStmt->bind_param("isii", $orderId, $name, $price, $qty);
    $itemStmt->execute();
}

echo json_encode([
    "success" => true,
    "order_id" => $orderId
]);

