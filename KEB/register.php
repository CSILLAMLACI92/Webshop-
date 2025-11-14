<?php
session_start();
include("connect.php"); // db kapcsolat

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // alapvető ellenőrzés
    if(empty($username) || empty($email) || empty($password)){
        echo "Minden mező kitöltése kötelező!";
        exit();
    }

    // jelszó hash-elése
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // beszúrás az adatbázisba
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $email, $password_hash);

    if($stmt->execute()){
        echo "Sikeres regisztráció!";
    } else {
        echo "Hiba: " . $stmt->error;
    }
}
?>
