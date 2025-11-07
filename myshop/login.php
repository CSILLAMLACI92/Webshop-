<?php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Minden mező kitöltése kötelező!";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Itt történik az átirányítás a főoldalra
                header("Location: KEBhangszerek.html");
                exit;
            } else {
                $error = "Helytelen jelszó!";
            }
        } else {
            $error = "Nem található felhasználó ezzel az email címmel!";
        }
    }
}

// Ha hiba van, visszaadhatod a hibaüzenetet (pl. JSON-ként vagy HTML-ben)
if (!empty($error)) {
    echo "<p style='color:red;'>$error</p>";
}
?>
