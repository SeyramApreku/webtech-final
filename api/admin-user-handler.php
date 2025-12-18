<?php
session_start();
require_once '../config/database.php';

// Access Control
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit();
}

$conn = getDBConnection();
$action = $_POST['action'] ?? '';

if ($action === 'delete_user') {
    $user_id = (int) $_POST['user_id'];

    // Prevent self-deletion just in case UI fails
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account.";
        header('Location: ../pages/admin.php');
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User account deleted.";
    } else {
        $_SESSION['error'] = "Error deleting user: " . $conn->error;
    }
    $stmt->close();

} elseif ($action === 'delete_review') {
    $review_id = (int) $_POST['review_id'];

    $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
    $stmt->bind_param("i", $review_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Review deleted.";
    } else {
        $_SESSION['error'] = "Error deleting review: " . $conn->error;
    }
    $stmt->close();

} elseif ($action === 'add_user') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $is_admin = (int) $_POST['is_admin'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header('Location: ../pages/admin.php');
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password_hash, is_admin) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $first_name, $last_name, $username, $email, $password_hash, $is_admin);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User created successfully.";
    } else {
        // likely duplicate entry
        $_SESSION['error'] = "Error creating user (Email/Username may already exist).";
    }
    $stmt->close();

} elseif ($action === 'delete_review') {
    $review_id = (int) $_POST['review_id'];

    $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
    $stmt->bind_param("i", $review_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Review deleted.";
    } else {
        $_SESSION['error'] = "Error deleting review: " . $conn->error;
    }
    $stmt->close();
}

$conn->close();
header('Location: ../pages/admin.php');
exit();
?>