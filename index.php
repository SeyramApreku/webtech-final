<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GriotShelf - Discovering African Literature, Old & New</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div style="padding: 5rem 0 4rem 0;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1
                        style="font-family: 'Playfair Display', serif; font-size: 3.5rem; color: var(--charcoal); margin-bottom: 1rem;">
                        GriotShelf
                    </h1>
                    <p style="font-size: 1.3rem; color: var(--terracotta); margin-bottom: 2rem;">
                        Discovering African Literature, Old & New
                    </p>
                    <p style="font-size: 1.05rem; color: var(--charcoal); max-width: 700px; margin: 0 auto 2.5rem;">
                        A digital library celebrating African storytelling—from Chinua Achebe's timeless classics
                        to Chimamanda Ngozi Adichie's contemporary voices, and everything in between.
                    </p>

                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="pages/register.php" class="btn btn-primary" style="margin-right: 1rem;">
                            Get Started
                        </a>
                        <a href="pages/books.php" class="btn btn-outline-dark">
                            Browse Books
                        </a>
                    <?php else: ?>
                        <a href="pages/books.php" class="btn btn-primary">
                            Browse Books
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div style="background-color: var(--soft-sand); padding: 3rem 0;">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <h2
                        style="font-family: 'Playfair Display', serif; color: var(--charcoal); margin-bottom: 1.5rem; text-align: center;">
                        What is a Griot?
                    </h2>
                    <p
                        style="font-size: 1.05rem; line-height: 1.8; color: var(--charcoal); text-align: center; max-width: 800px; margin: 0 auto;">
                        In West African tradition, griots are storytellers, historians, and keepers of oral traditions.
                        They preserve and pass down the stories that connect generations. GriotShelf continues this
                        tradition in the digital age, creating a space where African literature—past and present—is
                        preserved, discovered, and celebrated.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div style="padding: 4rem 0;">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h4 style="font-family: 'Playfair Display', serif; color: var(--terracotta); margin-bottom: 1rem;">
                        Discover Books
                    </h4>
                    <p style="color: var(--charcoal); line-height: 1.7;">
                        Browse our curated collection of African literature, from foundational classics
                        to contemporary bestsellers and emerging voices.
                    </p>
                </div>

                <div class="col-md-4 mb-4">
                    <h4 style="font-family: 'Playfair Display', serif; color: var(--terracotta); margin-bottom: 1rem;">
                        Share Reviews
                    </h4>
                    <p style="color: var(--charcoal); line-height: 1.7;">
                        Write reviews, rate books, and see what other readers think. Build a community
                        around the stories that matter.
                    </p>
                </div>

                <div class="col-md-4 mb-4">
                    <h4 style="font-family: 'Playfair Display', serif; color: var(--terracotta); margin-bottom: 1rem;">
                        Track Your Reading
                    </h4>
                    <p style="color: var(--charcoal); line-height: 1.7;">
                        Keep lists of books you want to read, are currently reading, or have finished.
                        Your personal literary journey, all in one place.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p style="margin-bottom: 0.5rem;">GriotShelf</p>
            <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 0;">
                &copy; 2025 GriotShelf. Celebrating African Literature.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>