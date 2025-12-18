<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - GriotShelf</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>


    <div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 2rem;">About GriotShelf</h1>

                <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 2rem;">
                    GriotShelf is a digital library dedicated to celebrating African literature, from timeless
                    classics to contemporary voices shaping the literary landscape today.
                </p>

                <h3
                    style="font-family: 'Playfair Display', serif; color: var(--terracotta); margin-top: 2.5rem; margin-bottom: 1rem;">
                    Our Mission
                </h3>
                <p style="line-height: 1.8;">
                    We believe African stories deserve a dedicated space where they can be discovered, discussed,
                    and celebrated. GriotShelf brings together readers who appreciate the rich diversity of
                    African storytelling—from Nigeria to Ghana, from South Africa to the diaspora.
                </p>

                <h3
                    style="font-family: 'Playfair Display', serif; color: var(--terracotta); margin-top: 2.5rem; margin-bottom: 1rem;">
                    Why "Griot"?
                </h3>
                <p style="line-height: 1.8;">
                    In West African tradition, griots are storytellers and historians who preserve oral traditions
                    and pass down knowledge through generations. GriotShelf honors this legacy by creating a
                    modern platform where African literary traditions continue to thrive.
                </p>

                <h3
                    style="font-family: 'Playfair Display', serif; color: var(--terracotta); margin-top: 2.5rem; margin-bottom: 1rem;">
                    What We Offer
                </h3>
                <p style="line-height: 1.8;">
                    Browse our curated collection, write reviews, track your reading journey, and connect with
                    fellow readers who share your passion for African literature.
                </p>
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