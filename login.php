<?php
session_start();
include 'connect.php';

$error = '';

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
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Bejelentkezés - KEB hangszerbolt</title>
  <link rel="stylesheet" href="KEBhangszerbolt.css" />
</head>
<body>

<h1>Bejelentkezés</h1>

<?php if ($error): ?>
  <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post" action="login.php">
  <label for="email">Email:</label><br/>
  <input type="email" id="email" name="email" required /><br/><br/>

  <label for="password">Jelszó:</label><br/>
  <input type="password" id="password" name="password" required /><br/><br/>

  <button type="submit">Bejelentkezés</button>
</form>

<p>Nincs még fiókod? <a href="register.html">Regisztrálj itt</a></p>

</body>
</html>
