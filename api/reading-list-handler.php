<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'];
$action = isset($_POST['action']) ? $_POST['action'] : 'add';

$conn = getDBConnection();

if ($action == 'remove') {
    $delete_query = "DELETE FROM reading_list WHERE user_id = ? AND book_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $stmt->close();
} else {
    $status = $_POST['status'];

    // Check if already exists
    $check_query = "SELECT list_id FROM reading_list WHERE user_id = ? AND book_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($exists) {
        // Update existing
        $update_query = "UPDATE reading_list SET status = ? WHERE user_id = ? AND book_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sii", $status, $user_id, $book_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert new
        $insert_query = "INSERT INTO reading_list (user_id, book_id, status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iis", $user_id, $book_id, $status);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();


if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'status' => $status ?? 'removed']);
    exit();
}

header('Location: ../pages/book-detail.php?id=' . $book_id);
exit();
?>