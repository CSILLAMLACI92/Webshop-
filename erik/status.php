<?php
session_start();
header('Content-Type: application/json');
include("db.php"); // adatbázis kapcsolat

$response = ['logged_in' => false];

if(isset($_SESSION['username'])){
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE username=?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $profile_pic = 'uploads/default_avatar.png'; // alapértelmezett
    if($row = $result->fetch_assoc()){
        if(!empty($row['profile_pic'])){
            $profile_pic = 'uploads/' . $row['profile_pic']; // a fájl neve az adatbázisból
        }
    }

    $response = [
        'logged_in' => true,
        'username' => $_SESSION['username'],
        'profile_pic' => $profile_pic
    ];
}

echo json_encode($response);
?>
