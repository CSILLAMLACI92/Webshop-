document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("loginForm");
    if (!form) {
        console.error("loginForm NOT FOUND!");
        return;
    }

    form.addEventListener("submit", async function(e) {
        e.preventDefault();

        let login = document.getElementById("login").value.trim();
        let password = document.getElementById("password").value.trim();

        const response = await fetch("../server/login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ login, password })
        });

        const data = await response.json();
        console.log("LOGIN RESPONSE:", data);

        if (data.success === true) {
            localStorage.setItem("token", data.token);
            window.location.href = "../pages/profile.html";
        } else if (data && data.error === "email_not_verified") {
            alert(data.message || "Email hitelesites szukseges. Ellenorizd a postaladadat.");
        } else {
            alert(data.error || "Hibas bejelentkezes!");
        }
    });

});
