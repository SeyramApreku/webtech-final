<?php
session_start();
require_once '../config/database.php';

$email = trim($_POST['email']);
$password = $_POST['password'];

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Please enter both email and password";
    header('Location: ../pages/login.php');
    exit();
}

$conn = getDBConnection();

if (!$conn) {
    $_SESSION['error'] = "Database connection failed.";
    header('Location: ../pages/login.php');
    exit();
}

$query = "SELECT user_id, username, first_name, last_name, email, password_hash, is_admin FROM users WHERE email = ? OR username = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    $_SESSION['error'] = "Database error: " . $conn->error . " (If this is a new deployment, you might need to import the database schema).";
    header('Location: ../pages/login.php');
    exit();
}

$stmt->bind_param("ss", $email, $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    $_SESSION['error'] = "Query failed: " . $conn->error;
    header('Location: ../pages/login.php');
    exit();
}

if ($result->num_rows === 0) {
    $_SESSION['error'] = "No account found with that email";
    header('Location: ../pages/login.php');
    exit();
}

$user = $result->fetch_assoc();

if (password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['is_admin'] = (bool) $user['is_admin'];

    header('Location: ../index.php');
} else {
    $_SESSION['error'] = "Incorrect password";
    header('Location: ../pages/login.php');
}

$stmt->close();
$conn->close();
?>