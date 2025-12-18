<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

$review_id = $_POST['review_id'];
$user_id = $_SESSION['user_id'];

$conn = getDBConnection();

// Delete only if user owns the review
$delete_query = "DELETE FROM reviews WHERE review_id = ? AND user_id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: ../pages/profile.php');
exit();
?>