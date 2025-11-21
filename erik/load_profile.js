// Token beolvasása
const token = localStorage.getItem("token");

if (!token) {
    console.log("Nincs token → nem töltök profilt");
    return;
}

// PROFILKÉP LEKÉRÉSE JWT-VEL
fetch("get_profile_pic.php", {
    method: "GET",
    headers: {
        "Authorization": "Bearer " + token
    }
})
.then(r => r.json())
.then(pic => {

    console.log("get_profile_pic.php -> ", pic);

    if (pic.status === "ok") {

        // 👤 ikon eltüntetése
        const loginIcon = document.getElementById("loginIcon");
        if (loginIcon) loginIcon.classList.add("d-none");

        // Profilkép betöltése
        const img = document.getElementById("topProfilePic");
        img.src = pic.pic;
        img.classList.remove("d-none");
    }
})
.catch(err => console.error("Hiba a profil betöltésnél:", err));
