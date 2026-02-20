<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../server/connect.php";
require_once __DIR__ . "/../server/jwt_helper.php";

$me = verify_jwt();
if (!$me) {
    echo json_encode(["error" => "no_token"]);
    exit;
}

$action = trim($_POST["action"] ?? "");
if ($action === "") {
    echo json_encode(["error" => "missing_action"]);
    exit;
}

$user_id = $me->id;
$stmt = $conn->prepare("INSERT INTO user_activity (user_id, action) VALUES (?, ?)");
if (!$stmt) {
    echo json_encode(["error" => "db_prepare_failed"]);
    exit;
}

$stmt->bind_param("is", $user_id, $action);
$stmt->execute();

echo json_encode(["success" => true]);
