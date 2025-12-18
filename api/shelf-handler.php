<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $shelf_id = $_POST['shelf_id'];

    $delete_query = "DELETE FROM shelves WHERE shelf_id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $shelf_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header('Location: ../pages/profile.php#shelves');
} elseif (isset($_POST['action']) && $_POST['action'] == 'update_privacy') {
    $shelf_id = (int) $_POST['shelf_id'];
    $is_public = (int) $_POST['is_public'];

    $update_query = "UPDATE shelves SET is_public = ? WHERE shelf_id = ? AND user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("iii", $is_public, $shelf_id, $user_id);
    $success = $stmt->execute();
    $stmt->close();

    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit();
    }

    header('Location: ../pages/profile.php#shelves');
} else {
    $shelf_name = trim($_POST['shelf_name']);
    $description = trim($_POST['description']);
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    $insert_query = "INSERT INTO shelves (user_id, shelf_name, description, is_public) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("issi", $user_id, $shelf_name, $description, $is_public);
    $stmt->execute();
    $stmt->close();

    header('Location: ../pages/profile.php#shelves');
}

$conn->close();
exit();
?>