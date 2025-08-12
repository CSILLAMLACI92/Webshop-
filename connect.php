<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$host = 'localhost';
$user = 'root';
$password = 'root'; // vagy amit a MAMP-ban használsz
$dbname = 'webshop adatbázis';


$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}
?>
