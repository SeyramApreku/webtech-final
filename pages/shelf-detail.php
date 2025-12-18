<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';
$shelf_id = $_GET['id'];
$conn = getDBConnection();

// Get shelf info
$shelf_query = "SELECT * FROM shelves WHERE shelf_id = ? AND user_id = ?";
$stmt = $conn->prepare($shelf_query);
$stmt->bind_param("ii", $shelf_id, $_SESSION['user_id']);
$stmt->execute();
$shelf = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$shelf) {
    header('Location: profile.php#shelves');
    exit();
}

// Get books in shelf
$books_query = "SELECT b.*, sb.id as shelf_book_id FROM shelf_books sb 
                JOIN books b ON sb.book_id = b.book_id 
                WHERE sb.shelf_id = ? 
                ORDER BY sb.added_at DESC";
$stmt = $conn->prepare($books_query);
$stmt->bind_param("i", $shelf_id);
$stmt->execute();
$books = $stmt->get_result();
$stmt->close();

// Get all books for "Add Books" modal
$all_books = $conn->query("SELECT * FROM books ORDER BY title");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($shelf['shelf_name']); ?> - GriotShelf</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 0.5rem;">
                    <?php echo htmlspecialchars($shelf['shelf_name']); ?>
                </h1>
                <p style="color: var(--charcoal); opacity: 0.8;">
                    <?php echo htmlspecialchars($shelf['description']); ?>
                </p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBooksModal">
                Add Books
            </button>
        </div>

        <?php if ($books->num_rows > 0): ?>
            <div class="row">
                <?php while ($book = $books->fetch_assoc()): ?>
                    <div class="col-md-3 mb-4">
                        <div class="book-card">
                            <a href="book-detail.php?id=<?php echo $book['book_id']; ?>">
                                <?php
                                $coverUrl = $book['cover_url'];
                                if (empty($coverUrl) && !empty($book['isbn'])) {
                                    $coverUrl = 'https://covers.openlibrary.org/b/isbn/' . $book['isbn'] . '-L.jpg';
                                }
                                if ($coverUrl && strpos($coverUrl, 'http') !== 0 && strpos($coverUrl, '../') !== 0) {
                                    $coverUrl = '../' . $coverUrl;
                                }
                                ?>
                                <?php if ($coverUrl): ?>
                                    <img src="<?php echo htmlspecialchars($coverUrl); ?>"
                                        style="width: 100%; height: 250px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem;"
                                        alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <?php else: ?>
                                    <div
                                        style="width: 100%; height: 250px; background: linear-gradient(135deg, var(--terracotta), var(--muted-gold)); 
                                                border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center;">
                                        <p
                                            style="color: white; font-family: 'Playfair Display', serif; font-size: 1.5rem; text-align: center; padding: 1rem;">
                                            <?php echo htmlspecialchars($book['title']); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <h6><?php echo htmlspecialchars($book['title']); ?></h6>
                            <p class="book-author"><?php echo htmlspecialchars($book['author']); ?></p>

                            <form action="../api/remove-from-shelf.php" method="POST">
                                <input type="hidden" name="shelf_id" value="<?php echo $shelf_id; ?>">
                                <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                <button type="submit" class="btn btn-outline-dark btn-sm w-100">Remove from Shelf</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="background-color: var(--soft-sand); padding: 3rem; border-radius: 8px; text-align: center;">
                <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">This shelf is empty</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBooksModal">
                    Add Books to Shelf
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Books Modal -->
    <div class="modal fade" id="addBooksModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Books to Shelf</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <?php while ($all_book = $all_books->fetch_assoc()): ?>
                            <div class="col-md-6 mb-3">
                                <div
                                    style="display: flex; gap: 1rem; background-color: var(--soft-sand); padding: 1rem; border-radius: 8px;">
                                    <?php if ($all_book['cover_url']): ?>
                                        <img src="<?php echo htmlspecialchars($all_book['cover_url']); ?>"
                                            style="width: 60px; height: 80px; object-fit: cover; border-radius: 4px;">
                                    <?php endif; ?>
                                    <div style="flex: 1;">
                                        <h6 style="margin-bottom: 0.25rem;">
                                            <?php echo htmlspecialchars($all_book['title']); ?>
                                        </h6>
                                        <p class="book-author" style="margin-bottom: 0.5rem;">
                                            <?php echo htmlspecialchars($all_book['author']); ?>
                                        </p>
                                        <form action="../api/add-to-shelf.php" method="POST" style="margin: 0;"
                                            class="add-to-shelf-form">
                                            <input type="hidden" name="shelf_id" value="<?php echo $shelf_id; ?>">
                                            <input type="hidden" name="book_id" value="<?php echo $all_book['book_id']; ?>">
                                            <button type="submit" class="btn btn-primary btn-sm">Add</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="../js/interactions.js"></script>
</body>

</html>
<?php $conn->close(); ?>