<?php
ini_set("display_errors", 0);
error_reporting(0);
header("Content-Type: application/json; charset=utf-8");

require "connect.php";
require "jwt_helper.php";
require "profanity.php";

$filePath = __DIR__ . "/../uploads/home_reviews.json";

function read_reviews($filePath) {
    if (!is_file($filePath)) return [];
    $raw = @file_get_contents($filePath);
    if ($raw === false || trim($raw) === "") return [];
    $arr = json_decode($raw, true);
    if (!is_array($arr)) return [];
    $out = [];
    foreach ($arr as $row) {
        if (!is_array($row)) continue;
        $text = sanitize_profanity_text(trim((string)($row["text"] ?? "")));
        $name = trim((string)($row["name"] ?? ""));
        if ($text === "" || $name === "") continue;
        $out[] = [
            "id" => (string)($row["id"] ?? ""),
            "text" => $text,
            "name" => $name,
            "tag" => trim((string)($row["tag"] ?? "")),
            "created_at" => trim((string)($row["created_at"] ?? ""))
        ];
    }
    return $out;
}

function write_reviews($filePath, $rows) {
    $dir = dirname($filePath);
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $fp = @fopen($filePath, "c+");
    if (!$fp) return false;
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }
    ftruncate($fp, 0);
    rewind($fp);
    $ok = fwrite($fp, json_encode(array_values($rows), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false;
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return $ok;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $list = read_reviews($filePath);
    echo json_encode(["reviews" => $list], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"], JSON_UNESCAPED_UNICODE);
    exit;
}

$user = verify_jwt();
if (!$user || empty($user->id)) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"], JSON_UNESCAPED_UNICODE);
    exit;
}

$text = sanitize_profanity_text(trim((string)($_POST["text"] ?? "")));
$tag = trim((string)($_POST["tag"] ?? ""));
if ($text === "") {
    http_response_code(400);
    echo json_encode(["error" => "Missing text"], JSON_UNESCAPED_UNICODE);
    exit;
}
$text = mb_substr($text, 0, 500, "UTF-8");
$tag = mb_substr($tag, 0, 80, "UTF-8");

$name = trim((string)($user->username ?? ""));
if ($name === "") {
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ? LIMIT 1");
    if ($stmt) {
        $uid = (int)$user->id;
        $stmt->bind_param("i", $uid);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
                $name = trim((string)($row["username"] ?? ""));
            }
        }
        $stmt->close();
    }
}
if ($name === "") $name = "Felhasználó";

$rows = read_reviews($filePath);
array_unshift($rows, [
    "id" => uniqid("hr_", true),
    "text" => $text,
    "name" => $name,
    "tag" => $tag,
    "created_at" => date("c")
]);

if (count($rows) > 200) {
    $rows = array_slice($rows, 0, 200);
}

if (!write_reviews($filePath, $rows)) {
    http_response_code(500);
    echo json_encode(["error" => "Write failed"], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(["success" => true], JSON_UNESCAPED_UNICODE);
