<?php
/**
 * remove-from-shelf.php
 * 
 * This file handles removing a book from a specific shelf.
 * It's separate from add-to-shelf for clarity in the project structure.
 */

session_start();
require_once '../config/database.php';

// Check if the user is actually logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$shelf_id = $_POST['shelf_id'];
$book_id = $_POST['book_id'];

// Step 1: Verify the user owns this shelf before deleting anything
$verify_query = "SELECT shelf_id FROM shelves WHERE shelf_id = ? AND user_id = ?";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("ii", $shelf_id, $user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    // Step 2: If owned, delete the connection between the book and the shelf
    $delete_query = "DELETE FROM shelf_books WHERE shelf_id = ? AND book_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("ii", $shelf_id, $book_id);
    $delete_stmt->execute();
    $delete_stmt->close();
}

$stmt->close();
$conn->close();

// Redirect back to the shelf detail page
header("Location: ../pages/shelf-detail.php?id=" . $shelf_id);
exit();
?>