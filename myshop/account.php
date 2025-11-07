<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8" />
<title>Fiók</title>
</head>
<body>
  <p>Üdvözlünk, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
  <form action="logout.php" method="post">
    <button type="submit">Kijelentkezés</button>
  </form>
</body>
</html>
