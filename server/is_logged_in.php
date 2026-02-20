<?php
header("Content-Type: application/json");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$token = $data["token"] ?? "";

require_once "jwt_helper.php";

try {
    $payload = verify_jwt($token);  // <-- EZ ÉRVÉNYESSÉ TESZI
    echo json_encode(["logged_in" => true]);
} 
catch (Exception $e) {
    echo json_encode(["logged_in" => false]);
}
