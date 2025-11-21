<?php
require_once "auth.php";
$user = require_user(); // JWT ellenőrzés

?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>Profilom</title>
<style>
body {
    background:#0b111f;
    color:white;
    font-family:Arial;
    padding:30px;
}
.container {
    width: 400px;
    margin:auto;
    background:#121a2b;
    padding:25px;
    border-radius:12px;
    text-align:center;
}
img.profile {
    width:130px;
    height:130px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #4da6ff;
    margin-bottom:20px;
}
button {
    padding:10px 20px;
    margin-top:10px;
    width:100%;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:16px;
}
.logout { background:#ff5555; }
.logout:hover { background:#cc0000; }

.save { background:#4da6ff; }
.save:hover { background:#007bff; }

.home {
    background:#555;
}
.home:hover {
    background:#444;
}
</style>
</head>
<body>

<div class="container">
    <h2>Üdv, <?=htmlspecialchars($user->username)?>!</h2>

    <img src="uploads/<?=$user->profile_pic ?: 'default.png'?>" class="profile">

    <form action="pfp.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="profile_pic"><br><br>
        <button class="save">Profilkép frissítése</button>
    </form>

    <button class="logout" onclick="logout()">Kijelentkezés</button>

    <br><br>
    <a href="KEBhangszerek.html">
        <button class="home">🏠 Vissza a főoldalra</button>
    </a>
</div>

<script>
function logout() {
    localStorage.removeItem("token");
    window.location.href = "KEBhangszerek.html";
}
</script>

</body>
</html>
