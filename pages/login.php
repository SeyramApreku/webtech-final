<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GriotShelf</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img src="../images/GriotShelf.png" alt="GriotShelf Logo" class="navbar-logo">
                <span class="brand-text">GriotShelf</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="books.php">Browse Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link active" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div style="background-color: white; padding: 2.5rem; border-radius: 8px;">
                    <h2 style="font-family: 'Playfair Display', serif; text-align: center; margin-bottom: 2rem;">
                        Welcome Back
                    </h2>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="../api/login-handler.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email or Username</label>
                            <input type="text" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <span id="eyeIcon">👁️</span>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" style="margin-top: 1rem;">
                            Login
                        </button>
                    </form>

                    <script>
                        const togglePassword = document.querySelector('#togglePassword');
                        const password = document.querySelector('#password');

                        togglePassword.addEventListener('click', function (e) {
                            // toggle the type attribute
                            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                            password.setAttribute('type', type);
                            // toggle the eye icon
                            this.querySelector('#eyeIcon').textContent = type === 'password' ? '👁️' : '🙈';
                        });
                    </script>

                    <p style="text-align: center; margin-top: 1.5rem; margin-bottom: 0;">
                        Don't have an account? <a href="register.php" style="color: var(--terracotta);">Register
                            here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p style="margin-bottom: 0.5rem;">GriotShelf</p>
            <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 0;">&copy; 2025 GriotShelf.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>