<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../server/connect.php";

$slug = trim((string)($_GET["slug"] ?? ""));

$basePath = rtrim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? "")), "/");
if ($basePath === "" || $basePath === ".") {
    $basePath = "";
} else {
    if (substr($basePath, -4) === "/api") {
        $basePath = substr($basePath, 0, -4);
    }
}

$normalizeAsset = function ($p) use ($basePath) {
    if (!$p) return null;
    $p = trim((string)$p);
    if ($p === "") return null;
    $p = str_replace("\\", "/", $p);
    if (preg_match("#^https?://#i", $p)) return $p;

    if (strpos($p, "../assets/") === 0) $p = "/" . ltrim(substr($p, 3), "/");
    if (strpos($p, "../uploads/") === 0) $p = "/" . ltrim(substr($p, 3), "/");

    if (strpos($p, "/assets/") === 0) return $basePath . $p;
    if (strpos($p, "/uploads/") === 0) return $basePath . $p;
    return $basePath . "/uploads/" . basename($p);
};

$hasHero = false;
$check = $conn->query("SHOW COLUMNS FROM kategoria LIKE 'hero_image_path'");
if ($check && $check->num_rows > 0) {
    $hasHero = true;
}

$sql = $hasHero
    ? "SELECT id, nev, slug, hero_image_path FROM kategoria"
    : "SELECT id, nev, slug, NULL AS hero_image_path FROM kategoria";

$types = "";
$params = [];
if ($slug !== "") {
    $sql .= " WHERE slug = ?";
    $types = "s";
    $params[] = $slug;
}
$sql .= " ORDER BY id ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "db_prepare_failed"]);
    exit;
}
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($row = $res->fetch_assoc()) {
    $out[] = [
        "id" => (int)$row["id"],
        "name" => (string)($row["nev"] ?? ""),
        "slug" => (string)($row["slug"] ?? ""),
        "hero_image" => $normalizeAsset($row["hero_image_path"] ?? null),
    ];
}

if ($slug !== "") {
    echo json_encode($out[0] ?? null, JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);

