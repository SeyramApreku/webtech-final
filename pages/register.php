<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GriotShelf</title>
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
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link active" href="register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div style="background-color: white; padding: 2.5rem; border-radius: 8px;">
                    <h2 style="font-family: 'Playfair Display', serif; text-align: center; margin-bottom: 2rem;">
                        Create Your Account
                    </h2>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="../api/register-handler.php" method="POST" id="registerForm">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>



                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <span id="eyeIcon">👁️</span>
                                </button>
                            </div>
                            <div id="passwordHelp" style="margin-top: 0.5rem; font-size: 0.9rem;">
                                <p style="margin: 0; color: var(--charcoal); opacity: 0.8;">Password must contain:</p>
                                <ul
                                    style="margin: 0.5rem 0; padding-left: 1.5rem; color: var(--charcoal); opacity: 0.7;">
                                    <li id="length">At least 8 characters</li>
                                    <li id="uppercase">One uppercase letter</li>
                                    <li id="lowercase">One lowercase letter</li>
                                    <li id="number">One number</li>
                                    <li id="symbol">One symbol/punctuation</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" id="confirm_password"
                                    class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <span id="eyeIconConfirm">👁️</span>
                                </button>
                            </div>
                            <small id="matchHelp" style="color: var(--charcoal); opacity: 0.7;"></small>
                        </div>

                        <script>
                            // Toggle Password Visibility
                            document.getElementById('togglePassword').addEventListener('click', function (e) {
                                const password = document.getElementById('password');
                                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                                password.setAttribute('type', type);
                                this.querySelector('#eyeIcon').textContent = type === 'password' ? '👁️' : '🙈';
                            });

                            document.getElementById('toggleConfirmPassword').addEventListener('click', function (e) {
                                const confirmPassword = document.getElementById('confirm_password');
                                const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                                confirmPassword.setAttribute('type', type);
                                this.querySelector('#eyeIconConfirm').textContent = type === 'password' ? '👁️' : '🙈';
                            });
                        </script>

                        <button type="submit" class="btn btn-primary w-100" style="margin-top: 1rem;">
                            Register
                        </button>
                    </form>

                    <p style="text-align: center; margin-top: 1.5rem; margin-bottom: 0;">
                        Already have an account? <a href="login.php" style="color: var(--terracotta);">Login here</a>
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
    <script>
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const form = document.getElementById('registerForm');

        // Password validation
        password.addEventListener('input', function () {
            const value = this.value;

            // Check length
            if (value.length >= 8) {
                document.getElementById('length').style.color = 'green';
            } else {
                document.getElementById('length').style.color = '';
            }

            // Check uppercase
            if (/[A-Z]/.test(value)) {
                document.getElementById('uppercase').style.color = 'green';
            } else {
                document.getElementById('uppercase').style.color = '';
            }

            // Check lowercase
            if (/[a-z]/.test(value)) {
                document.getElementById('lowercase').style.color = 'green';
            } else {
                document.getElementById('lowercase').style.color = '';
            }

            // Check number
            if (/[0-9]/.test(value)) {
                document.getElementById('number').style.color = 'green';
            } else {
                document.getElementById('number').style.color = '';
            }

            // Check symbol
            if (/[^A-Za-z0-9]/.test(value)) {
                document.getElementById('symbol').style.color = 'green';
            } else {
                document.getElementById('symbol').style.color = '';
            }
        });

        // Confirm password match
        confirmPassword.addEventListener('input', function () {
            const matchHelp = document.getElementById('matchHelp');
            if (this.value === password.value && this.value !== '') {
                matchHelp.textContent = '✓ Passwords match';
                matchHelp.style.color = 'green';
            } else if (this.value !== '') {
                matchHelp.textContent = '✗ Passwords do not match';
                matchHelp.style.color = 'red';
            } else {
                matchHelp.textContent = '';
            }
        });

        // Form submission validation
        form.addEventListener('submit', function (e) {
            const value = password.value;

            if (value.length < 8 || !/[A-Z]/.test(value) || !/[a-z]/.test(value) || !/[0-9]/.test(value) || !/[^A-Za-z0-9]/.test(value)) {
                e.preventDefault();
                alert('Please ensure your password meets all requirements');
                return false;
            }

            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
        });
    </script>
</body>

</html>