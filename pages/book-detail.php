<?php
session_start();
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header('Location: books.php');
    exit();
}

$book_id = $_GET['id'];
$conn = getDBConnection();

// Get book details
$book_query = "SELECT b.*, 
               (SELECT AVG(rating) FROM reviews WHERE book_id = b.book_id) as avg_rating,
               (SELECT COUNT(*) FROM reviews WHERE book_id = b.book_id) as review_count
               FROM books b WHERE b.book_id = ?";
$stmt = $conn->prepare($book_query);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    header('Location: books.php');
    exit();
}

// Check if user has this book in reading list
$in_list = false;
$current_status = null;
if (isset($_SESSION['user_id'])) {
    $check_query = "SELECT status FROM reading_list WHERE user_id = ? AND book_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $_SESSION['user_id'], $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $in_list = true;
        $current_status = $result->fetch_assoc()['status'];
    }
    $stmt->close();
}

// Get reviews
$reviews_query = "SELECT r.*, u.first_name, u.last_name FROM reviews r 
                  JOIN users u ON r.user_id = u.user_id 
                  WHERE r.book_id = ? 
                  ORDER BY r.created_at DESC";
$stmt = $conn->prepare($reviews_query);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$reviews = $stmt->get_result();
$stmt->close();

// Check if user has reviewed
$user_reviewed = false;
if (isset($_SESSION['user_id'])) {
    $check_review = "SELECT review_id FROM reviews WHERE user_id = ? AND book_id = ?";
    $stmt = $conn->prepare($check_review);
    $stmt->bind_param("ii", $_SESSION['user_id'], $book_id);
    $stmt->execute();
    $user_reviewed = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}
// Get user shelves
$userShelves = [];
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $s_query = $conn->query("SELECT s.shelf_id, s.shelf_name, 
                            (SELECT COUNT(*) FROM shelf_books sb WHERE sb.shelf_id = s.shelf_id AND sb.book_id = $book_id) as in_shelf 
                            FROM shelves s WHERE s.user_id = $uid ORDER BY s.shelf_name");
    while ($s = $s_query->fetch_assoc()) {
        $userShelves[] = $s;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - GriotShelf</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <?php
                // Reliable Cover Logic: Prioritize Open Library via ISBN
                $coverUrl = '';
                if (!empty($book['isbn'])) {
                    $coverUrl = 'https://covers.openlibrary.org/b/isbn/' . $book['isbn'] . '-L.jpg';
                } elseif (!empty($book['cover_url'])) {
                    $coverUrl = $book['cover_url'];
                    if (strpos($coverUrl, 'http') !== 0 && strpos($coverUrl, '../') !== 0) {
                        $coverUrl = '../' . $coverUrl;
                    }
                }
                ?>
                <?php if ($coverUrl): ?>
                    <img src="<?php echo htmlspecialchars($coverUrl); ?>"
                        alt="<?php echo htmlspecialchars($book['title']); ?>" style="width: 100%; border-radius: 8px;">
                <?php else: ?>
                    <div style="width: 100%; height: 400px; background: linear-gradient(135deg, var(--terracotta), var(--muted-gold)); 
                                border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <p
                            style="color: white; font-family: 'Playfair Display', serif; font-size: 2rem; text-align: center; padding: 2rem;">
                            <?php echo htmlspecialchars($book['title']); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div style="margin-top: 1.5rem;">
                        <label style="font-weight: 600; margin-bottom: 0.5rem;">Reading Status:</label>
                        <div class="dropdown w-100 mb-3">
                            <button
                                class="btn btn-outline-primary w-100 dropdown-toggle d-flex justify-content-between align-items-center"
                                type="button" data-bs-toggle="dropdown">
                                <?php
                                $statusLabels = [
                                    'want_to_read' => 'Want to Read',
                                    'currently_reading' => 'Currently Reading',
                                    'finished' => 'Finished'
                                ];
                                echo $current_status ? $statusLabels[$current_status] : 'Select Status';
                                ?>
                            </button>
                            <ul class="dropdown-menu w-100 shadow">
                                <?php foreach ($statusLabels as $val => $label): ?>
                                    <li>
                                        <button
                                            class="dropdown-item btn-reading-list d-flex justify-content-between align-items-center <?php echo ($current_status == $val) ? 'active' : ''; ?>"
                                            data-book-id="<?php echo $book_id; ?>" data-status="<?php echo $val; ?>">
                                            <?php echo $label; ?>
                                            <?php if ($current_status == $val): ?>
                                                <span class="ms-2">✓</span>
                                            <?php endif; ?>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                                <?php if ($in_list): ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger btn-reading-list"
                                            data-book-id="<?php echo $book_id; ?>" data-action="remove">
                                            Remove from List
                                        </button>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Add to Shelf Dropdown -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div style="margin-top: 1.5rem;">
                            <label style="font-weight: 600; margin-bottom: 0.5rem;">Add to Shelf:</label>
                            <div class="dropdown w-100">
                                <button
                                    class="btn btn-outline-primary w-100 dropdown-toggle d-flex justify-content-between align-items-center"
                                    type="button" data-bs-toggle="dropdown">
                                    Select Shelf
                                </button>
                                <ul class="dropdown-menu w-100 shadow">
                                    <?php foreach ($userShelves as $shelf): ?>
                                        <li>
                                            <form class="add-to-shelf-form px-2 py-1">
                                                <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                                                <input type="hidden" name="shelf_id" value="<?php echo $shelf['shelf_id']; ?>">
                                                <button type="submit"
                                                    class="dropdown-item rounded d-flex justify-content-between align-items-center">
                                                    <?php echo htmlspecialchars($shelf['shelf_name']); ?>
                                                    <?php if ($shelf['in_shelf'] > 0): ?>
                                                        <span class="text-success ms-2">✓</span>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="profile.php#shelves">Create New Shelf</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="col-lg-8">
                <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 0.5rem;">
                    <?php echo htmlspecialchars($book['title']); ?>
                </h1>
                <p class="book-author" style="font-size: 1.2rem; margin-bottom: 1rem;">
                    by <?php echo htmlspecialchars($book['author']); ?>
                </p>
                <p style="color: var(--muted-gold); margin-bottom: 1.5rem;">
                    <?php echo $book['publication_year']; ?> • <?php echo htmlspecialchars($book['genre']); ?>
                </p>
                <div class="mb-3">
                    <?php
                    $avg = round($book['avg_rating'] ?? 0);
                    $review_count = $book['review_count'] ?? 0;
                    for ($i = 1; $i <= 5; $i++) {
                        echo '<span style="color: ' . ($i <= $avg ? 'var(--terracotta)' : '#ccc') . '; font-size: 1.2rem;">★</span>';
                    }
                    ?>
                    <span class="ms-2" style="font-weight: 600;">
                        <?php echo number_format($book['avg_rating'] ?? 0, 1); ?>
                    </span>
                    <span class="text-muted ms-1">(<?php echo $review_count; ?> reviews)</span>
                </div>

                <div class="row mb-4"
                    style="font-size: 0.9rem; color: var(--charcoal); opacity: 0.9; background-color: var(--warm-ivory); padding: 1rem; border-radius: 8px;">
                    <div class="col-6 mb-2"><strong>Language:</strong>
                        <?php echo htmlspecialchars($book['language'] ?? 'N/A'); ?></div>
                    <div class="col-6 mb-2"><strong>Pages:</strong>
                        <?php echo htmlspecialchars($book['page_count'] ?? 'N/A'); ?></div>
                    <div class="col-6 mb-2"><strong>Publisher:</strong>
                        <?php echo htmlspecialchars($book['publisher'] ?? 'N/A'); ?></div>
                    <div class="col-6 mb-2"><strong>ISBN:</strong>
                        <?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></div>
                </div>

                <h3 style="font-family: 'Playfair Display', serif; margin-bottom: 1rem;">About This Book</h3>
                <p style="line-height: 1.8; margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($book['description']); ?>
                </p>

                <h3 style="font-family: 'Playfair Display', serif; margin-bottom: 1rem;">Reviews</h3>

                <?php if (isset($_SESSION['user_id'])):
                    // Get user's review if it exists
                    $my_review = null;
                    if ($user_reviewed) {
                        $mr_query = "SELECT * FROM reviews WHERE user_id = ? AND book_id = ?";
                        $stmt = $conn->prepare($mr_query);
                        $stmt->bind_param("ii", $_SESSION['user_id'], $book_id);
                        $stmt->execute();
                        $my_review = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                    }
                    ?>
                    <div style="background-color: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                        <h5 style="font-family: 'Playfair Display', serif; margin-bottom: 1rem;">
                            <?php echo $my_review ? 'Edit Your Review' : 'Write a Review'; ?>
                        </h5>

                        <form action="../api/<?php echo $my_review ? 'edit-review.php' : 'review-handler.php'; ?>"
                            method="POST">
                            <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                            <?php if ($my_review): ?>
                                <input type="hidden" name="review_id" value="<?php echo $my_review['review_id']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-control" required>
                                    <option value="">Select rating...</option>
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($my_review && $my_review['rating'] == $i) ? 'selected' : ''; ?>>
                                            <?php echo str_repeat('★', $i) . str_repeat('☆', 5 - $i) . " $i stars"; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Your Review</label>
                                <textarea name="review_text" class="form-control" rows="4"
                                    required><?php echo $my_review ? htmlspecialchars($my_review['review_text']) : ''; ?></textarea>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" name="contains_spoilers" class="form-check-input" id="spoilersCheck"
                                    <?php echo ($my_review && $my_review['contains_spoilers']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="spoilersCheck">
                                    This review contains spoilers
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <?php echo $my_review ? 'Update Review' : 'Submit Review'; ?>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <?php if ($reviews->num_rows > 0): ?>
                    <?php while ($review = $reviews->fetch_assoc()): ?>
                        <div class="review-card">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div>
                                    <p class="review-username">
                                        <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                                    </p>
                                    <div class="rating-stars">
                                        <?php for ($i = 0; $i < $review['rating']; $i++): ?>★<?php endfor; ?>
                                        <?php for ($i = $review['rating']; $i < 5; $i++): ?>☆<?php endfor; ?>
                                    </div>
                                </div>
                                <small style="color: var(--charcoal); opacity: 0.7;">
                                    <?php echo date('M j, Y', strtotime($review['created_at'])); ?>
                                </small>
                            </div>
                            <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                                <?php if ($review['contains_spoilers']): ?>
                                    <span class="spoiler-badge" style="width: fit-content;">⚠️ Contains Spoilers</span>
                                <?php endif; ?>
                                <p class="<?php echo $review['contains_spoilers'] ? 'spoiler-blur' : ''; ?>"
                                    style="margin-top: 0.5rem;">
                                    <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: var(--charcoal); opacity: 0.7;">No reviews yet. Be the first to review this book!
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/interactions.js"></script>
</body>

</html>
<?php $conn->close(); ?>