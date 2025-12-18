<?php
// Determine path prefix based on location
$script_name = $_SERVER['SCRIPT_NAME'];
if (strpos($script_name, '/pages/') !== false) {
    $base = '../';
    $pages = '';
} else {
    $base = '';
    $pages = 'pages/';
}
?>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $base; ?>index.php">
            <img src="<?php echo $base; ?>images/GriotShelf.png" alt="GriotShelf Logo" class="navbar-logo">
            <span class="brand-text">GriotShelf</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <!-- ADMIN VIEW: Reduced to essential tools -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $pages; ?>admin.php"
                            style="color: var(--terracotta); font-weight: 600;">
                            Admin Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base; ?>api/logout.php">Logout</a>
                    </li>

                <?php else: ?>
                    <!-- REGULAR USER / GUEST VIEW -->
                    <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>index.php">Home</a></li>

                    <!-- Discover Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Discover
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $pages; ?>books.php">Browse Books</a></li>
                            <li><a class="dropdown-item" href="<?php echo $pages; ?>users.php">Find Readers</a></li>
                        </ul>
                    </li>

                    <li class="nav-item"><a class="nav-link" href="<?php echo $pages; ?>about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $pages; ?>contact.php">Contact</a></li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <?php
                                if (!empty($_SESSION['username'])) {
                                    echo htmlspecialchars($_SESSION['username']);
                                } else {
                                    echo htmlspecialchars($_SESSION['first_name'] ?? 'Account');
                                }
                                ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo $pages; ?>profile.php">My Profile</a></li>
                                <li><a class="dropdown-item" href="<?php echo $pages; ?>settings.php">Settings</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?php echo $base; ?>api/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $pages; ?>login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link btn btn-primary text-white ms-2 px-3"
                                href="<?php echo $pages; ?>register.php">Register</a></li>
                    <?php endif; ?>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>