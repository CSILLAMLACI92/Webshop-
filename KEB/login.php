<?php
session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST['login']); // username vagy email
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("SQL hiba: " . $conn->error); }

    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            header("Location: profile.php");
            exit();
        } else {
            echo "❌ Hibás jelszó!";
        }
    } else {
        echo "❌ Nincs ilyen felhasználó!";
    }
}
?>

<form method="post">
    <h2>Bejelentkezés</h2>
    <input type="text" name="login" placeholder="Felhasználónév vagy Email" required><br>
    <input type="password" name="password" placeholder="Jelszó" required><br>
    <button type="submit">Bejelentkezés</button>
</form>
