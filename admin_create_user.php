<?php

include_once 'php/mysql_connection.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $password = $_POST['password'];
    $role = $_POST['role'];


    // Validate and sanitize input
    $first_name = filter_var($first_name, FILTER_SANITIZE_STRING);
    $last_name = filter_var($last_name, FILTER_SANITIZE_STRING);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $address = filter_var($address, FILTER_SANITIZE_STRING);
    $phone = filter_var($phone, FILTER_SANITIZE_STRING);
    $dob = filter_var($dob, FILTER_SANITIZE_STRING);
    $password = password_hash($password, PASSWORD_DEFAULT);
    $role = ($role === 'admin') ? 'admin' : 'user';

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, address, phone, date_of_birth, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $address, $phone, $dob, password_hash($password, PASSWORD_DEFAULT), $role);

    if ($stmt->execute()) {
        header("Location: admin.php");
        exit();
    } else {
        $error = "Error creating user: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
header("Location: admin.php?error=" . urlencode($error));
