<?php
// ===== Egyszerű JWT composer nélkül =====

const JWT_SECRET = "VALAMI_ERŐS_TITKOS_KULCS";
const JWT_EXP    = 3600; // 1 óra

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function generate_jwt(array $user): string {
    $header = [
        "alg" => "HS256",
        "typ" => "JWT"
    ];

    $payload = [
        "iat"  => time(),
        "exp"  => time() + JWT_EXP,
        "data" => [
            "id"       => (int)$user["id"],
            "username" => $user["username"],
            "email"    => $user["email"],
            "role"     => $user["role"] ?? "user"
        ]
    ];

    $h = base64url_encode(json_encode($header));
    $p = base64url_encode(json_encode($payload));

    $signature = hash_hmac("sha256", $h . "." . $p, JWT_SECRET, true);
    $s = base64url_encode($signature);

    return $h . "." . $p . "." . $s;
}

function get_token(): ?string {
    $headers = function_exists("apache_request_headers")
        ? apache_request_headers()
        : (function_exists("getallheaders") ? getallheaders() : []);

    foreach ($headers as $k => $v) {
        if (strtolower($k) === "authorization") {
            if (preg_match('/Bearer\s+(\S+)/', $v, $m)) {
                return $m[1];
            }
        }
    }
    return null;
}

function verify_jwt() {
    $token = get_token();
    if (!$token) return null;

    $parts = explode(".", $token);
    if (count($parts) !== 3) return null;

    list($h64, $p64, $s64) = $parts;

    $header  = json_decode(base64url_decode($h64), true);
    $payload = json_decode(base64url_decode($p64), true);
    $sig     = base64url_decode($s64);

    if (!is_array($header) || !is_array($payload)) return null;

    // Aláírás ellenőrzés
    $valid = hash_hmac("sha256", $h64 . "." . $p64, JWT_SECRET, true);
    if (!hash_equals($valid, $sig)) return null;

    // Lejárt token
    if (($payload["exp"] ?? 0) < time()) return null;

    return (object)$payload["data"];
}
