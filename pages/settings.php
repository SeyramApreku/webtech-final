<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - GriotShelf</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
        <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 2rem;">Settings</h1>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">

            <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile"
                        type="button">Profile</button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password"
                        type="button">Password</button>
                </li>
            </ul>

            <div class="tab-content" id="settingsTabContent">

                <!-- Profile Settings -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <form action="../api/update-profile.php" method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control"
                                    value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control"
                                    value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control"
                                    value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Profile Changes</button>
                    </form>
                </div>



                <!-- Password Settings -->
                <div class="tab-pane fade" id="password" role="tabpanel">
                    <form action="../api/update-profile.php" method="POST">
                        <input type="hidden" name="action" value="update_password">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-warning">Update Password</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="../js/interactions.js"></script>
</body>

</html>