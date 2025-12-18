<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - GriotShelf</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 2rem;">Get in Touch</h1>

                <div style="background-color: white; padding: 2rem; border-radius: 8px;">
                    <p style="line-height: 1.8; margin-bottom: 2rem;">
                        Have questions or feedback? We'd love to hear from you.
                    </p>

                    <div style="margin-bottom: 1.5rem;">
                        <p style="margin-bottom: 0.5rem; font-weight: 600;">Email</p>
                        <p style="margin-bottom: 0;">seyram.apreku@ashesi.edu.gh</p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <p style="margin-bottom: 0.5rem; font-weight: 600;">Location</p>
                        <p style="margin-bottom: 0;">Berekuso, Ghana</p>
                    </div>
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