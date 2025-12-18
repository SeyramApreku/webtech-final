<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

$conn = getDBConnection();
$user_id = $_SESSION['user_id'] ?? null;

// Get parameters
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$region = isset($_GET['region']) ? $_GET['region'] : '';

// Build query - include reading list status, shelf info, and ratings
if ($user_id) {
    $sql = "SELECT b.*, rl.status as reading_status,
            (SELECT GROUP_CONCAT(sb.shelf_id) FROM shelf_books sb 
             JOIN shelves s ON sb.shelf_id = s.shelf_id 
             WHERE sb.book_id = b.book_id AND s.user_id = ?) as shelf_ids,
            (SELECT AVG(rating) FROM reviews WHERE book_id = b.book_id) as avg_rating,
            (SELECT COUNT(*) FROM reviews WHERE book_id = b.book_id) as review_count
            FROM books b 
            LEFT JOIN reading_list rl ON b.book_id = rl.book_id AND rl.user_id = ? 
            WHERE 1=1";
} else {
    $sql = "SELECT b.*, 
            (SELECT AVG(rating) FROM reviews WHERE book_id = b.book_id) as avg_rating,
            (SELECT COUNT(*) FROM reviews WHERE book_id = b.book_id) as review_count
            FROM books b 
            WHERE 1=1";
}

$params = [];
$types = "";

if ($user_id) {
    $params[] = $user_id; // For shelf_ids subquery
    $params[] = $user_id; // For reading_list join
    $types .= "ii";
}

if (!empty($query)) {
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ?)";
    $searchTerm = "%{$query}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if (!empty($genre)) {
    $sql .= " AND " . ($user_id ? "b." : "") . "genre = ?";
    $params[] = $genre;
    $types .= "s";
}

if (!empty($year)) {
    $sql .= " AND " . ($user_id ? "b." : "") . "publication_year = ?";
    $params[] = $year;
    $types .= "i";
}

if (!empty($region)) {
    $sql .= " AND " . ($user_id ? "b." : "") . "region = ?";
    $params[] = $region;
    $types .= "s";
}

$sql .= " ORDER BY " . ($user_id ? "b." : "") . "title ASC";

// Execute
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$books = [];
while ($row = $result->fetch_assoc()) {
    if (!empty($row['isbn'])) {
        $row['cover_url'] = 'https://covers.openlibrary.org/b/isbn/' . $row['isbn'] . '-L.jpg';
    }
    // Parse shelf_ids
    $row['shelves'] = [];
    if (!empty($row['shelf_ids'])) {
        $row['shelves'] = array_map('intval', explode(',', $row['shelf_ids']));
    }
    $books[] = $row;
}

echo json_encode($books);

$stmt->close();
$conn->close();
?>