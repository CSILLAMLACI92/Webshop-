<?php
header("Content-Type: application/json");

require_once "auth.php";

$user = require_user();

$stmt = $conn->prepare("SELECT id, username, email, role, profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user->id);
$stmt->execute();

echo json_encode(["user" => $stmt->get_result()->fetch_assoc()]);
