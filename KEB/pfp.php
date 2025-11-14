<?php
$conn = new mysqli("localhost", "root", "", "myshop");
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Feltételezzük, hogy a bejelentkezett felhasználó ID-ja 1
$user_id = 1;

$sql = "SELECT profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
$conn->close();
?>