<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ADMIN CHECK
if(!isset($_SESSION['username']) || $_SESSION['username'] !== "ADMIN"){
    header("Location: account.php");
    exit();
}

// DB
$host = "localhost:8889";
$user = "root";
$password = "root";
$dbname = "myshop";
$conn = new mysqli($host, $user, $password, $dbname);

// Delete user
if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $id = (int)$_GET['delete'];

    if($id !== 0){ // Admin User ID = 0 -> so cannot delete itself
        $stmt = $conn->prepare("CALL deleteUser(?)");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_dashboard.php");
    exit();
}

// List users
$result = $conn->query("SELECT id, username, email, profile_pic, created_at FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>Admin Panel</title>

<style>
body { font-family: 'Segoe UI', sans-serif; background-color: #0b111f; color: #e0eaf5; padding: 40px; }
.container { max-width: 800px; margin: auto; background: #121a2b; padding: 32px; border-radius: 14px; border: 1px solid #1f2a40; }
h1 { text-align: center; margin-bottom: 25px; color: #4da6ff; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 12px; border-bottom: 1px solid #1f2a40; text-align: center; }
img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; border: 1px solid #4da6ff; }
a.btn { display: inline-block; padding: 8px 10px; border-radius: 6px; background: #4da6ff; color: #000; text-decoration: none; font-weight: bold; }
a.btn:hover { background: #1c90ff; }
.delete-btn { background: #ff3333 !important; color: #000 !important; }
.delete-btn:hover { background: #e60000 !important; }
.back { display: block; text-align: center; margin-top: 20px; }
</style>

</head>
<body>

<div class="container">
<h1>Admin Dashboard</h1>

<table>
<tr>
    <th>ID</th>
    <th>Profil</th>
    <th>Felhasználó</th>
    <th>Email</th>
    <th>Regisztrált</th>
    <th>Művelet</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><img src="<?= $row['profile_pic'] ?>"></td>
    <td><?= $row['username'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['created_at'] ?></td>
    <td>
        <?php if($row['id'] != 0): ?>
            <a class="btn delete-btn" href="admin_dashboard.php?delete=<?= $row['id'] ?>" onclick="return confirm('Biztosan törlöd?');">Törlés</a>
        <?php else: ?>
            <span style="color:#4da6ff;">ADMIN</span>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>

</table>

<div class="back">
    <a class="btn" href="account.php">← Vissza a fiók oldalra</a>
</div>

</div>
</body>
</html>