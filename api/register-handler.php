<?php
session_start();
require_once '../config/database.php';

$username = trim($_POST['username']);
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validate all fields
if (empty($username) || empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    $_SESSION['error'] = "All fields are required";
    header('Location: ../pages/register.php');
    exit();
}

// 2. Validate password requirements (length, uppercase, lowercase, number, symbol)
if (
    strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[^A-Za-z0-9]/', $password)
) {

    $_SESSION['error'] = "Password must meet all complexity requirements.";
    header('Location: ../pages/register.php');
    exit();
}

// 3. Confirm the passwords match
if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match.";
    header('Location: ../pages/register.php');
    exit();
}

// 4. Connect to the database
$conn = getDBConnection();

// 5. Check if the username or email already exists in the system
$check_query = "SELECT user_id FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['error'] = "Username or Email already taken";
    header('Location: ../pages/register.php');
    exit();
}
$stmt->close();

// 6. Hash the password for security
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// 7. Insert the new user into the database
$insert_query = "INSERT INTO users (username, first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("sssss", $username, $first_name, $last_name, $email, $password_hash);

if ($stmt->execute()) {
    // If successful, log the user in automatically by setting session variables
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['username'] = $username;
    $_SESSION['first_name'] = $first_name;
    $_SESSION['success'] = "Registration successful! Welcome to GriotShelf.";
    header('Location: ../index.php');
} else {
    $_SESSION['error'] = "Registration failed. Please try again.";
    header('Location: ../pages/register.php');
}

$stmt->close();
$conn->close();

exit();
?>