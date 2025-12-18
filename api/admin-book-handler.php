<?php
session_start();
require_once '../config/database.php';

// Access Control
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit();
}

$conn = getDBConnection();
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $year = (int) $_POST['publication_year'];
    $genre = trim($_POST['genre']);
    $region = trim($_POST['region']);
    $desc = trim($_POST['description']);
    $cover = trim($_POST['cover_url']);

    $stmt = $conn->prepare("INSERT INTO books (title, author, publication_year, genre, region, description, cover_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissss", $title, $author, $year, $genre, $region, $desc, $cover);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Book added successfully.";
    } else {
        $_SESSION['error'] = "Error adding book: " . $conn->error;
    }
    $stmt->close();

} elseif ($action === 'edit') {
    $book_id = (int) $_POST['book_id'];
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $year = (int) $_POST['publication_year'];
    $genre = trim($_POST['genre']);
    $region = trim($_POST['region']);
    $desc = trim($_POST['description']);
    $cover = trim($_POST['cover_url']);

    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, publication_year=?, genre=?, region=?, description=?, cover_url=? WHERE book_id=?");
    $stmt->bind_param("ssissssi", $title, $author, $year, $genre, $region, $desc, $cover, $book_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Book updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating book: " . $conn->error;
    }
    $stmt->close();

} elseif ($action === 'delete') {
    $book_id = (int) $_POST['book_id'];

    $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Book deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting book: " . $conn->error;
    }
    $stmt->close();
}

$conn->close();
header('Location: ../pages/admin.php');
exit();
?>