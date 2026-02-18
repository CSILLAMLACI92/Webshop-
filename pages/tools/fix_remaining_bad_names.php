<?php
require __DIR__ . '/../../server/connect.php';
mysqli_set_charset($conn, 'utf8mb4');

$fix = [
  336 => 'Gitárpengető készlet',
  339 => 'Hangszer tisztító spray',
  340 => 'Mikrofon tartó állvány',
  341 => 'Fejhallgató (Basic Studio)',
  342 => 'Gitár húrkészlet (Ernie Ball)',
  344 => 'Billentyűzet állvány',
  346 => 'Yamaha HS5 stúdiómonitor',
  378 => 'Gitárhúr készlet',
  379 => 'Húzókulcs',
  380 => 'Jack-Jack kábel',
  385 => 'Fejhallgató adapter',
];

$stmt = $conn->prepare('UPDATE products SET name = ? WHERE id = ?');
$updated = 0;
foreach ($fix as $id => $name) {
  $stmt->bind_param('si', $name, $id);
  $stmt->execute();
  if ($stmt->affected_rows >= 0) $updated++;
}
$stmt->close();

echo 'updated=' . $updated . PHP_EOL;
