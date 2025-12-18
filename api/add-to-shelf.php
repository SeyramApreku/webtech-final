<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

$shelf_id = $_POST['shelf_id'];
$book_id = $_POST['book_id'];

// Connect to the database using our helper function
$conn = getDBConnection();

try {
    // Step 1: Verify the shelf belongs to the logged-in user
    // We don't want people adding books to shelves they don't own!
    $verify_query = "SELECT shelf_id FROM shelves WHERE shelf_id = ? AND user_id = ?";
    $stmt = $conn->prepare($verify_query);
    // Error handling for prepare statement
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $shelf_id, $_SESSION['user_id']);
    $stmt->execute();

    if ($stmt->get_result()->num_rows == 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'You do not have permission for this shelf.']);
        exit();
    }
    $stmt->close();

    // Step 2: Check if the book is already in the shelf
    $check_query = "SELECT id FROM shelf_books WHERE shelf_id = ? AND book_id = ?";
    $stmt = $conn->prepare($check_query);
    // Error handling for prepare statement
    if (!$stmt) {
        throw new Exception("Prepare failed (check): " . $conn->error);
    }
    $stmt->bind_param("ii", $shelf_id, $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $action_taken = "";
    if ($result->num_rows > 0) {
        // Step 3a: If it is already there, remove it (toggle behavior)
        $delete_query = "DELETE FROM shelf_books WHERE shelf_id = ? AND book_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        // Error handling for prepare statement
        if (!$delete_stmt) {
            throw new Exception("Prepare failed (delete): " . $conn->error);
        }
        $delete_stmt->bind_param("ii", $shelf_id, $book_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        $action_taken = "removed";
    } else {
        // Step 3b: If it's not there, add it
        $insert_query = "INSERT INTO shelf_books (shelf_id, book_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        // Error handling for prepare statement
        if (!$insert_stmt) {
            throw new Exception("Prepare failed (insert): " . $conn->error);
        }
        $insert_stmt->bind_param("ii", $shelf_id, $book_id);
        $insert_stmt->execute();
        $insert_stmt->close();
        $action_taken = "added";
    }
    $stmt->close(); // Close the statement used for checking existence

    // Success! Send a simple message back to the frontend
    echo json_encode(['success' => true, 'action' => $action_taken]);
} catch (Exception $e) {
    // If something goes wrong, send an error message
    echo json_encode(['success' => false, 'message' => 'Something went wrong on the server.']);
}

$conn->close();
?>