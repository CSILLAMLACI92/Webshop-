<?php
require_once "auth.php";
$user = require_user(); // JWT ellenőrzés
header("Content-Type: text/html; charset=UTF-8");

?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title data-i18n="profile_page_title">Profilom</title>
<link rel="stylesheet" href="../assets/css/Mobile.css">
<script src="../assets/js/lang.js?v=20260204"></script>
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
<div class="lang-switch" role="group" aria-label="Nyelv">
  <button type="button" class="lang-pill" data-lang="hu">HU</button>
  <button type="button" class="lang-pill" data-lang="en">EN</button>
  <button type="button" class="lang-pill" data-lang="de">DE</button>
</div>

<div class="container">
    <h2 id="welcomeText"></h2>

    <img src="/uploads/<?=htmlspecialchars($user->profile_pic ?: 'Default.avatar.jpg', ENT_QUOTES)?>" class="profile">

    <form action="pfp.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="profile_pic"><br><br>
        <button class="save" data-i18n="profile_page_upload">Profilkép frissítése</button>
    </form>

    <button class="logout" onclick="logout()" data-i18n="profile_page_logout">Kijelentkezés</button>

    <br><br>
    <a href="../pages/KEBhangszerek.html">
        <button class="home" data-i18n="profile_page_back">← Vissza a főoldalra</button>
    </a>
</div>

<script>
const tr = (key) => (window.SH_LANG && window.SH_LANG.t ? window.SH_LANG.t(key) : key);
const userName = "<?=htmlspecialchars($user->username, ENT_QUOTES)?>";
const welcomeText = document.getElementById("welcomeText");
if (welcomeText) {
    welcomeText.textContent = tr("profile_page_welcome").replace("{name}", userName);
}

function logout() {
    const token = localStorage.getItem("token");
    if (token) {
        fetch("../api/activity.php", {
            method: "POST",
            headers: { "Authorization": "Bearer " + token },
            body: new URLSearchParams({ action: "logout" })
        }).catch(() => {});
    }
    localStorage.removeItem("token");
    window.location.href = "../pages/KEBhangszerek.html";
}

window.onLangChange = () => {
    if (welcomeText) {
        welcomeText.textContent = tr("profile_page_welcome").replace("{name}", userName);
    }
};
</script>

</body>
</html>


