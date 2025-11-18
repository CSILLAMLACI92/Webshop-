<?php
require_once "jwt_helper.php";
require_once "connect.php";

function require_user() {
    $user = verify_jwt();
    if (!$user) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid or missing token"]);
        exit;
    }
    return $user; // object (stdClass)
}

function require_admin() {
    $user = require_user();
    if (!isset($user->role) || $user->role !== "admin") {
        http_response_code(403);
        echo json_encode(["error" => "Admin access only"]);
        exit;
    }
    return $user;
}

?>
