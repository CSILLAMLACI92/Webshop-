<?php
require __DIR__ . '/../../server/connect.php';

mysqli_set_charset($conn, 'utf8mb4');

$res = $conn->query("SELECT p.id, p.name, k.slug FROM products p LEFT JOIN kategoria k ON k.id=p.category_id ORDER BY p.id");
if (!$res) {
  echo "sql_error: " . $conn->error . PHP_EOL;
  exit(1);
}
$bad = 0;
while ($r = $res->fetch_assoc()) {
  $name = (string)$r['name'];
  if (preg_match('/[\x{00C2}\x{00C3}\x{00C5}\x{0102}\x{0139}\x{FFFD}]/u', $name) || preg_match('/Ă|Ã|Å|Ĺ/u', $name)) {
    echo $r['id'] . "\t" . ($r['slug'] ?? '-') . "\t" . $name . PHP_EOL;
    $bad++;
  }
}
echo "bad_count=" . $bad . PHP_EOL;
