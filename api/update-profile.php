<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (empty($first_name) || empty($last_name) || empty($email) || empty($username)) {
        $_SESSION['error'] = "All fields are required.";
        header('Location: ../pages/settings.php');
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("ssssi", $first_name, $last_name, $username, $email, $user_id);

    if ($stmt->execute()) {
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['username'] = $username;
        $_SESSION['success'] = "Profile updated successfully.";
    } else {
        $_SESSION['error'] = "Update failed. Username or Email might be taken.";
    }
    $stmt->close();

} elseif ($action === 'update_privacy') {
    $wtr = isset($_POST['privacy_want_to_read']) ? 1 : 0;
    $cr = isset($_POST['privacy_currently_reading']) ? 1 : 0;
    $fin = isset($_POST['privacy_finished']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE users SET privacy_want_to_read = ?, privacy_currently_reading = ?, privacy_finished = ? WHERE user_id = ?");
    $stmt->bind_param("iiii", $wtr, $cr, $fin, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Privacy settings updated.";
    } else {
        $_SESSION['error'] = "Failed to update privacy settings.";
    }
    $stmt->close();

} elseif ($action === 'update_password') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $_SESSION['error'] = "New passwords do not match.";
        header('Location: ../pages/settings.php');
        exit();
    }

    // Verify current password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($current, $user['password_hash'])) {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $update->bind_param("si", $new_hash, $user_id);

        if ($update->execute()) {
            $_SESSION['success'] = "Password changed successfully.";
        } else {
            $_SESSION['error'] = "Password update failed.";
        }
        $update->close();
    } else {
        $_SESSION['error'] = "Incorrect current password.";
    }

} elseif ($action === 'update_reading_list_privacy') {
    $list_type = $_POST['list_type'];
    $is_public = (int) $_POST['is_public'];
    
    $field_map = [
        'want_to_read' => 'privacy_want_to_read',
        'currently_reading' => 'privacy_currently_reading',
        'finished' => 'privacy_finished'
    ];
    
    if (isset($field_map[$list_type])) {
        $field = $field_map[$list_type];
        $stmt = $conn->prepare("UPDATE users SET $field = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $is_public, $user_id);
        
        if ($stmt->execute()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            $stmt->close();
            $conn->close();
            exit();
        }
        $stmt->close();
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    $conn->close();
    exit();
}

$conn->close();
header('Location: ../pages/settings.php');
exit();
?>