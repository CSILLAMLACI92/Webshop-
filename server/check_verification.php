<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "connect.php";
require_once "email_verification.php";

$email = trim($_GET["email"] ?? "");
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email"]);
    exit;
}

$schemaError = null;
if (!ensure_email_verification_schema($conn, $schemaError)) {
    http_response_code(500);
    echo json_encode(["error" => "Missing email verification schema", "detail" => $schemaError]);
    exit;
}

$stmt = $conn->prepare("SELECT email_verified FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "DB prepare failed"]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) {
    http_response_code(404);
    echo json_encode(["error" => "Email not found"]);
    exit;
}

echo json_encode(["verified" => ((int)$row["email_verified"] === 1)]);