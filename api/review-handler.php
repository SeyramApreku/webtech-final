<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'];
$rating = $_POST['rating'];
$review_text = trim($_POST['review_text']);
$contains_spoilers = isset($_POST['contains_spoilers']) ? 1 : 0;

if (empty($review_text) || $rating < 1 || $rating > 5) {
    header('Location: ../pages/book-detail.php?id=' . $book_id);
    exit();
}

$conn = getDBConnection();

// Insert review (multiple reviews allowed)
$insert_query = "INSERT INTO reviews (user_id, book_id, rating, review_text, contains_spoilers) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("iiisi", $user_id, $book_id, $rating, $review_text, $contains_spoilers);
$stmt->execute();
$stmt->close();

$conn->close();

if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}

header('Location: ../pages/book-detail.php?id=' . $book_id);
exit();
?>