<?php
require __DIR__ . '/../../server/connect.php';
mysqli_set_charset($conn, 'utf8mb4');
$res = $conn->query("SELECT id,name,category_id FROM products WHERE id IN (336,339,340,341,342,344,346,378,379,380,385) ORDER BY id");
$out=[];
while($r=$res->fetch_assoc()) $out[]=$r;
echo json_encode($out, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
