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

$schemaError = null;
if (!ensure_orders_schema($conn, $schemaError)) {
    http_response_code(500);
    echo json_encode(["error" => "DB schema error", "detail" => $schemaError]);
    exit;
}

$uid = (int)$user->id;
$orders = [];

$stmt = $conn->prepare("SELECT id, total, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 50");
if ($stmt) {
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $orders[$row["id"]] = $row + ["items" => []];
    }
}

if (count($orders) > 0) {
    $ids = implode(",", array_map("intval", array_keys($orders)));
    $itemsRes = $conn->query("SELECT order_id, product_name, unit_price, quantity FROM order_items WHERE order_id IN ($ids)");
    if ($itemsRes) {
        while ($it = $itemsRes->fetch_assoc()) {
            $oid = (int)$it["order_id"];
            if (isset($orders[$oid])) {
                $orders[$oid]["items"][] = $it;
            }
        }
    }
}

echo json_encode(["orders" => array_values($orders)]);

