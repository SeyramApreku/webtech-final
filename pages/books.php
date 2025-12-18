<?php
session_start();

require_once '../config/database.php';

$conn = getDBConnection();

// Get filter options
$genres = $conn->query("SELECT DISTINCT genre FROM books ORDER BY genre");
$years = $conn->query("SELECT DISTINCT publication_year FROM books ORDER BY publication_year DESC");
$regions = $conn->query("SELECT DISTINCT region FROM books WHERE region IS NOT NULL ORDER BY region");

// Get user shelves for JS
$userShelves = [];
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $s_query = $conn->query("SELECT shelf_id, shelf_name FROM shelves WHERE user_id = $uid ORDER BY shelf_name");
    while ($s = $s_query->fetch_assoc()) {
        $userShelves[] = $s;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books - GriotShelf</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
        <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 2rem;">Browse Books</h1>

        <!-- Search & Filter Section -->
        <div class="row mb-4">
            <div class="col-md-4 mb-2">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by title or author...">
            </div>
            <div class="col-md-2 mb-2">
                <select id="genreFilter" class="form-control">
                    <option value="">All Genres</option>
                    <?php while ($g = $genres->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($g['genre']); ?>">
                            <?php echo htmlspecialchars($g['genre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select id="regionFilter" class="form-control">
                    <option value="">All Regions</option>
                    <?php while ($r = $regions->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($r['region']); ?>">
                            <?php echo htmlspecialchars($r['region']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select id="yearFilter" class="form-control">
                    <option value="">All Years</option>
                    <?php while ($y = $years->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($y['publication_year']); ?>">
                            <?php echo htmlspecialchars($y['publication_year']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button id="clearFilters" class="btn btn-outline-dark w-100">Clear</button>
            </div>
        </div>

        <!-- Books Grid -->
        <div class="row" id="booksContainer">
            <!-- Books will be loaded here via JS -->
            <div class="text-center w-100 mt-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script>
        const searchInput = document.getElementById('searchInput');
        const genreFilter = document.getElementById('genreFilter');
        const regionFilter = document.getElementById('regionFilter');
        const yearFilter = document.getElementById('yearFilter');
        const clearBtn = document.getElementById('clearFilters');
        const container = document.getElementById('booksContainer');
        const userShelves = <?php echo json_encode($userShelves); ?>;
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        function fetchBooks() {
            const query = searchInput.value;
            const genre = genreFilter.value;
            const region = regionFilter.value;
            const year = yearFilter.value;

            // Show loading
            container.innerHTML = '<div class="text-center w-100 mt-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

            const url = `../api/search-books.php?query=${encodeURIComponent(query)}&genre=${encodeURIComponent(genre)}&year=${encodeURIComponent(year)}&region=${encodeURIComponent(region)}`;

            fetch(url)
                .then(response => response.json())
                .then(books => {
                    container.innerHTML = '';

                    if (books.length === 0) {
                        container.innerHTML = '<div class="col-12 text-center mt-5"><p class="text-muted">No books found matching your criteria.</p></div>';
                        return;
                    }

                    books.forEach(book => {
                        const col = document.createElement('div');
                        col.className = 'col-md-6 col-lg-4 mb-4';

                        let imgHtml = '';
                        if (book.cover_url) {
                            imgHtml = `<img src="${book.cover_url}" alt="${book.title}" style="width: 100%; height: 350px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem;">`;
                        } else {
                            imgHtml = `<div style="width: 100%; height: 350px; background: linear-gradient(135deg, var(--terracotta), var(--muted-gold)); border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center;"><p style="color: white; font-family: 'Playfair Display', serif; font-size: 1.5rem; text-align: center; padding: 1rem;">${book.title}</p></div>`;
                        }

                        // Determine region badge color if needed, or just text
                        const regionBadge = book.region ? `<span class="badge bg-secondary mb-2" style="background-color: var(--soft-sand) !important; color: var(--charcoal); font-weight: normal; border: 1px solid var(--muted-gold);">${book.region}</span>` : '';

                        let actionsHtml = '';
                        if (isLoggedIn) {
                            // Reading List Dropdown - highlight current status
                            const currentStatus = book.reading_status || '';

                            let statusOptions = `
                                <li>
                                    <button class="dropdown-item btn-reading-list d-flex justify-content-between align-items-center ${currentStatus === 'want_to_read' ? 'active' : ''}" 
                                            data-book-id="${book.book_id}" data-status="want_to_read">
                                        Want to Read ${currentStatus === 'want_to_read' ? '<span class="ms-2">✓</span>' : ''}
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item btn-reading-list d-flex justify-content-between align-items-center ${currentStatus === 'currently_reading' ? 'active' : ''}" 
                                            data-book-id="${book.book_id}" data-status="currently_reading">
                                        Currently Reading ${currentStatus === 'currently_reading' ? '<span class="ms-2">✓</span>' : ''}
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item btn-reading-list d-flex justify-content-between align-items-center ${currentStatus === 'finished' ? 'active' : ''}" 
                                            data-book-id="${book.book_id}" data-status="finished">
                                        Finished ${currentStatus === 'finished' ? '<span class="ms-2">✓</span>' : ''}
                                    </button>
                                </li>`;

                            // Shelves Dropdown
                            let shelfOptions = '';
                            userShelves.forEach(shelf => {
                                // Check if book is in this shelf
                                const shelfId = parseInt(shelf.shelf_id);
                                const isInShelf = book.shelves && book.shelves.includes(shelfId);
                                shelfOptions += `
                                <li>
                                    <form class="add-to-shelf-form px-2 py-1">
                                        <input type="hidden" name="book_id" value="${book.book_id}">
                                        <input type="hidden" name="shelf_id" value="${shelf.shelf_id}">
                                        <button type="submit" class="dropdown-item rounded d-flex justify-content-between align-items-center">
                                            ${shelf.shelf_name}
                                            ${isInShelf ? '<span class="text-success ms-2">✓</span>' : ''}
                                        </button>
                                    </form>
                                </li>`;
                            });
                            shelfOptions += `<li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="profile.php#shelves">Create New Shelf</a></li>`;

                            actionsHtml = `
                                <div class="d-flex gap-2 mt-auto mb-2">
                                    <div class="dropdown w-50">
                                        <button class="btn btn-outline-dark btn-sm w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu shadow">${statusOptions}</ul>
                                    </div>
                                    <div class="dropdown w-50">
                                        <button class="btn btn-outline-dark btn-sm w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Shelf
                                        </button>
                                        <ul class="dropdown-menu shadow w-100">${shelfOptions}</ul>
                                    </div>
                                </div>`;
                        }

                        // Rating HTML
                        const avgRating = parseFloat(book.avg_rating) || 0;
                        const reviewCount = parseInt(book.review_count) || 0;
                        let starsHtml = '<div class="rating-display mb-2">';
                        for(let i = 1; i <= 5; i++) {
                            starsHtml += `<span style="color: ${i <= Math.round(avgRating) ? 'var(--terracotta)' : '#ccc'};">★</span>`;
                        }
                        starsHtml += ` <small class="text-muted">(${avgRating.toFixed(1)})</small></div>`;

                        col.innerHTML = `
                            <div class="book-card h-100 d-flex flex-column">
                                <a href="book-detail.php?id=${book.book_id}" style="text-decoration: none;">
                                    ${imgHtml}
                                </a>
                                <h5>${book.title}</h5>
                                <p class="book-author">by ${book.author}</p>
                                ${starsHtml}
                                ${regionBadge}
                                <p style="font-size: 0.9rem; margin-bottom: 0.5rem;">
                                    ${book.description ? book.description.substring(0, 100) + '...' : ''}
                                </p>
                                <p style="font-size: 0.85rem; color: var(--muted-gold); margin-bottom: 1rem;">
                                    ${book.publication_year} • ${book.genre}
                                </p>
                                ${actionsHtml}
                                <a href="book-detail.php?id=${book.book_id}" class="btn btn-primary btn-sm w-100">View Details</a>
                            </div>
                        `;
                        container.appendChild(col);
                    });
                })
                .catch(err => {
                    console.error('Error fetching books:', err);
                    container.innerHTML = '<div class="col-12 text-center mt-5"><p class="text-danger">Failed to load books. Please try again.</p></div>';
                });
        }

        // Event Listeners
        searchInput.addEventListener('input', debounce(fetchBooks, 300));
        genreFilter.addEventListener('change', fetchBooks);
        regionFilter.addEventListener('change', fetchBooks);
        yearFilter.addEventListener('change', fetchBooks);

        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            genreFilter.value = '';
            regionFilter.value = '';
            yearFilter.value = '';
            fetchBooks();
        });

        // Debounce helper
        function debounce(func, wait) {
            let timeout;
            return function () {
                const context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        // Initial fetch
        fetchBooks();
    </script>
    <script src="../js/interactions.js"></script>
</body>

</html>
<?php $conn->close(); ?>