<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$review_id = $_POST['review_id'];
$review_text = trim($_POST['review_text']);
$rating = (int) $_POST['rating'];
$contains_spoilers = isset($_POST['contains_spoilers']) ? 1 : 0;
$user_id = $_SESSION['user_id'];

if (empty($review_text) || empty($rating)) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit();
}

$conn = getDBConnection();

// Verify ownership
$check_query = "SELECT review_id FROM reviews WHERE review_id = ? AND user_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Review not found or permission denied']);
    exit();
}
$stmt->close();

// Update
$update_query = "UPDATE reviews SET review_text = ?, rating = ?, contains_spoilers = ? WHERE review_id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("siii", $review_text, $rating, $contains_spoilers, $review_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}

$stmt->close();
$conn->close();
?>