<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Windows MAMP alapértelmezett beállítások
$host = "localhost:8889"; // MySQL port
$user = "root";
$password = "root";           // alapértelmezett üres
$dbname = "myshop";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}
?>
