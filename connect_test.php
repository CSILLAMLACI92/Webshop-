<?php
$host = 'localhost';
$user = 'root';
$password = 'root';
$dbname = 'webshop';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
} else {
    echo "Sikeres kapcsolódás a webshop adatbázishoz!";
}
?>
