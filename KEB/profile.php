<?php
session_start();

// Ha nincs bejelentkezve, üzenet vagy átirányítás
if (!isset($_SESSION['username'])) {
    echo "❌ Nem vagy bejelentkezve. <a href='login.php'>Bejelentkezés</a>";
    exit();
}

// Ha be van jelentkezve, kiírjuk az adatokat
echo "<h2>Üdv, " . htmlspecialchars($_SESSION['username']) . "!</h2>";
echo "<p>Email: " . htmlspecialchars($_SESSION['email']) . "</p>";
echo "<a href='logout.php'>Kijelentkezés</a>";
?>
