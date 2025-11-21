document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("loginForm");
    if (!form) {
        console.error("❌ loginForm NOT FOUND!");
        return;
    }

    form.addEventListener("submit", async function(e) {
        e.preventDefault();

        let login = document.getElementById("login").value.trim();
        let password = document.getElementById("password").value.trim();

        const response = await fetch("login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ login, password })
        });

        const data = await response.json();
        console.log("LOGIN RESPONSE:", data);

        if (data.success === true) {
            // TOKEN ELMENTÉSE
            localStorage.setItem("token", data.token);

            // Átirányítás
            window.location.href = "KEBhangszerek.html";
        } else {
            alert(data.error || "Hibás bejelentkezés!");
        }
    });

});
