<?php
session_start();
require_once '../config/database.php';

$conn = getDBConnection();
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Base query: Select users, count reviews, count books
$users_query = "SELECT u.user_id, u.username, u.first_name, u.last_name, u.email,
                (SELECT COUNT(*) FROM reviews WHERE user_id = u.user_id) as review_count,
                (SELECT COUNT(*) FROM reading_list WHERE user_id = u.user_id) as books_count";

if (isset($_SESSION['user_id'])) {
    $users_query .= ", (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = u.user_id) as is_following";
}

$users_query .= " FROM users u WHERE u.is_admin = 0"; // Hide admins

if (isset($_SESSION['user_id'])) {
    $users_query .= " AND u.user_id != ?"; // Hide self
}

if ($search) {
    $users_query .= " AND u.username LIKE ?";
}

$users_query .= " ORDER BY u.username";

$stmt = $conn->prepare($users_query);

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    if ($search) {
        $param = "%$search%";
        $stmt->bind_param("iis", $uid, $uid, $param);
    } else {
        $stmt->bind_param("ii", $uid, $uid);
    }
} else {
    // Guest view
    if ($search) {
        $param = "%$search%";
        $stmt->bind_param("s", $param);
    }
    // No bind needed if no search and no session (but prepare w/o params is fine)
}

$stmt->execute();
$users = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Readers - GriotShelf</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 style="font-family: 'Playfair Display', serif;">Find Readers</h1>
            <form action="" method="GET" class="d-flex" style="max-width: 500px;">
                <input type="text" name="q" class="form-control me-2" placeholder="Search by username..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-outline-dark">Search</button>
            </form>
        </div>

        <div class="row">
            <?php if ($users->num_rows > 0): ?>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="book-card p-3">
                            <h5 style="color: var(--terracotta); margin-bottom: 0.2rem;">
                                <a href="profile.php?user_id=<?php echo $user['user_id']; ?>"
                                    style="text-decoration: none; color: inherit;">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </a>
                            </h5>
                            <!-- Optionally show real name small below -->
                            <!-- <small class="text-muted"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></small> -->

                            <p style="font-size: 0.9rem; margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                <?php echo $user['review_count']; ?> reviews • <?php echo $user['books_count']; ?> books
                            </p>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="../api/follow-handler.php" method="POST" style="margin-top: 1rem;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <?php if ($user['is_following'] > 0): ?>
                                        <input type="hidden" name="action" value="unfollow">
                                        <button type="submit" class="btn btn-outline-dark btn-sm w-100">Unfollow</button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="follow">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">Follow</button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No readers found matching "<?php echo htmlspecialchars($search); ?>".</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
<?php $conn->close(); ?>