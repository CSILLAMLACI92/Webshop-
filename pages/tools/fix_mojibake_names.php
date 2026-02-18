<?php
require_once __DIR__ . '/../../server/connect.php';

function norm_text($s) {
    $s = trim((string)($s ?? ''));
    if ($s === '') return $s;

    $brokenPattern = '/[\x{00C2}\x{00C3}\x{00C5}\x{0102}\x{0139}\x{FFFD}]/u';
    if (preg_match($brokenPattern, $s)) {
        for ($i = 0; $i < 2; $i++) {
            $cand = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $s);
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

    $s = str_replace("\u{FFFD}", '', $s);
    $s = preg_replace('/\s+/u', ' ', $s);
    return trim((string)$s);
}

$fixedProducts = 0;
$fixedCats = 0;

$res = $conn->query('SELECT id, name FROM products');
$upd = $conn->prepare('UPDATE products SET name = ? WHERE id = ?');
while ($row = $res->fetch_assoc()) {
    $old = (string)$row['name'];
    $new = norm_text($old);
    if ($new !== $old && $new !== '') {
        $upd->bind_param('si', $new, $row['id']);
        $upd->execute();
        $fixedProducts++;
    }
}
$upd->close();

$res2 = $conn->query('SELECT id, nev FROM kategoria');
$upd2 = $conn->prepare('UPDATE kategoria SET nev = ? WHERE id = ?');
while ($row = $res2->fetch_assoc()) {
    $old = (string)$row['nev'];
    $new = norm_text($old);
    if ($new !== $old && $new !== '') {
        $upd2->bind_param('si', $new, $row['id']);
        $upd2->execute();
        $fixedCats++;
    }
}
$upd2->close();

echo "fixed products: {$fixedProducts}\n";
echo "fixed categories: {$fixedCats}\n";
