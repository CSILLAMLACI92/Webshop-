<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../server/connect.php";

$sqlVideo = "
SELECT id, filename, path, alt_text, mime_type
FROM pictures
WHERE owner_type = 'other'
  AND is_active = 1
  AND (mime_type LIKE 'video/%' OR filename REGEXP '\\\\.(mp4|webm|mov|m4v|avi|mkv)$')
ORDER BY id DESC
LIMIT 1
";

$sqlCarousel = "
SELECT id, filename, path, alt_text, mime_type
FROM pictures
WHERE owner_type = 'other'
  AND is_active = 1
  AND alt_text LIKE 'Carousel kep %'
  AND (
    mime_type LIKE 'image/%'
    OR filename REGEXP '\\\\.(jpg|jpeg|png|webp|gif)$'
  )
ORDER BY id ASC
";

$resVideo = $conn->query($sqlVideo);
$videoRow = $resVideo ? $resVideo->fetch_assoc() : null;

$carouselRows = [];
$resCarousel = $conn->query($sqlCarousel);
if ($resCarousel) {
    while ($r = $resCarousel->fetch_assoc()) {
        $carouselRows[] = $r;
    }
}

$basePath = rtrim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? "")), "/");
if ($basePath === "" || $basePath === ".") {
    $basePath = "";
} else {
    if (substr($basePath, -4) === "/api") {
        $basePath = substr($basePath, 0, -4);
    }
}

$normalize = function ($p) use ($basePath) {
    if (!$p) return null;
    $p = trim((string)$p);
    if ($p === "") return null;
    $p = str_replace("\\", "/", $p);
    if (preg_match("#^https?://#i", $p)) return $p;
    if (strpos($p, "../uploads/") === 0) {
        $p = "/" . ltrim(substr($p, 3), "/");
    }
    if (strpos($p, "/uploads/") === 0) return $basePath . $p;
    return $basePath . "/uploads/" . basename($p);
};

$promoVideo = null;
if ($videoRow) {
    $promoVideo = [
        "id" => (int)$videoRow["id"],
        "filename" => $videoRow["filename"],
        "path" => $normalize($videoRow["path"] ?: $videoRow["filename"]),
        "alt" => $videoRow["alt_text"] ?: "Promocio video",
        "mime_type" => $videoRow["mime_type"] ?: "video/mp4",
    ];
}

$carousel = [];
foreach ($carouselRows as $row) {
    $carousel[] = [
        "id" => (int)$row["id"],
        "filename" => $row["filename"],
        "path" => $normalize($row["path"] ?: $row["filename"]),
        "alt" => $row["alt_text"] ?: "Carousel kep",
        "mime_type" => $row["mime_type"] ?: "image/jpeg",
    ];
}

$out = [
    "promo_video" => $promoVideo,
    "carousel_images" => $carousel,
];

if ($promoVideo) {
    // Legacy fields for older frontend callers.
    $out["id"] = $promoVideo["id"];
    $out["filename"] = $promoVideo["filename"];
    $out["path"] = $promoVideo["path"];
    $out["alt"] = $promoVideo["alt"];
    $out["mime_type"] = $promoVideo["mime_type"];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
