<?php
include_once 'php/mysql_connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    if (!is_numeric($user_id)) {
        header("Location: admin.php?error=" . urlencode("Invalid user ID."));
        exit();
    }

    $user_id = intval($user_id);
    if ($user_id == $_SESSION['user_id']) {
        // Prevent toggling status for the logged-in admin
        header("Location: admin.php?error=" . urlencode("You cannot toggle your own status."));
        exit();
    }

    // Toggle user status
    $stmt = $conn->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $new_status = $user['is_active'] ? 0 : 1;

        $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $user_id);

        if ($stmt->execute()) {
            header("Location: admin.php");
            exit();
        } else {
            $error = "Error updating user status: " . $stmt->error;
        }
    } else {
        $error = "User not found.";
    }

    $stmt->close();
}

$conn->close();
