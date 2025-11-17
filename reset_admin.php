<?php
require_once "connect.php";

$plain = "KEBDEV"; // <<< EZ LESZ A JELSZÓ

$hash = password_hash($plain, PASSWORD_BCRYPT);

echo "<pre>";
echo "GENERATED HASH:\n" . $hash . "\n\n";

// Ha már létezik admin user:
$sql = "UPDATE users SET password_hash = ?, role = 'admin' WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hash);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Admin jelszó FRISSÍTVE erre: {$plain}\n";
} else {
    echo "NINCS admin user, létrehozom...\n";

    $sql2 = "INSERT INTO users (username, email, password_hash, role, profile_pic)
             VALUES ('admin', 'admin@example.com', ?, 'admin', 'uploads/default.png')";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("s", $hash);
    $stmt2->execute();

    if ($stmt2->affected_rows > 0) {
        echo "Admin user LÉTREHOZVA. Jelszó: {$plain}\n";
    } else {
        echo "Valami nagyon félrement az INSERT-nél.\n";
    }
}

echo "</pre>";
