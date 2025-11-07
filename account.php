<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Adatbázis kapcsolat
$host = "localhost:8889";
$user = "root";
$password = "root";
$dbname = "myshop";

$conn = new mysqli($host, $user, $password, $dbname);
mysqli_report(MYSQLI_REPORT_OFF);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Logout
if(isset($_GET['logout'])){
    session_unset();
    session_destroy();
    header("Location: account.php");
    exit();
}

// Profilkép feltöltés
$upload_msg = "";
if(isset($_POST['upload_pic']) && isset($_SESSION['username'])){
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
        $target_dir = "uploads/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $filename = time() . "_" . basename($_FILES['profile_pic']['name']);
        $target_file = $target_dir . $filename;

        $allowed_types = ['jpg','jpeg','png','gif'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if(in_array($file_ext, $allowed_types)){
            if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)){
                $stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE username=?");
                $stmt->bind_param("ss", $target_file, $_SESSION['username']);
                $stmt->execute();
                $stmt->close(); 
                $_SESSION['profile_pic'] = $target_file;
                $upload_msg = "✅ Profilkép sikeresen feltöltve!";
            } else {
                $upload_msg = "❌ Hiba a fájl feltöltése közben!";
            }
        } else {
            $upload_msg = "❌ Csak JPG, PNG vagy GIF formátum engedélyezett!";
        }
    } else {
        $upload_msg = "❌ Nincs kiválasztva fájl!";
    }
}

// Regisztráció
$register_msg = "";
if(isset($_POST['action']) && $_POST['action'] === 'register'){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if(empty($username) || empty($email) || empty($password)){
        $register_msg = "❌ Minden mező kitöltése kötelező!";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, profile_pic, created_at) VALUES (?, ?, ?, 'uploads/default_avatar.png', NOW())");
        $stmt->bind_param("sss", $username, $email, $password_hash);

        if($stmt->execute()){
            $register_msg = "✅ Sikeres regisztráció!";
        } else {
            if(strpos($stmt->error, 'Duplicate') !== false){
                $register_msg = "❌ Ez a felhasználó már létezik!";
            } else {
                $register_msg = "❌ Hiba történt!";
            }
        }
        $stmt->close();
    }
}

// Bejelentkezés + Admin mód
$login_msg = "";
if(isset($_POST['action']) && $_POST['action'] === 'login'){
    $is_admin = isset($_POST['admin_login']);

    if($is_admin){
        if($_POST['login'] === "admin" && $_POST['password'] === "KEBDEV2000"){
            $_SESSION['username'] = "ADMIN";
            $_SESSION['email'] = "admin@site.hu";
            $_SESSION['profile_pic'] = "uploads/default_avatar.png";
            $_SESSION['user_id'] = 0;
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $login_msg = "❌ Hibás admin belépési adatok!";
        }
    } else {
        $login = trim($_POST['login']);
        $password = trim($_POST['password']);

        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if($result->num_rows === 1){
            $row = $result->fetch_assoc();
            if(password_verify($password,$row['password_hash'])){
                $_SESSION['username'] = $row['username'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['profile_pic'] = !empty($row['profile_pic']) ? $row['profile_pic'] : 'uploads/default_avatar.png';
                $_SESSION['user_id'] = $row['id'];
                header("Location: account.php");
                exit();
            } else {
                $login_msg = "❌ Hibás jelszó!";
            }
        } else {
            $login_msg = "❌ Nincs ilyen felhasználó!";
        }
    }
}

// Fiók törlése - ADMIN VÉDETT ✅
if(isset($_POST['delete_account']) && isset($_SESSION['user_id'])){

    if($_SESSION['username'] === "ADMIN" || $_SESSION['user_id'] == 0){
        header("Location: account.php?error=admin_cannot_be_deleted");
        exit();
    }

    $stmt = $conn->prepare("CALL deleteUser(?)");
    $stmt->bind_param("i", $_SESSION['user_id']);
    if($stmt->execute()){
        session_unset();
        session_destroy();
        header("Location: account.php?deleted=1");
        exit();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>Fiók</title>
<style>
/* Alap */
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', sans-serif; background-color: #0b111f; color: #e0eaf5; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }

/* Konténer */
.container { width: 100%; max-width: 460px; background-color: #121a2b; padding: 32px; border-radius: 14px; box-shadow: 0 0 24px rgba(0, 0, 0, 0.7); border: 1px solid #1f2a40; }

/* Címek */
h2 { text-align: center; color: #4da6ff; margin-bottom: 24px; font-size: 24px; font-weight: 600; }

/* Formák */
form { display: flex; flex-direction: column; gap: 12px; }
input { padding: 12px 14px; border-radius: 8px; border: 1px solid #2c3e50; background-color: #0f172a; color: #e0eaf5; font-size: 15px; }
input:focus { outline: none; border-color: #4da6ff; }
input::placeholder { color: #7a8ca5; }

/* Gombok */
button { padding: 12px; border-radius: 8px; border: none; background-color: #4da6ff; color: #000; font-weight: bold; font-size: 15px; cursor: pointer; transition: background-color 0.3s ease; }
button:hover { background-color: #3399ff; }

/* Üzenetek */
.msg { text-align: center; font-weight: bold; margin: 12px 0; font-size: 14px; }
.error { color: #80aaff; }
.success { color: #66ccff; }

/* Profilkép */
img.profile { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 20px auto; display: block; border: 2px solid #4da6ff; }

/* Kijelentkezés */
.logout { text-align: center; margin-top: 20px; }
.logout a { text-decoration: none; color: #4da6ff; font-weight: bold; }
.logout a:hover { color: #ffffff; }

/* Fiók törlés */
.delete-btn { background-color: #4da6ff; color: #000; }
.delete-btn:hover { background-color: #3399ff; }

/* Navigáció */
.nav-btns { display: flex; justify-content: space-around; margin-bottom: 20px; }
.nav-btns button { width: 30%; }
</style>
</head>

<body>
<div class="container">
<div style="text-align:center;">
    <a href="KEBhangszerek.html" class="home-btn">🏠 Főoldal</a>
</div>

<?php if(!isset($_SESSION['username'])): ?>
<div class="nav-btns">
    <button onclick="showForm('loginForm')">Bejelentkezés</button>
    <button onclick="showForm('registerForm')">Regisztráció</button>
    <button onclick="showForm('adminForm')">Admin</button>
</div>

<div id="loginForm" style="display:block;">
<h2>Bejelentkezés</h2>
<?php if($login_msg) echo "<p class='msg error'>$login_msg</p>"; ?>
<form action="account.php" method="post">
    <input type="text" name="login" placeholder="Felhasználónév vagy Email" required>
    <input type="password" name="password" placeholder="Jelszó" required>
    <button type="submit" name="action" value="login">Bejelentkezés</button>
</form>
</div>

<div id="registerForm" style="display:none;">
<h2>Regisztráció</h2>
<?php if($register_msg) echo "<p class='msg error'>$register_msg</p>"; ?>
<form action="account.php" method="post">
    <input type="text" name="username" placeholder="Felhasználónév" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Jelszó" required>
    <button type="submit" name="action" value="register">Regisztráció</button>
</form>
</div>

<div id="adminForm" style="display:none;">
<h2>Admin bejelentkezés</h2>
<form action="account.php" method="post">
    <input type="text" name="login" placeholder="Admin név" required>
    <input type="password" name="password" placeholder="Jelszó" required>
    <input type="hidden" name="admin_login" value="1">
    <button type="submit" name="action" value="login">Belépés</button>
</form>
</div>

<script>
function showForm(id){
    document.getElementById('loginForm').style.display='none';
    document.getElementById('registerForm').style.display='none';
    document.getElementById('adminForm').style.display='none';
    document.getElementById(id).style.display='block';
}
</script>

<?php else: ?>

<h2>Üdv, <?=$_SESSION['username']?>!</h2>
<img src="<?=$_SESSION['profile_pic']?>" class="profile" alt="Profilkép">

<?php if($upload_msg) echo "<p class='msg success'>$upload_msg</p>"; ?>
<?php if(isset($_GET['error']) && $_GET['error'] === 'admin_cannot_be_deleted') echo "<p class='msg error'>❌ Az admin fiók nem törölhető!</p>"; ?>

<form action="account.php" method="post" enctype="multipart/form-data">
    <input type="file" name="profile_pic" accept="image/*" required>
    <button type="submit" name="upload_pic">🖼️ Feltöltés</button>
</form>

<p><strong>Email:</strong> <?=$_SESSION['email']?></p>

<form method="POST" action="account.php" onsubmit="return confirm('Biztosan törölni szeretnéd a fiókodat? Ez a művelet nem visszavonható!');">
    <button type="submit" name="delete_account" class="delete-btn">🗑️ Fiók törlése</button>
</form>

<div class="logout">
    <a href="account.php?logout=1">🚪 Kijelentkezés</a>
</div>

<?php endif; ?>
</div>
</body>
</html>
