<?php
header("Content-Type: application/json");

require_once "auth.php";

$admin = require_admin();

$action = $_GET["action"] ?? "list";

if ($action === "list") {
    $res = $conn->query("SELECT id,username,email,role,profile_pic FROM users ORDER BY id DESC");
    $out = [];
    while ($row = $res->fetch_assoc()) $out[] = $row;
    echo json_encode($out);
    exit;
}

if ($action === "delete") {
    $id = intval($_GET["id"] ?? 0);
    if ($id === 0) {
        echo json_encode(["error" => "Missing id"]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(["message" => "User deleted"]);
    exit;
}

echo json_encode(["error" => "Invalid action"]);
