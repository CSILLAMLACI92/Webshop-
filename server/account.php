<?php
header("Content-Type: text/html; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title data-i18n="account_title">Fiók</title>
<link rel="icon" href="../uploads/favicon-16x16.png">

<link rel="stylesheet" href="../assets/css/LightTheme.css">
<link rel="stylesheet" href="../assets/css/Mobile.css">
<script src="../assets/js/lang.js?v=20260216-4"></script>

<style>
* { box-sizing: border-box; margin:0; padding:0;}

body.account-page {
    font-family: "Poppins", "Trebuchet MS", sans-serif;
    background:
      radial-gradient(1200px 600px at 10% -10%, rgba(77, 184, 255, 0.25), transparent 60%),
      radial-gradient(800px 500px at 90% 0%, rgba(16, 116, 182, 0.25), transparent 65%),
      linear-gradient(180deg, #0b2446 0%, #0f3d63 45%, #0d567b 100%);
    color: #eaf6ff;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    padding:20px;
}

.container {
    width:100%;
    max-width:560px;
    background: rgba(10, 22, 38, 0.92);
    padding:28px;
    border-radius:18px;
    box-shadow:0 16px 36px rgba(17, 41, 71, 0.12);
    border:1px solid rgba(120, 200, 255, 0.2);
    backdrop-filter: blur(10px);
}

h2 {
    text-align:center;
    color:#7dd3ff;
    margin-bottom:16px;
    font-size:32px;
    font-weight:700;
    letter-spacing:0.01em;
}

.nav-btns {
    display:flex;
    gap:8px;
    margin-bottom:20px;
}

.nav-btns button {
    flex:1;
    padding:10px 12px;
    border-radius:999px;
    background: rgba(255,255,255,0.06);
    border:1px solid rgba(120,200,255,0.25);
    color:#eaf6ff;
    font-weight:600;
    letter-spacing:0.01em;
    transition: 0.2s ease;
}

.nav-btns button:hover {
    background: rgba(77,184,255,0.2);
}

.nav-btns button.active {
    background: linear-gradient(135deg, #4db8ff, #1f7fd6);
    color: #0b1a2a;
    border: none;
}

form { display:flex; flex-direction:column; gap:12px;}

.field {
    display:flex;
    flex-direction:column;
    gap:6px;
}

.field label {
    font-size:13px;
    font-weight:600;
    color:#bcd9ee;
}

input {
    padding:12px 14px;
    border-radius:10px;
    border:1px solid rgba(120,200,255,0.25);
    background:#0f172a;
    color:#eaf6ff;
    font-size:15px;
    transition: border-color .18s ease, box-shadow .18s ease;
}

input:focus {
    outline:none;
    border-color:#7dd3ff;
    box-shadow:0 0 0 3px rgba(77,184,255,0.2);
}

input.input-error {
    border-color:#d43f3f;
    box-shadow:0 0 0 3px rgba(212,63,63,0.12);
}

input[type="file"] {
    padding: 7px 10px;
}

input[type="file"]::file-selector-button {
    border: 0;
    margin-right: 10px;
    border-radius: 9px;
    padding: 8px 12px;
    font-weight: 700;
    cursor: pointer;
    color: #081a2e;
    background: linear-gradient(135deg, #82d0ff, #4cb8ff);
    transition: 0.2s ease;
}

input[type="file"]::file-selector-button:hover {
    transform: translateY(-1px);
    filter: brightness(1.05);
}

button {
    padding:12px;
    border-radius:10px;
    border:none;
    background: linear-gradient(135deg, #4db8ff, #1f7fd6);
    color:#ffffff;
    font-weight:bold;
    font-size:15px;
    cursor:pointer;
    transition:0.2s ease;
}

button:hover { transform: translateY(-1px); filter: brightness(1.05); }

.btn {
    background: linear-gradient(135deg, #4db8ff, #1f7fd6);
    color:#0b1a2a;
    font-weight:600;
    border-radius:10px;
}

.back-link {
    display:inline-block;
    padding:10px 16px;
    border-radius:999px;
    background: rgba(77, 184, 255, 0.2);
    border:1px solid rgba(120, 200, 255, 0.35);
    color:#eaf6ff;
    font-weight:700;
    text-decoration:none;
}

.back-link:hover {
    background: rgba(77, 184, 255, 0.3);
    color:#ffffff;
}

.lang-switch {
    display:flex;
    gap:8px;
    justify-content:center;
    margin: 12px 0 18px;
}

.lang-pill {
    border:1px solid rgba(120,200,255,0.3);
    background: rgba(255,255,255,0.06);
    color:#eaf6ff;
    font-weight:700;
    font-size:12px;
    padding:6px 10px;
    border-radius:999px;
    cursor:pointer;
    transition: 0.2s ease;
}

.lang-pill.active {
    background: linear-gradient(135deg, #4db8ff, #1f7fd6);
    color:#0b1a2a;
    border:none;
}

.msg { text-align:center; font-weight:bold; margin:12px 0; font-size:14px;}
.error { color:#d43f3f;}
.success { color:#1c8f4a;}

.field-error {
    min-height:16px;
    font-size:12px;
    line-height:1.2;
    color:#d43f3f;
}

.field-hint {
    font-size:12px;
    color:#9fc8ea;
    line-height:1.2;
}

img.profile {
    width:100px; height:100px;
    border-radius:50%; object-fit:cover;
    margin:20px auto; display:block;
    border:2px solid #4da6ff;
}

.logout { text-align:center; margin-top:20px;}
.logout a { text-decoration:none; color:#2b567f; font-weight:bold;}
.logout a:hover { color:#ffffff;}

.delete-btn { background:#cf3f3f; color:#ffffff;}
.delete-btn:hover { background:#ff4d4d;}

body.light-theme.account-page {
    background: linear-gradient(140deg, #f4f8ff, #eef5ff);
    color:#1a3350;
}

body.light-theme.account-page .container {
    background: #ffffff;
    border:1px solid #dce6ff;
    box-shadow:0 18px 36px rgba(20,60,120,0.12);
}

body.light-theme.account-page h2 {
    color:#1a3350;
}

body.light-theme.account-page input {
    background:#ffffff;
    color:#1a3350;
    border:1px solid #dce6ff;
}

body.light-theme.account-page .nav-btns button {
    background:#eef3ff;
    color:#1a3350;
    border:1px solid #cfdcff;
}

body.light-theme.account-page .btn,
body.light-theme.account-page button {
    color:#ffffff;
}

body.light-theme.account-page .back-link {
    background: #eef3ff;
    color: #1a3350;
    border: 1px solid #cfdcff;
}

@media (max-width: 640px) {
    body.account-page { padding:14px; }
    .container { padding:18px; border-radius:14px; }
    h2 { font-size:28px; }
    .nav-btns { flex-direction: column; }
    .back-link { width:100%; text-align:center; }
}
</style>
</head>
<body class="account-page" data-theme-toggle="off">

<div class="container">

<div style="text-align:center; margin-bottom:10px;">
 <a href="../pages/KEBhangszerek.html" class="back-link" data-i18n="account_back">
    ← Vissza a főoldalra
 </a>
</div>

<div class="lang-switch" role="group" aria-label="Nyelv">
    <button type="button" class="lang-pill active" data-lang="hu">HU</button>
    <button type="button" class="lang-pill" data-lang="en">EN</button>
    <button type="button" class="lang-pill" data-lang="de">DE</button>
</div>

<div class="nav-btns">
    <button data-target="login" class="tab-btn active" onclick="show('login')" data-i18n="account_login_tab">Bejelentkezés</button>
    <button data-target="register" class="tab-btn" onclick="show('register')" data-i18n="account_register_tab">Regisztráció</button>
    <button data-target="admin" class="tab-btn" onclick="show('admin')" data-i18n="account_admin_tab">Admin</button>
</div>

<!-- LOGIN -->
<div id="login" style="display:block;">
<h2 data-i18n="account_login_title">Bejelentkezés</h2>
<p id="login_msg" class="msg"></p>
<form onsubmit="login(event)">
    <div class="field">
        <label for="login_user" data-i18n="account_username_or_email">Felhasználónév vagy Email</label>
        <input type="text" id="login_user" placeholder="Felhasználónév vagy Email" data-i18n-placeholder="account_username_or_email" autocomplete="username">
        <div id="login_user_error" class="field-error"></div>
    </div>
    <div class="field">
        <label for="login_pass" data-i18n="account_password">Jelszó</label>
        <input type="password" id="login_pass" placeholder="Jelszó" data-i18n-placeholder="account_password" autocomplete="current-password">
        <div id="login_pass_error" class="field-error"></div>
    </div>
    <button type="submit" data-i18n="account_login_btn">Bejelentkezés</button>
</form>
</div>

<!-- REGISTER -->
<div id="register" style="display:none;">
<h2 data-i18n="account_register_title">Regisztráció</h2>
<p id="register_msg" class="msg"></p>
<form onsubmit="registerUser(event)">
    <div id="verify_panel" style="display:none; margin-top:12px; padding:12px; border-radius:12px; border:1px solid rgba(120,200,255,0.35); background:rgba(77,184,255,0.08);">
      <div id="verify_status" style="font-weight:700;">Ellenőrzés folyamatban...</div>
      <div id="verify_email" style="opacity:0.8; margin-top:4px;"></div>
      <div id="verify_spinner" style="margin-top:8px;">...</div>
      <button type="button" id="verify_resend" style="margin-top:10px;" data-i18n="verify_resend_btn">Hitelesítő email újraküldése</button>
    </div>
    <div class="field">
        <label for="reg_user" data-i18n="account_username">Felhasználónév</label>
        <input type="text" id="reg_user" placeholder="Felhasználónév" data-i18n-placeholder="account_username" autocomplete="username">
        <div class="field-hint" data-i18n="account_username_hint">3-32 karakter, betűvel kezdődjön, csak betű/szám/._-</div>
        <div id="reg_user_error" class="field-error"></div>
    </div>
    <div class="field">
        <label for="reg_email" data-i18n="email_label">Email</label>
        <input type="email" id="reg_email" placeholder="Email" data-i18n-placeholder="email_label" autocomplete="email">
        <div id="reg_email_error" class="field-error"></div>
    </div>
    <div class="field">
        <label for="reg_pass" data-i18n="account_password">Jelszó</label>
        <input type="password" id="reg_pass" placeholder="Jelszó" data-i18n-placeholder="account_password" autocomplete="new-password">
        <div id="reg_pass_error" class="field-error"></div>
    </div>
    <button type="submit" data-i18n="account_register_btn">Regisztráció</button>
</form>
</div>

<!-- ADMIN -->
<div id="admin" style="display:none;">
<h2 data-i18n="account_admin_title">Admin bejelentkezés</h2>
<p id="admin_msg" class="msg"></p>
<form onsubmit="adminLogin(event)">
    <div class="field">
        <label for="admin_user" data-i18n="account_admin_name">Admin név</label>
        <input type="text" id="admin_user" placeholder="Admin név" data-i18n-placeholder="account_admin_name" autocomplete="username">
        <div id="admin_user_error" class="field-error"></div>
    </div>
    <div class="field">
        <label for="admin_pass" data-i18n="account_password">Jelszó</label>
        <input type="password" id="admin_pass" placeholder="Jelszó" data-i18n-placeholder="account_password" autocomplete="current-password">
        <div id="admin_pass_error" class="field-error"></div>
    </div>
    <button type="submit" data-i18n="account_admin_btn">Belépés</button>
</form>
</div>

<!-- USER PAGE -->
<div id="user_page" style="display:none;">
<h2 id="welcomeText"></h2>
<img
  id="u_pic"
  class="profile"
  src="../uploads/Default.avatar.jpg"
  alt="Profilkép"
  data-i18n="profile_title"
  onerror="this.onerror=null; this.src='../uploads/Default.avatar.jpg';">
<p id="u_email"></p>

<form onsubmit="uploadPic(event)" enctype="multipart/form-data">
    <input type="file" id="u_file">
    <button type="submit" data-i18n="account_profile_upload">Profilkép feltöltése</button>
</form>

<button class="delete-btn" onclick="deleteAccount()" data-i18n="account_delete">Fiók törlése</button>

<div class="logout">
    <a href="#" onclick="logout()" data-i18n="account_logout">Kijelentkezés</a>
</div>
</div>

</div>

<script>
const tr = (key) => (window.SH_LANG && window.SH_LANG.t ? window.SH_LANG.t(key) : key);
let currentUserName = "";
let lastRegisterMsgKey = "";

const LETTER_CLASS = "A-Za-zÁÉÍÓÖŐÚÜŰáéíóöőúüű";
const USERNAME_PATTERN = new RegExp(
    `^[${LETTER_CLASS}][${LETTER_CLASS}0-9._-]*[${LETTER_CLASS}0-9]$`
);
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function setFieldError(fieldId, message) {
    const input = document.getElementById(fieldId);
    const err = document.getElementById(fieldId + "_error");
    if (input) input.classList.toggle("input-error", !!message);
    if (err) err.textContent = message || "";
}

function clearFieldError(fieldId) {
    setFieldError(fieldId, "");
}

function byId(id) {
    return document.getElementById(id);
}

function validateUsername(value) {
    const username = (value || "").trim();
    if (!username) return tr("account_required_fields");
    if (username.length < 3 || username.length > 32) return tr("account_username_len");
    if (!USERNAME_PATTERN.test(username)) return tr("account_username_format");
    if (/[._-]{2,}/.test(username)) return tr("account_username_separators");
    const letterCount = (username.match(new RegExp(`[${LETTER_CLASS}]`, "g")) || []).length;
    if (letterCount < 3) return tr("account_username_letters");
    if (/(.)\1\1\1+/u.test(username)) return tr("account_username_repeat");
    return "";
}

function validateEmail(value) {
    const email = (value || "").trim();
    if (!email) return tr("account_required_fields");
    if (!EMAIL_PATTERN.test(email)) return tr("account_invalid_email");
    return "";
}

function validatePassword(value) {
    const password = (value || "").trim();
    if (!password) return tr("account_required_fields");
    if (password.length < 6) return tr("account_password_min");
    return "";
}

function updateWelcome(name) {
    const welcomeText = document.getElementById("welcomeText");
    if (!welcomeText) return;
    const raw = tr("account_welcome");
    const label = (raw && raw !== "account_welcome")
        ? raw.replace("{name}", name || "")
        : `Üdv, ${name || ""}`.trim();
    welcomeText.textContent = label;
}

function parseJwt(token) {
    try {
        let base64Url = token.split('.')[1];
        let base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        let jsonPayload = decodeURIComponent(atob(base64).split('').map(c =>
            '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
        ).join(''));
        return JSON.parse(jsonPayload);
    } catch { return null; }
}

function show(id) {
    ["login","register","admin","user_page"].forEach(x => {
        document.getElementById(x).style.display = "none";
    });
    document.getElementById(id).style.display = "block";

    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.classList.toggle("active", btn.dataset.target === id);
    });
}

const verifyPanel = document.getElementById("verify_panel");
const verifyStatus = document.getElementById("verify_status");
const verifyEmailEl = document.getElementById("verify_email");
const verifySpinner = document.getElementById("verify_spinner");
const verifyResend = document.getElementById("verify_resend");
let verifyTimer = null;
let verifyState = "pending";
let verifyEmailValue = "";
let verifyDetail = "";

function updateVerifyUI() {
    if (!verifyPanel) return;
    const statusText = (() => {
        if (verifyState === "success") return tr("verify_success");
        if (verifyState === "sent") return tr("verify_email_sent");
        if (verifyState === "resend_sent") return tr("verify_resend_sent");
        if (verifyState === "error") return tr("verify_resend_error") + (verifyDetail || "");
        return tr("verify_pending");
    })();
    verifyStatus.textContent = statusText;
    verifyEmailEl.textContent = verifyEmailValue ? ("Email: " + verifyEmailValue) : "";
    verifySpinner.textContent = (verifyState === "success") ? "OK" : "...";
}

function showVerifyPanel(email) {
    if (!verifyPanel) return;
    verifyPanel.style.display = "block";
    verifyEmailValue = email || "";
    verifyState = "pending";
    verifyDetail = "";
    updateVerifyUI();
    if (verifyTimer) clearInterval(verifyTimer);
    if (!email) return;
    verifyTimer = setInterval(async () => {
        try {
            const res = await fetch(`check_verification.php?email=${encodeURIComponent(email)}`);
            const data = await res.json();
            if (res.ok && data.verified) {
                clearInterval(verifyTimer);
                verifyState = "success";
                updateVerifyUI();
            }
        } catch (e) {
            // ignore polling errors
        }
    }, 4000);
}

function renderVerifyLink(link) {
    if (!verifyPanel || !link) return;
    let node = document.getElementById("verify_link_box");
    if (!node) {
        node = document.createElement("div");
        node.id = "verify_link_box";
        node.style.marginTop = "10px";
        node.style.fontSize = "12px";
        node.style.wordBreak = "break-all";
        verifyPanel.appendChild(node);
    }
    node.innerHTML = `Kézi hitelesítő link: <a href="${link}" target="_blank" rel="noopener noreferrer">${link}</a>`;
}

if (verifyResend) {
    verifyResend.onclick = async () => {
        const email = byId("reg_email")?.value?.trim() || "";
        if (!email) return;
        try {
            const res = await fetch("resend_verification.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
                body: new URLSearchParams({ email })
            });
            let text = await res.text();
            text = text.replace(/^\uFEFF/, "");
            let data = {};
            try { data = JSON.parse(text); } catch { data = { error: text }; }
            if (!res.ok) {
                verifyState = "error";
                verifyDetail = data.error || tr("bad_json");
            } else {
                verifyState = "resend_sent";
                verifyDetail = "";
            }
            updateVerifyUI();
        } catch (e) {
            verifyState = "error";
            verifyDetail = e?.message || tr("bad_json");
            updateVerifyUI();
        }
    };
}

async function login(e) {
    e.preventDefault();

    const loginInput = byId("login_user");
    const passInput = byId("login_pass");
    const msg = byId("login_msg");
    const login = loginInput?.value?.trim() || "";
    const password = passInput?.value?.trim() || "";
    clearFieldError("login_user");
    clearFieldError("login_pass");

    if (!login || !password) {
        if (!login) setFieldError("login_user", tr("account_required_fields"));
        if (!password) setFieldError("login_pass", tr("account_required_fields"));
        if (msg) msg.innerHTML = `<span class='error'>${tr("account_required_fields")}</span>`;
        return;
    }

    try {
        let res = await fetch("login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ login, password })
        });

        let text = await res.text();
        let data = {};
        try { data = JSON.parse(text); } catch { data = { error: text }; }

        if (!res.ok) {
            if (data && data.error === "email_not_verified") {
                const detail = data.detail ? ` (${data.detail})` : "";
                if (msg) msg.innerHTML = `<span class="error">Hiba: ${data.message || "Email hitelesites szukseges. Ellenorizd a postaladadat."}${detail}</span>`;
                const verifyEmail = data.email || (login.includes("@") ? login : "");
                showVerifyPanel(verifyEmail);
                if (data.verification_link) renderVerifyLink(data.verification_link);
            } else {
                const detail = data.detail ? ` (${data.detail})` : "";
                if (msg) msg.innerHTML = `<span class="error">Hiba: ${data.error || "Bejelentkezes hiba"}${detail}</span>`;
            }
            return;
        }

        if (msg) msg.innerHTML = `<span class="success">${tr("account_login_success")}</span>`;
        localStorage.setItem("token", data.token);

        let decoded = parseJwt(data.token);
        if (decoded?.data?.username) {
            currentUserName = decoded.data.username;
            updateWelcome(currentUserName);
        }

        window.location.href = "../pages/profile.html";
    } catch (err) {
        if (msg) msg.innerHTML = `<span class="error">Hiba: ${err.message || "Halozati hiba"}</span>`;
    }
}

async function registerUser(e) {
    e.preventDefault();

    const userInput = byId("reg_user");
    const emailInput = byId("reg_email");
    const passInput = byId("reg_pass");
    const msg = byId("register_msg");
    const username = userInput?.value?.trim() || "";
    const email = emailInput?.value?.trim() || "";
    const password = passInput?.value?.trim() || "";
    clearFieldError("reg_user");
    clearFieldError("reg_email");
    clearFieldError("reg_pass");

    if (!username || !email || !password) {
        if (!username) setFieldError("reg_user", tr("account_required_fields"));
        if (!email) setFieldError("reg_email", tr("account_required_fields"));
        if (!password) setFieldError("reg_pass", tr("account_required_fields"));
        if (msg) msg.innerHTML = `<span class='error'>${tr("account_required_fields")}</span>`;
        return;
    }

    const usernameErr = validateUsername(username);
    const emailErr = validateEmail(email);
    const passErr = validatePassword(password);
    if (usernameErr || emailErr || passErr) {
        if (usernameErr) setFieldError("reg_user", usernameErr);
        if (emailErr) setFieldError("reg_email", emailErr);
        if (passErr) setFieldError("reg_pass", passErr);
        if (msg) msg.innerHTML = `<span class='error'>${tr("account_fix_fields")}</span>`;
        return;
    }

    try {
        let res = await fetch("register.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username, email, password })
        });

        let text = await res.text();
        let data = {};
        try { data = JSON.parse(text); } catch { data = { error: text }; }

        if (!res.ok) {
            const detail = data.detail ? ` (${data.detail})` : "";
            const manualLink = data.verification_link
                ? `<br><a href="${data.verification_link}" target="_blank" rel="noopener noreferrer">Kézi hitelesítő link</a>`
                : "";
            if (msg) msg.innerHTML = `<span class="error">Hiba: ${data.error || "Regisztracio hiba"}${detail}${manualLink}</span>`;
            if (data.verification_link) {
                showVerifyPanel(email);
                renderVerifyLink(data.verification_link);
            }
        } else {
            if (data.token) {
                localStorage.setItem("token", data.token);
                window.location.href = "../pages/profile.html";
                return;
            }
            const sentLabel = (data.verification_sent === false)
                ? "<br><span style='color:#ffb1b1'>Email küldés nem sikerült, használd a kézi hitelesítő linket.</span>"
                : "";
            const manualLink = data.verification_link
                ? `<br><a href="${data.verification_link}" target="_blank" rel="noopener noreferrer">Kézi hitelesítő link</a>`
                : "";
            if (msg) msg.innerHTML = `<span class="success">${tr("verify_email_sent")}${sentLabel}${manualLink}</span>`;
            lastRegisterMsgKey = "verify_email_sent";
            showVerifyPanel(email);
            verifyState = "sent";
            updateVerifyUI();
            if (data.verification_link) renderVerifyLink(data.verification_link);
        }
    } catch (err) {
        if (msg) msg.innerHTML = `<span class="error">Hiba: ${err.message || "Halozati hiba"}</span>`;
    }
}

async function adminLogin(e) {
    e.preventDefault();

    const userInput = byId("admin_user");
    const passInput = byId("admin_pass");
    const msg = byId("admin_msg");
    const login = userInput?.value?.trim() || "";
    const password = passInput?.value?.trim() || "";
    clearFieldError("admin_user");
    clearFieldError("admin_pass");

    if (!login || !password) {
        if (!login) setFieldError("admin_user", tr("account_required_fields"));
        if (!password) setFieldError("admin_pass", tr("account_required_fields"));
        if (msg) msg.innerHTML = `<span class='error'>${tr("account_required_fields")}</span>`;
        return;
    }

    try {
        let res = await fetch("login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ login, password })
        });

        let text = await res.text();
        let data = {};
        try { data = JSON.parse(text); } catch { data = { error: text || "Bad response" }; }

        if (!res.ok || !data.token) {
            const detail = data.detail ? ` (${data.detail})` : "";
            if (msg) msg.innerHTML = `<span class='error'>${tr("account_invalid_login")}${detail}</span>`;
            return;
        }

        localStorage.setItem("token", data.token);
        let decoded = parseJwt(data.token);

        if (decoded?.data?.role !== "admin") {
            if (msg) msg.innerHTML = `<span class='error'>${tr("account_not_admin")}</span>`;
            localStorage.removeItem("token");
            return;
        }

        if (msg) msg.innerHTML = `<span class='success'>${tr("account_login_success")}</span>`;
        setTimeout(() => window.location.href = "../pages/Admin_dashboard.html", 500);
    } catch (err) {
        if (msg) msg.innerHTML = `<span class='error'>Hiba: ${err.message || "Halozati hiba"}</span>`;
    }
}

async function loadProfile() {
    let token = localStorage.getItem("token");
    if (!token) return;

    let res = await fetch("profile_api.php", {
        headers: { "Authorization": "Bearer " + token }
    });

    let data = await res.json();

    if (!res.ok) {
        alert(tr("account_token_expired"));
        localStorage.removeItem("token");
        show("login");
        return;
    }

    currentUserName = data.user.username;
    updateWelcome(currentUserName);
    const emailEl = byId("u_email");
    const picEl = byId("u_pic");
    if (emailEl) emailEl.textContent = data.user.email;
    if (picEl) picEl.src = data.user.profile_pic;

    show("user_page");
}

async function uploadPic(e) {
    e.preventDefault();

    const fileInput = byId("u_file");
    let file = fileInput?.files?.[0];
    if (!file) return alert(tr("account_no_file"));

    let form = new FormData();
    form.append("profile_pic", file);

    let token = localStorage.getItem("token");

    let res = await fetch("pfp.php", {
        method: "POST",
        headers: { "Authorization": "Bearer " + token },
        body: form
    });

    let text = await res.text();
    let data = {};
    try { data = JSON.parse(text); } catch { data = { message: text }; }
    if (!res.ok) {
        alert(data.message || data.error || "Feltöltés sikertelen");
        return;
    }
    alert(data.message || "Sikeres feltöltés");
    loadProfile();
}

async function deleteAccount() {
    if (!confirm(tr("confirm_delete"))) return;

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

function logout() {
    localStorage.removeItem("token");
    show("login");
}

async function hasValidToken(token) {
    if (!token) return false;
    try {
        const res = await fetch("profile_api.php", {
            headers: { "Authorization": "Bearer " + token }
        });
        if (!res.ok) return false;
        const data = await res.json().catch(() => null);
        return !!(data && data.user && data.user.id && data.user.username);
    } catch (_) {
        return false;
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    const regUserEl = document.getElementById("reg_user");
    const regEmailEl = document.getElementById("reg_email");
    const regPassEl = document.getElementById("reg_pass");
    if (regUserEl) {
        regUserEl.addEventListener("input", () => {
            const err = validateUsername(regUserEl.value);
            setFieldError("reg_user", err);
        });
    }
    if (regEmailEl) {
        regEmailEl.addEventListener("input", () => {
            const err = validateEmail(regEmailEl.value);
            setFieldError("reg_email", err);
        });
    }
    if (regPassEl) {
        regPassEl.addEventListener("input", () => {
            const err = validatePassword(regPassEl.value);
            setFieldError("reg_pass", err);
        });
    }

    const token = localStorage.getItem("token");
    if (token) {
        const ok = await hasValidToken(token);
        if (ok) {
            window.location.href = "../pages/profile.html";
            return;
        }
        localStorage.removeItem("token");
    }
    updateWelcome(currentUserName);
});

window.onLangChange = () => {
    updateWelcome(currentUserName);
    updateVerifyUI();
    const registerMsgEl = byId("register_msg");
    if (lastRegisterMsgKey && registerMsgEl) {
        registerMsgEl.innerHTML = `<span class="success">${tr(lastRegisterMsgKey)}</span>`;
    }
};
</script>
<script src="../assets/js/DarkLight.js"></script>
<script>
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
</script>

</body>
</html>




