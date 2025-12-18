<?php
session_start();
require_once '../config/database.php';

// Check if viewing another user or self
$viewer_id = $_SESSION['user_id'] ?? 0;
$profile_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : $viewer_id;

// If no profile ID determined (guest viewing nothing?), redirect to login or search
if ($profile_id === 0) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$is_own_profile = ($viewer_id === $profile_id);

// Get user info and privacy settings
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$user_res = $stmt->get_result();

if ($user_res->num_rows === 0) {
    echo "User not found.";
    exit();
}

$user = $user_res->fetch_assoc();
$stmt->close();

// Check Privacy Settings
$show_wtr = $is_own_profile || $user['privacy_want_to_read'];
$show_cr = $is_own_profile || $user['privacy_currently_reading'];
$show_fin = $is_own_profile || $user['privacy_finished'];

// Get reviews (Always public?) - Let's assume reviews are public
$reviews_query = "SELECT r.*, b.title, b.author, b.book_id FROM reviews r 
                  JOIN books b ON r.book_id = b.book_id 
                  WHERE r.user_id = ? 
                  ORDER BY r.created_at DESC";
$stmt = $conn->prepare($reviews_query);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$reviews = $stmt->get_result();
$review_count = $reviews->num_rows;
$stmt->close();

// Get Want to Read
$want_count = 0;
$want_books = null;
if ($show_wtr) {
    $want_query = "SELECT b.*, rl.list_id FROM reading_list rl 
                   JOIN books b ON rl.book_id = b.book_id 
                   WHERE rl.user_id = ? AND rl.status = 'want_to_read' 
                   ORDER BY rl.added_at DESC";
    $stmt = $conn->prepare($want_query);
    $stmt->bind_param("i", $profile_id);
    $stmt->execute();
    $want_books = $stmt->get_result();
    $want_count = $want_books->num_rows;
    $stmt->close();
}

// Get Currently Reading
$reading_count = 0;
$reading_books = null;
if ($show_cr) {
    $reading_query = "SELECT b.*, rl.list_id FROM reading_list rl 
                      JOIN books b ON rl.book_id = b.book_id 
                      WHERE rl.user_id = ? AND rl.status = 'currently_reading' 
                      ORDER BY rl.added_at DESC";
    $stmt = $conn->prepare($reading_query);
    $stmt->bind_param("i", $profile_id);
    $stmt->execute();
    $reading_books = $stmt->get_result();
    $reading_count = $reading_books->num_rows;
    $stmt->close();
}

// Get Finished
$finished_count = 0;
$finished_books = null;
if ($show_fin) {
    $finished_query = "SELECT b.*, rl.list_id FROM reading_list rl 
                       JOIN books b ON rl.book_id = b.book_id 
                       WHERE rl.user_id = ? AND rl.status = 'finished' 
                       ORDER BY rl.added_at DESC";
    $stmt = $conn->prepare($finished_query);
    $stmt->bind_param("i", $profile_id);
    $stmt->execute();
    $finished_books = $stmt->get_result();
    $finished_count = $finished_books->num_rows;
    $stmt->close();
}

// Get custom shelves (Filter private if not own)
$shelves_query = "SELECT s.*, COUNT(sb.book_id) as book_count 
                  FROM shelves s 
                  LEFT JOIN shelf_books sb ON s.shelf_id = sb.shelf_id 
                  WHERE s.user_id = ?";

if (!$is_own_profile) {
    $shelves_query .= " AND s.is_public = 1";
}

$shelves_query .= " GROUP BY s.shelf_id ORDER BY s.created_at DESC";
$stmt = $conn->prepare($shelves_query);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$shelves = $stmt->get_result();
$shelves_count = $shelves->num_rows;
$stmt->close();

// Get Followers
$followers_query = "SELECT u.user_id, u.first_name, u.last_name, u.username 
                    FROM follows f 
                    JOIN users u ON f.follower_id = u.user_id 
                    WHERE f.following_id = ?";
$stmt = $conn->prepare($followers_query);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$followers = $stmt->get_result();
$followers_count = $followers->num_rows;
$stmt->close();

// Get Following
$following_query = "SELECT u.user_id, u.first_name, u.last_name, u.username 
                    FROM follows f 
                    JOIN users u ON f.following_id = u.user_id 
                    WHERE f.follower_id = ?";
$stmt = $conn->prepare($following_query);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$following = $stmt->get_result();
$following_count = $following->num_rows;
$stmt->close();

// Check if viewer follows profile user
$is_following = false;
if ($viewer_id && !$is_own_profile) {
    $stmt = $conn->prepare("SELECT * FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->bind_param("ii", $viewer_id, $profile_id);
    $stmt->execute();
    $is_following = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $is_own_profile ? "My Profile" : $user['username'] . "'s Profile"; ?> - GriotShelf
    </title>
    <script src="../js/interactions.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>
    <div style="background: linear-gradient(135deg, var(--warm-ivory) 0%, var(--soft-sand) 100%); padding: 3rem 0;">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 0.5rem;">
                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                </h1>
                <p style="color: var(--charcoal); opacity: 0.8; margin-bottom: 0.5rem;">
                    @<?php echo htmlspecialchars($user['username']); ?>
                    <?php if ($is_own_profile): ?>
                        • <?php echo htmlspecialchars($user['email']); ?>
                    <?php endif; ?>
                </p>
                <div class="d-flex gap-3 mt-2">
                    <a href="#followers" data-bs-toggle="tab"
                        style="text-decoration: none; color: var(--charcoal);">
                        <strong><?php echo $followers_count; ?></strong> <span style="opacity: 0.7;">Followers</span>
                    </a>
                    <a href="#following" data-bs-toggle="tab"
                        style="text-decoration: none; color: var(--charcoal);">
                        <strong><?php echo $following_count; ?></strong> <span style="opacity: 0.7;">Following</span>
                    </a>
                </div>
            </div>
            <div>
                <?php if (!$is_own_profile && $viewer_id): ?>
                    <form action="../api/follow-handler.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $profile_id; ?>">
                        <input type="hidden" name="redirect" value="profile.php?user_id=<?php echo $profile_id; ?>">
                        <?php if ($is_following): ?>
                            <input type="hidden" name="action" value="unfollow">
                            <button type="submit" class="btn btn-outline-dark">Unfollow</button>
                        <?php else: ?>
                            <input type="hidden" name="action" value="follow">
                            <button type="submit" class="btn btn-primary">Follow</button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews"
                    type="button">
                    Reviews (<?php echo $review_count; ?>)
                </button>
            </li>

            <?php if ($show_wtr): ?>
                <li class="nav-item">
                    <button class="nav-link" id="want-tab" data-bs-toggle="tab" data-bs-target="#want" type="button">
                        Want to Read (<?php echo $want_count; ?>)
                    </button>
                </li>
            <?php endif; ?>

            <?php if ($show_cr): ?>
                <li class="nav-item">
                    <button class="nav-link" id="reading-tab" data-bs-toggle="tab" data-bs-target="#reading" type="button">
                        Currently Reading (<?php echo $reading_count; ?>)
                    </button>
                </li>
            <?php endif; ?>

            <?php if ($show_fin): ?>
                <li class="nav-item">
                    <button class="nav-link" id="finished-tab" data-bs-toggle="tab" data-bs-target="#finished"
                        type="button">
                        Finished (<?php echo $finished_count; ?>)
                    </button>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <button class="nav-link" id="shelves-tab" data-bs-toggle="tab" data-bs-target="#shelves" type="button">
                    Shelves (<?php echo $shelves_count; ?>)
                </button>
            </li>

            <li class="nav-item">
                <button class="nav-link" id="followers-tab" data-bs-toggle="tab" data-bs-target="#followers"
                    type="button">
                    Followers (<?php echo $followers_count; ?>)
                </button>
            </li>

            <li class="nav-item">
                <button class="nav-link" id="following-tab" data-bs-toggle="tab" data-bs-target="#following"
                    type="button">
                    Following (<?php echo $following_count; ?>)
                </button>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content" id="profileTabsContent">

            <!-- Reviews Tab -->
            <div class="tab-pane fade show active" id="reviews">
                <?php if ($review_count > 0): ?>
                    <?php $reviews->data_seek(0);
                    while ($review = $reviews->fetch_assoc()): ?>
                        <div class="review-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 style="font-family: 'Playfair Display', serif;">
                                        <a href="book-detail.php?id=<?php echo $review['book_id']; ?>"
                                            style="color: var(--terracotta); text-decoration: none;">
                                            <?php echo htmlspecialchars($review['title']); ?>
                                        </a>
                                    </h5>
                                    <div class="rating-stars">
                                        <?php for ($i = 0; $i < $review['rating']; $i++): ?>★<?php endfor; ?>
                                        <?php for ($i = $review['rating']; $i < 5; $i++): ?>☆<?php endfor; ?>
                                    </div>
                                </div>
                                <?php if ($is_own_profile): ?>
                                    <form action="../api/delete-review.php" method="POST" onsubmit="return confirm('Delete?');">
                                        <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <p class="<?php echo $review['contains_spoilers'] ? 'spoiler-blur' : ''; ?> mt-2">
                                <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No reviews yet.</p>
                <?php endif; ?>
            </div>

            <!-- Want to Read -->
            <?php if ($show_wtr): ?>
                <div class="tab-pane fade" id="want">
                    <?php if ($is_own_profile): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Want to Read</h5>
                                <span class="small text-muted me-2">Private</span>
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input reading-list-privacy-toggle" type="checkbox" 
                                           data-list-type="want_to_read" 
                                           <?php echo $user['privacy_want_to_read'] ? 'checked' : ''; ?>>
                                </div>
                                <span class="small text-muted ms-1">Public</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($want_count > 0): ?>
                        <div class="row">
                            <?php $want_books->data_seek(0);
                            while ($book = $want_books->fetch_assoc()): ?>
                                <div class="col-md-3 mb-4">
                                    <div class="book-card">
                                        <a href="book-detail.php?id=<?php echo $book['book_id']; ?>">
                                            <?php 
                                            $coverUrl = $book['cover_url'];
                                            if (empty($coverUrl) && !empty($book['isbn'])) {
                                                $coverUrl = 'https://covers.openlibrary.org/b/isbn/' . $book['isbn'] . '-L.jpg';
                                            }
                                            ?>
                                            <?php if ($coverUrl): ?>
                                                <img src="<?php echo htmlspecialchars($coverUrl); ?>"
                                                    class="w-100 rounded mb-2" style="height: 200px; object-fit: cover;">
                                            <?php else: ?>
                                                <div style="width: 100%; height: 200px; background: linear-gradient(135deg, var(--terracotta), var(--muted-gold)); 
                                                            border-radius: 8px; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                                    <p style="color: white; font-family: 'Playfair Display', serif; font-size: 1.2rem; text-align: center; padding: 1rem;">
                                                        <?php echo htmlspecialchars($book['title']); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($book['title']); ?></h6>
                                        <?php if ($is_own_profile): ?>
                                            <form action="../api/reading-list-handler.php" method="POST" class="mt-2">
                                                <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                                <input type="hidden" name="action" value="remove">
                                                <button class="btn btn-sm btn-outline-danger w-100">Remove</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">List is empty.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Currently Reading -->
            <?php if ($show_cr): ?>
                <div class="tab-pane fade" id="reading">
                    <?php if ($is_own_profile): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Currently Reading</h5>
                                <span class="small text-muted me-2">Private</span>
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input reading-list-privacy-toggle" type="checkbox" 
                                           data-list-type="currently_reading" 
                                           <?php echo $user['privacy_currently_reading'] ? 'checked' : ''; ?>>
                                </div>
                                <span class="small text-muted ms-1">Public</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($reading_count > 0): ?>
                        <div class="row">
                            <?php $reading_books->data_seek(0);
                            while ($book = $reading_books->fetch_assoc()): ?>
                                <div class="col-md-3 mb-4">
                                    <div class="book-card">
                                        <a href="book-detail.php?id=<?php echo $book['book_id']; ?>">
                                            <?php 
                                            $coverUrl = $book['cover_url'];
                                            if (empty($coverUrl) && !empty($book['isbn'])) {
                                                $coverUrl = 'https://covers.openlibrary.org/b/isbn/' . $book['isbn'] . '-L.jpg';
                                            }
                                            ?>
                                            <?php if ($coverUrl): ?>
                                                <img src="<?php echo htmlspecialchars($coverUrl); ?>"
                                                    class="w-100 rounded mb-2" style="height: 200px; object-fit: cover;">
                                            <?php else: ?>
                                                <div style="width: 100%; height: 200px; background: linear-gradient(135deg, var(--terracotta), var(--muted-gold)); 
                                                            border-radius: 8px; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                                    <p style="color: white; font-family: 'Playfair Display', serif; font-size: 1.2rem; text-align: center; padding: 1rem;">
                                                        <?php echo htmlspecialchars($book['title']); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($book['title']); ?></h6>
                                        <?php if ($is_own_profile): ?>
                                            <form action="../api/reading-list-handler.php" method="POST" class="mt-2">
                                                <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                                <input type="hidden" name="action" value="remove">
                                                <button class="btn btn-sm btn-outline-danger w-100">Remove</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">List is empty.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Finished -->
            <?php if ($show_fin): ?>
                <div class="tab-pane fade" id="finished">
                    <?php if ($is_own_profile): ?>
                    <?php if ($is_own_profile): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Finished</h5>
                            <div class="d-flex align-items-center">
                                <span class="small text-muted me-2">Private</span>
                                <div class="form-check form-switch d-inline-block m-0 min-h-0">
                                    <input class="form-check-input reading-list-privacy-toggle" type="checkbox" 
                                           data-list-type="finished" 
                                           <?php echo $user['privacy_finished'] ? 'checked' : ''; ?>>
                                </div>
                                <span class="small text-muted ms-2">Public</span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if ($finished_count > 0): ?>
                        <div class="row">
                            <?php $finished_books->data_seek(0);
                            while ($book = $finished_books->fetch_assoc()): ?>
                                <div class="col-md-3 mb-4">
                                    <div class="book-card">
                                        <a href="book-detail.php?id=<?php echo $book['book_id']; ?>">
                                            <?php 
                                            $coverUrl = $book['cover_url'];
                                            if (empty($coverUrl) && !empty($book['isbn'])) {
                                                $coverUrl = 'https://covers.openlibrary.org/b/isbn/' . $book['isbn'] . '-L.jpg';
                                            }
                                            ?>
                                            <?php if ($coverUrl): ?>
                                                <img src="<?php echo htmlspecialchars($coverUrl); ?>"
                                                    class="w-100 rounded mb-2" style="height: 200px; object-fit: cover;">
                                            <?php else: ?>
                                                <div style="width: 100%; height: 200px; background: linear-gradient(135deg, var(--terracotta), var(--muted-gold)); 
                                                            border-radius: 8px; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                                    <p style="color: white; font-family: 'Playfair Display', serif; font-size: 1.2rem; text-align: center; padding: 1rem;">
                                                        <?php echo htmlspecialchars($book['title']); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($book['title']); ?></h6>
                                        <?php if ($is_own_profile): ?>
                                            <form action="../api/reading-list-handler.php" method="POST" class="mt-2">
                                                <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                                <input type="hidden" name="action" value="remove">
                                                <button class="btn btn-sm btn-outline-danger w-100">Remove</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">List is empty.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Shelves -->
            <div class="tab-pane fade" id="shelves">
                <?php if ($is_own_profile): ?>
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createShelfModal">
                        Create New Shelf
                    </button>
                <?php endif; ?>

                <?php if ($shelves_count > 0): ?>
                    <div class="row">
                        <?php $shelves->data_seek(0);
                        while ($shelf = $shelves->fetch_assoc()): ?>
                            <div class="col-md-4 mb-3">
                                <div class="book-card">
                                    <h5><?php echo htmlspecialchars($shelf['shelf_name']); ?></h5>
                                    <p class="text-muted small"><?php echo $shelf['book_count']; ?> books</p>
                                    
                                    <?php if ($is_own_profile): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="small text-muted me-2">Private</span>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input privacy-toggle" type="checkbox" 
                                                       data-shelf-id="<?php echo $shelf['shelf_id']; ?>"
                                                       <?php echo $shelf['is_public'] ? 'checked' : ''; ?>>
                                            </div>
                                            <span class="small text-muted ms-1">Public</span>
                                        </div>
                                    <?php elseif (!$shelf['is_public']): ?>
                                        <span class="badge bg-secondary mb-2">Private</span>
                                    <?php endif; ?>

                                    <a href="shelf-detail.php?id=<?php echo $shelf['shelf_id']; ?>"
                                        class="btn btn-sm btn-outline-primary d-block">View Shelf</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No shelves found.</p>
                <?php endif; ?>
            </div>

            <!-- Followers Tab -->
            <div class="tab-pane fade" id="followers">
                <?php if ($followers_count > 0): ?>
                    <div class="row">
                        <?php $followers->data_seek(0); while ($f = $followers->fetch_assoc()): ?>
                            <div class="col-md-4 mb-3">
                                <div class="book-card">
                                    <h6><?php echo htmlspecialchars($f['first_name'] . ' ' . $f['last_name']); ?></h6>
                                    <p class="text-muted small mb-2">@<?php echo htmlspecialchars($f['username']); ?></p>
                                    <a href="profile.php?user_id=<?php echo $f['user_id']; ?>" 
                                       class="btn btn-sm btn-outline-primary w-100">View Profile</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No followers yet.</p>
                <?php endif; ?>
            </div>

            <!-- Following Tab -->
            <div class="tab-pane fade" id="following">
                <?php if ($following_count > 0): ?>
                    <div class="row">
                        <?php $following->data_seek(0); while ($f = $following->fetch_assoc()): ?>
                            <div class="col-md-4 mb-3">
                                <div class="book-card">
                                    <h6><?php echo htmlspecialchars($f['first_name'] . ' ' . $f['last_name']); ?></h6>
                                    <p class="text-muted small mb-2">@<?php echo htmlspecialchars($f['username']); ?></p>
                                    <a href="profile.php?user_id=<?php echo $f['user_id']; ?>" 
                                       class="btn btn-sm btn-outline-primary w-100">View Profile</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Not following anyone yet.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <?php if ($is_own_profile): ?>
        <!-- Create Shelf Modal -->
        <div class="modal fade" id="createShelfModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Shelf</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="../api/shelf-handler.php" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Shelf Name</label>
                                <input type="text" name="shelf_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description (optional)</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_public" value="1" checked>
                                <label class="form-check-label">Public Shelf</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Shelf</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/interactions.js"></script>
</body>

</html>