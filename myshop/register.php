<?php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Csak POST kérést fogadunk!");
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    die("Minden mező kitöltése kötelező!");
}

// Ellenőrizzük, hogy nincs-e már ilyen felhasználó
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Felhasználónév vagy email már foglalt!");
}

// Jelszó hash-elése
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Beszúrás az adatbázisba
$stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $password_hash);

if ($stmt->execute()) {
    echo "Sikeres regisztráció!";
} else {
    die("Hiba történt a regisztráció során!");
}
?>
