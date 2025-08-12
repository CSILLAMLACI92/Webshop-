<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Felhasználónév vagy email már foglalt!");
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $password_hash);

if ($stmt->execute()) {
    // Sikerüzenet és átirányítás 3 mp múlva
    ?>
    <!DOCTYPE html>
    <html lang="hu">
    <head>
      <meta charset="UTF-8" />
      <title>Sikeres regisztráció</title>
    </head>
    <body>
      <p style="color: green; font-weight: bold;">
        Sikeres regisztráció! 3 másodperc múlva átirányítunk a főoldalra...
      </p>
      <script>
        setTimeout(() => {
          window.location.href = 'KEBhangszerek.html';
        }, 3000);
      </script>
    </body>
    </html>
    <?php
    exit;
} else {
    die("Hiba történt a regisztráció során!");
}
?>
