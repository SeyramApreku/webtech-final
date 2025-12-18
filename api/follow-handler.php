<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

$follower_id = $_SESSION['user_id'];
$following_id = $_POST['user_id'];
$action = $_POST['action'];

if ($follower_id == $following_id) {
    header('Location: ../pages/profile.php');
    exit();
}

$conn = getDBConnection();

if ($action == 'follow') {
    $insert_query = "INSERT IGNORE INTO follows (follower_id, following_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ii", $follower_id, $following_id);
    $stmt->execute();
    $stmt->close();
} else {
    $delete_query = "DELETE FROM follows WHERE follower_id = ? AND following_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $follower_id, $following_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
?>
