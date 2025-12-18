<?php
session_start();
require_once '../config/database.php';

// Access Control
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit();
}

$conn = getDBConnection();

// Fetch Data for Dashboard
// 1. Books
$books = $conn->query("SELECT * FROM books ORDER BY created_at DESC");

// 2. Users (excluding self)
$users = $conn->query("SELECT * FROM users WHERE user_id != {$_SESSION['user_id']} ORDER BY created_at DESC");

// 3. Reviews (Recent)
$reviews = $conn->query("SELECT r.*, b.title as book_title, u.username 
                         FROM reviews r 
                         JOIN books b ON r.book_id = b.book_id 
                         JOIN users u ON r.user_id = u.user_id 
                         ORDER BY r.created_at DESC LIMIT 50");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GriotShelf</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Admin Dashboard</h1>
            <span class="badge bg-danger">Administrator Access</span>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Dashboard Tabs -->
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="books-tab" data-bs-toggle="tab" data-bs-target="#books"
                    type="button">Manage Books</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users"
                    type="button">Manage Users</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews"
                    type="button">Moderation</button>
            </li>
        </ul>

        <div class="tab-content" id="adminTabContent">

            <!-- BOOKS TAB -->
            <div class="tab-pane fade show active" id="books">
                <div class="mb-3 text-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
                        + Add New Book
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Region</th>
                                <th>Year</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($book = $books->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $book['book_id']; ?></td>
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td><?php echo htmlspecialchars($book['region']); ?></td>
                                    <td><?php echo $book['publication_year']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                            data-bs-target="#editBookModal<?php echo $book['book_id']; ?>">
                                            Edit
                                        </button>
                                        <form action="../api/admin-book-handler.php" method="POST" class="d-inline"
                                            onsubmit="return confirm('Delete this book?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Modal (Placeholder - loop generation) -->
                                <div class="modal fade" id="editBookModal<?php echo $book['book_id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Book</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="../api/admin-book-handler.php" method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="book_id"
                                                        value="<?php echo $book['book_id']; ?>">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Title</label>
                                                            <input type="text" name="title" class="form-control"
                                                                value="<?php echo htmlspecialchars($book['title']); ?>"
                                                                required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Author</label>
                                                            <input type="text" name="author" class="form-control"
                                                                value="<?php echo htmlspecialchars($book['author']); ?>"
                                                                required>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Year</label>
                                                            <input type="number" name="publication_year"
                                                                class="form-control"
                                                                value="<?php echo $book['publication_year']; ?>">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Genre</label>
                                                            <input type="text" name="genre" class="form-control"
                                                                value="<?php echo htmlspecialchars($book['genre']); ?>">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Region</label>
                                                            <select name="region" class="form-select">
                                                                <option value="">Select Region...</option>
                                                                <?php
                                                                $regions = ['West Africa', 'East Africa', 'Southern Africa', 'North Africa', 'Diaspora', 'North America'];
                                                                foreach ($regions as $r) {
                                                                    $sel = ($book['region'] === $r) ? 'selected' : '';
                                                                    echo "<option value='$r' $sel>$r</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <label class="form-label">Description</label>
                                                            <textarea name="description" class="form-control"
                                                                rows="3"><?php echo htmlspecialchars($book['description']); ?></textarea>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <label class="form-label">Cover URL</label>
                                                            <input type="url" name="cover_url" class="form-control"
                                                                value="<?php echo htmlspecialchars($book['cover_url']); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add Book Modal -->
                <div class="modal fade" id="addBookModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Book</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="../api/admin-book-handler.php" method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="add">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="title" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Author</label>
                                            <input type="text" name="author" class="form-control" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Year</label>
                                            <input type="number" name="publication_year" class="form-control">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Genre</label>
                                            <input type="text" name="genre" class="form-control">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Region</label>
                                            <select name="region" class="form-select">
                                                <option value="">Select Region...</option>
                                                <option value="West Africa">West Africa</option>
                                                <option value="East Africa">East Africa</option>
                                                <option value="Southern Africa">Southern Africa</option>
                                                <option value="North Africa">North Africa</option>
                                                <option value="Diaspora">Diaspora</option>
                                                <option value="North America">North America</option>
                                            </select>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Cover URL</label>
                                            <input type="url" name="cover_url" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Add Book</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- USERS TAB -->
            <div class="tab-pane fade" id="users">
                <div class="mb-3 text-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        + Add New User
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($u = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $u['user_id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?><br>
                                        <small class="text-muted">@<?php echo htmlspecialchars($u['username']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                                    <td>
                                        <?php if ($u['is_admin']): ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="../api/admin-user-handler.php" method="POST"
                                            onsubmit="return confirm('Permanently delete this user?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add User Modal -->
                <div class="modal fade" id="addUserModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New User</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="../api/admin-user-handler.php" method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="add_user">
                                    <div class="mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select name="is_admin" class="form-select">
                                            <option value="0">Standard User</option>
                                            <option value="1">Administrator</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Create User</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- REVIEWS TAB -->
            <div class="tab-pane fade" id="reviews">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Book</th>
                                <th>Rating</th>
                                <th>Content</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($r = $reviews->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M j', strtotime($r['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($r['username']); ?></td>
                                    <td><?php echo htmlspecialchars($r['book_title']); ?></td>
                                    <td><?php echo $r['rating']; ?>/5</td>
                                    <td>
                                        <?php if ($r['contains_spoilers']): ?>
                                            <span class="badge bg-warning text-dark">Spoiler</span><br>
                                        <?php endif; ?>
                                        <small><?php echo htmlspecialchars(substr($r['review_text'], 0, 100)) . '...'; ?></small>
                                    </td>
                                    <td>
                                        <form action="../api/admin-user-handler.php" method="POST"
                                            onsubmit="return confirm('Delete this review?');">
                                            <input type="hidden" name="action" value="delete_review">
                                            <input type="hidden" name="review_id" value="<?php echo $r['review_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>