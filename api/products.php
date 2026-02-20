<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../server/connect.php";

$id = intval($_GET["id"] ?? 0);
$category = trim($_GET["category"] ?? "");
$q = trim($_GET["q"] ?? "");
$limit = intval($_GET["limit"] ?? 200);
$offset = intval($_GET["offset"] ?? 0);

if ($limit <= 0 || $limit > 1000) $limit = 200;
if ($offset < 0) $offset = 0;

$params = [];
$where = [];

if ($id > 0) {
    $where[] = "p.id = ?";
    $params[] = $id;
}

if ($category !== "") {
    $where[] = "k.slug = ?";
    $params[] = $category;
}

if ($q !== "") {
    $where[] = "p.name LIKE ?";
    $params[] = "%" . $q . "%";
}

$sql = "
SELECT
  p.id,
  p.name,
  p.price,
  p.hang,
  k.id AS category_id,
  k.nev AS category_name,
  k.slug AS category_slug,
  pic.path AS image_path
FROM products p
LEFT JOIN kategoria k ON k.id = p.category_id
LEFT JOIN (
  SELECT owner_id, MIN(id) AS pic_id
  FROM pictures
  WHERE owner_type = 'product' AND is_active = 1
  GROUP BY owner_id
) px ON px.owner_id = p.id
LEFT JOIN pictures pic ON pic.id = px.pic_id
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.id ASC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "db_prepare_failed"]);
    exit;
}

$types = str_repeat("s", count($params)) . "ii";
$params[] = $limit;
$params[] = $offset;

$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$basePath = rtrim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? "")), "/");
if ($basePath === "" || $basePath === ".") {
    $basePath = "";
} else {
    if (substr($basePath, -4) === "/api") {
        $basePath = substr($basePath, 0, -4);
    }
}

$normalizeAsset = function ($p, $kind) use ($basePath) {
    if (!$p) return null;
    $p = trim((string)$p);
    if ($p === "") return null;
    $p = str_replace("\\", "/", $p);
    if (preg_match("#^https?://#i", $p)) return $p;

    if (strpos($p, "../assets/") === 0) {
        $p = "/" . ltrim(substr($p, 3), "/");
    }
    if (strpos($p, "../uploads/") === 0) {
        $p = "/" . ltrim(substr($p, 3), "/");
    }
    if (strpos($p, "/assets/") === 0) return $basePath . $p;
    if (strpos($p, "/uploads/") === 0) return $basePath . $p;

    $lower = strtolower($p);
    if (preg_match("#(^|/)pages/images/#", $lower) || preg_match("#(^|/)images/#", $lower)) {
        return $basePath . "/uploads/" . basename($p);
    }
    if (preg_match("#(^|/)pages/hangok/#", $lower) || preg_match("#(^|/)hangok/#", $lower)) {
        return $basePath . "/assets/audio/" . basename($p);
    }
    if (preg_match("#\\.(jpg|jpeg|png|webp|gif)$#i", $p)) {
        return $basePath . "/uploads/" . basename($p);
    }
    if (preg_match("#\\.(mp3|wav|ogg)$#i", $p)) {
        return $basePath . "/assets/audio/" . basename($p);
    }

    if ($kind === "img") return $basePath . "/uploads/" . basename($p);
    if ($kind === "audio") return $basePath . "/assets/audio/" . basename($p);
    return $p;
};

$normalizeText = function ($s) {
    $s = trim((string)($s ?? ""));
    if ($s === "") return $s;

    $brokenPattern = '/[\\x{00C2}\\x{00C3}\\x{00C5}\\x{0102}\\x{0139}\\x{FFFD}]/u';

    if (preg_match($brokenPattern, $s)) {
        for ($i = 0; $i < 2; $i++) {
            $cand = @iconv("ISO-8859-1", "UTF-8//IGNORE", $s);
            if (!$cand || $cand === $s) break;
            $s = $cand;
            if (!preg_match($brokenPattern, $s)) break;
        }
    }

    $map = [
        "\u{0102}\u{02C7}" => "á", "\u{0102}\u{00A9}" => "é", "\u{0102}\u{00AD}" => "í", "\u{0102}\u{0142}" => "ó",
        "\u{0102}\u{00B6}" => "ö", "\u{0102}\u{015F}" => "ú", "\u{0102}\u{017A}" => "ü",
        "\u{00C3}\u{00A1}" => "á", "\u{00C3}\u{00A9}" => "é", "\u{00C3}\u{00AD}" => "í", "\u{00C3}\u{00B3}" => "ó",
        "\u{00C3}\u{00B6}" => "ö", "\u{00C3}\u{00BA}" => "ú", "\u{00C3}\u{00BC}" => "ü",
        "\u{00C5}\u{2018}" => "ő", "\u{00C5}\u{00B1}" => "ű", "\u{0139}\u{2018}" => "ő", "\u{0139}\u{00B1}" => "ű",
        "\u{00C5}\u{201C}" => "Ő", "\u{00C5}\u{00B0}" => "Ű", "\u{0139}\u{201C}" => "Ő", "\u{0139}\u{00B0}" => "Ű",
        "\u{00E2}\u{20AC}\u{201C}" => "-"
    ];
    $s = strtr($s, $map);

    $s = str_replace("\u{FFFD}", "", $s);
    $s = preg_replace('/\s+/u', ' ', $s);
    return trim((string)$s);
};

$out = [];
while ($row = $res->fetch_assoc()) {
    $img = $normalizeAsset($row["image_path"] ?: null, "img");
    $hang = $normalizeAsset($row["hang"] ?: null, "audio");
    $out[] = [
        "id" => (int)$row["id"],
        "nev" => $normalizeText($row["name"] ?? ""),
        "ar" => (float)$row["price"],
        "hang" => $hang,
        "kep" => $img,
        "category_id" => $row["category_id"] ? (int)$row["category_id"] : null,
        "category_name" => $normalizeText($row["category_name"] ?? ""),
        "category_slug" => $row["category_slug"],
    ];
}

echo json_encode($id > 0 ? ($out[0] ?? null) : $out, JSON_UNESCAPED_UNICODE);
