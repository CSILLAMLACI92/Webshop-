<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>Fiók</title>

<style>
* { box-sizing: border-box; margin:0; padding:0;}
body { font-family:'Segoe UI',sans-serif; background:#0b111f; color:#e0eaf5; display:flex; justify-content:center; align-items:center; min-height:100vh; padding:20px;}
.container { width:100%; max-width:460px; background:#121a2b; padding:32px; border-radius:14px; box-shadow:0 0 24px rgba(0,0,0,0.7); border:1px solid #1f2a40;}
h2 { text-align:center; color:#4da6ff; margin-bottom:24px; font-size:24px; font-weight:600;}
form { display:flex; flex-direction:column; gap:12px;}
input { padding:12px 14px; border-radius:8px; border:1px solid #2c3e50; background:#0f172a; color:#e0eaf5; font-size:15px;}
button { padding:12px; border-radius:8px; border:none; background:#4da6ff; color:#000; font-weight:bold; font-size:15px; cursor:pointer; transition:0.3s;}
button:hover { background:#3399ff;}
.msg { text-align:center; font-weight:bold; margin:12px 0; font-size:14px;}
.error { color:#ff6666;}
.success { color:#66ff66;}
img.profile { width:100px; height:100px; border-radius:50%; object-fit:cover; margin:20px auto; display:block; border:2px solid #4da6ff;}
.logout { text-align:center; margin-top:20px;}
.logout a { text-decoration:none; color:#4da6ff; font-weight:bold;}
.logout a:hover { color:#ffffff;}
.delete-btn { background:#ff6666; color:#000;}
.delete-btn:hover { background:#cc0000;}
.nav-btns { display:flex; justify-content:space-around; margin-bottom:20px;}
.nav-btns button { width:30%;}
</style>

</head>
<body>
<div class="container">

<div style="text-align:center;">
 <a href="KEBhangszerek.html" class="btn btn-primary">🏠 Vissza a főoldalra</a>
</div>

<div class="nav-btns">
    <button onclick="show('login')">Bejelentkezés</button>
    <button onclick="show('register')">Regisztráció</button>
    <button onclick="show('admin')">Admin</button>
</div>

<!-- LOGIN -->
<div id="login" style="display:block;">
<h2>Bejelentkezés</h2>
<p id="login_msg"></p>
<form onsubmit="login(event)">
    <input type="text" id="login_user" placeholder="Felhasználónév vagy Email">
    <input type="password" id="login_pass" placeholder="Jelszó">
    <button type="submit">Bejelentkezés</button>
</form>
</div>

<!-- REGISTER -->
<div id="register" style="display:none;">
<h2>Regisztráció</h2>
<p id="register_msg"></p>
<form onsubmit="registerUser(event)">
    <input type="text" id="reg_user" placeholder="Felhasználónév">
    <input type="email" id="reg_email" placeholder="Email">
    <input type="password" id="reg_pass" placeholder="Jelszó">
    <button type="submit">Regisztráció</button>
</form>
</div>

<!-- ADMIN -->
<div id="admin" style="display:none;">
<h2>Admin bejelentkezés</h2>
<p id="admin_msg"></p>
<form onsubmit="adminLogin(event)">
    <input type="text" id="admin_user" placeholder="Admin név">
    <input type="password" id="admin_pass" placeholder="Jelszó">
    <button type="submit">Belépés</button>
</form>
</div>

<!-- USER PAGE -->
<div id="user_page" style="display:none;">
<h2>Üdv <span id="u_name"></span>!</h2>
<img id="u_pic" class="profile">
<p id="u_email"></p>

<form onsubmit="uploadPic(event)" enctype="multipart/form-data">
    <input type="file" id="u_file">
    <button type="submit">Profilkép feltöltése</button>
</form>

<button class="delete-btn" onclick="deleteAccount()">🗑️ Fiók törlése</button>

<div class="logout">
    <a href="#" onclick="logout()">🚪 Kijelentkezés</a>
</div>
</div>

</div>

<script>
// ===== JWT dekódoló =====
function parseJwt(token) {
    try {
        let base64Url = token.split('.')[1];
        let base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        let jsonPayload = decodeURIComponent(atob(base64).split('').map(c =>
            '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
        ).join(''));
        return JSON.parse(jsonPayload);
    } catch {
        return null;
    }
}

// ===== UI váltás =====
function show(id) {
    ["login","register","admin","user_page"].forEach(x => {
        document.getElementById(x).style.display = "none";
    });
    document.getElementById(id).style.display = "block";
}

// ===== USER LOGIN =====
async function login(e) {
    e.preventDefault();

    let login = login_user.value.trim();
    let password = login_pass.value.trim();
    let msg = login_msg;

    if (!login || !password) {
        msg.innerHTML = "<span class='error'>❌ Minden mező kötelező!</span>";
        return;
    }

    let res = await fetch("login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ login, password })
    });

    let data = await res.json();

    if (!res.ok) {
        msg.innerHTML = `<span class="error">❌ ${data.error}</span>`;
        return;
    }

    msg.innerHTML = `<span class="success">✔ Sikeres bejelentkezés!</span>`;
    localStorage.setItem("token", data.token);

    // 🔥 FELHASZNÁLÓNÉV KIÍRÁSA AZONNAL
    let decoded = parseJwt(data.token);
    if (decoded && decoded.data && decoded.data.username) {
        u_name.textContent = decoded.data.username;
    }

    // profil betöltése
    loadProfile();
}


// ===== REGISTER =====
async function registerUser(e) {
    e.preventDefault();

    let username = reg_user.value.trim();
    let email = reg_email.value.trim();
    let password = reg_pass.value.trim();
    let msg = register_msg;

    if (!username || !email || !password) {
        msg.innerHTML = "<span class='error'>❌ Minden mező kötelező!</span>";
        return;
    }

    let res = await fetch("register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, email, password })
    });

    let data = await res.json();

    if (!res.ok) 
        msg.innerHTML = `<span class="error">❌ ${data.error}</span>`;
    else 
        msg.innerHTML = `<span class="success">✔ Sikeres regisztráció! Jelentkezz be.</span>`;
}

// ===== ADMIN LOGIN =====
async function adminLogin(e) {
    e.preventDefault();

    let login = document.getElementById("admin_user").value.trim();
    let password = document.getElementById("admin_pass").value.trim();
    let msg = document.getElementById("admin_msg");
    msg.innerHTML = "";

    console.log("ADMIN LOGIN INPUT:", login, password); // DEBUG!!

    if (!login || !password) {
        msg.innerHTML = "<span class='error'>❌ Minden mező kötelező!</span>";
        return;
    }

    let res = await fetch("login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ login, password })
    });

    let data;
    try {
        data = await res.json();
    } catch {
        msg.innerHTML = "<span class='error'>❌ Szerver hiba: nem JSON választ kaptam!</span>";
        return;
    }

    if (!res.ok || !data.token) {
        msg.innerHTML = `<span class='error'>❌ ${data.error || "Hibás adatok"}</span>`;
        return;
    }

    localStorage.setItem("token", data.token);

    let decoded = parseJwt(data.token);

    if (!decoded || decoded.data.role !== "admin") {
        msg.innerHTML = "<span class='error'>❌ Ez NEM admin fiók!</span>";
        localStorage.removeItem("token");
        return;
    }

    // SIKEEEER 🟢
    msg.innerHTML = "<span class='success'>✔ Sikeres admin bejelentkezés!</span>";

    // 🔥 1 mp múlva ADMIN DASHBOARD
    setTimeout(() => {
        window.location.href = "Admin_dashboard.html";
    }, 1000);
}



// ===== PROFIL TÖLTÉS =====
async function loadProfile() {
    let token = localStorage.getItem("token");
    if (!token) return;

    let res = await fetch("profile.php", {
        headers: { "Authorization": "Bearer " + token }
    });

    let data = await res.json();

    if (!res.ok) {
        alert("❌ Lejárt token. Jelentkezz be újra.");
        localStorage.removeItem("token");
        show("login");
        return;
    }

    u_name.textContent = data.user.username;
    u_email.textContent = data.user.email;
    u_pic.src = data.user.profile_pic;

    show("user_page");
}

// ===== PROFILKÉP FELTÖLTÉS =====
async function uploadPic(e) {
    e.preventDefault();

    let file = u_file.files[0];
    if (!file) return alert("Nincs fájl kiválasztva!");

    let form = new FormData();
    form.append("profile_pic", file);

    let token = localStorage.getItem("token");

    let res = await fetch("pfp.php", {
        method: "POST",
        headers: { "Authorization": "Bearer " + token },
        body: form
    });

    let data = await res.json();
    alert(data.message);
    loadProfile();
}

// ===== FIÓK TÖRLÉS =====
async function deleteAccount() {
    if (!confirm("Biztos vagy benne?")) return;

    let token = localStorage.getItem("token");

    let res = await fetch("account.php", {
        method: "DELETE",
        headers: { "Authorization": "Bearer " + token }
    });

    let data = await res.json();
    alert(data.message);

    localStorage.removeItem("token");
    show("login");
}

// ===== KIJELENTKEZÉS =====
function logout() {
    localStorage.removeItem("token");
    show("login");
}

</script>

</body>
</html>
