<?php
// ===== Egyszerű JWT helper =====

const JWT_SECRET = "VALAMI_NAGYON_EROS_RANDOM_TITKOS_KULCS_123456789";
const JWT_EXP = 86400; // 24 óra

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

// 🔑 TOKEN GENERÁLÁS (USER TÖMBBŐL)
function generate_jwt(array $user): string {
    $header = [
        "alg" => "HS256",
        "typ" => "JWT"
    ];

    $payload = [
        "iat" => time(),
        "exp" => time() + JWT_EXP,
        "data" => [
            "id" => (int)$user["id"],
            "username" => $user["username"],
            "email" => $user["email"],
            "role" => $user["role"] ?? "user"
        ]
    ];

    $h = base64url_encode(json_encode($header));
    $p = base64url_encode(json_encode($payload));

    $sig = hash_hmac("sha256", "$h.$p", JWT_SECRET, true);
    $s = base64url_encode($sig);

    return "$h.$p.$s";
}

// 🔍 TOKEN KIOLVASÁS HEADERBŐL
function get_token(): ?string {
    $candidates = [];

    if (function_exists("getallheaders")) {
        $headers = getallheaders();
        if (is_array($headers)) {
            foreach ($headers as $k => $v) {
                if (strtolower((string)$k) === "authorization" && is_string($v)) {
                    $candidates[] = $v;
                }
            }
        }
    }

    if (function_exists("apache_request_headers")) {
        $headers = apache_request_headers();
        if (is_array($headers)) {
            foreach ($headers as $k => $v) {
                if (strtolower((string)$k) === "authorization" && is_string($v)) {
                    $candidates[] = $v;
                }
            }
        }
    }

    if (!empty($_SERVER["HTTP_AUTHORIZATION"])) {
        $candidates[] = (string)$_SERVER["HTTP_AUTHORIZATION"];
    }
    if (!empty($_SERVER["REDIRECT_HTTP_AUTHORIZATION"])) {
        $candidates[] = (string)$_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
    }
    if (!empty($_SERVER["Authorization"])) {
        $candidates[] = (string)$_SERVER["Authorization"];
    }

    foreach ($candidates as $value) {
        if (preg_match('/Bearer\s+(\S+)/i', $value, $m)) {
            return $m[1];
        }
    }

    return null;
}

// ✅ TOKEN ELLENŐRZÉS
function verify_jwt() {
    $token = get_token();
    if (!$token) return null;

    $parts = explode(".", $token);
    if (count($parts) !== 3) return null;

    [$h64, $p64, $s64] = $parts;

    $payload = json_decode(base64url_decode($p64), true);
    if (!$payload) return null;

    $valid = hash_hmac("sha256", "$h64.$p64", JWT_SECRET, true);
    if (!hash_equals($valid, base64url_decode($s64))) return null;

    if (($payload["exp"] ?? 0) < time()) return null;

    return (object)$payload["data"];
}
