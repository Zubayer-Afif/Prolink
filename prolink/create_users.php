<?php
include 'config/db.php';

// Default passwords
$clientPass = password_hash("client123", PASSWORD_DEFAULT);
$freelancerPass = password_hash("free123", PASSWORD_DEFAULT);

// Insert client
$stmt1 = $conn->prepare("INSERT INTO users (email, password_hash, type) VALUES (?, ?, ?)");
$email1 = "client@gmail.com";
$type1 = "client";
$stmt1->bind_param("sss", $email1, $clientPass, $type1);
$stmt1->execute();

// Insert freelancer
$stmt2 = $conn->prepare("INSERT INTO users (email, password_hash, type) VALUES (?, ?, ?)");
$email2 = "freelancer@gmail.com";
$type2 = "freelancer";
$stmt2->bind_param("sss", $email2, $freelancerPass, $type2);
$stmt2->execute();

echo "Users created successfully!";
?>