// GriotShelf Interactions
// This script handles dynamic parts of the website using Event Delegation
// so it works for both static and dynamically loaded content.

document.addEventListener('click', function (e) {
    // --- 1. Handle Reading Status Updates (Want to Read, etc.) ---
    const readingBtn = e.target.closest('.btn-reading-list');
    if (readingBtn) {
        e.preventDefault();

        const status = readingBtn.dataset.status;
        const bookId = readingBtn.dataset.bookId;
        const action = readingBtn.dataset.action || 'add';

        const formData = new FormData();
        formData.append('book_id', bookId);
        formData.append('status', status);
        formData.append('action', action);
        formData.append('ajax', '1');

        fetch('../api/reading-list-handler.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload to refresh all UI states and checkmarks
                    location.reload();
                } else {
                    alert('Failed to update reading status.');
                }
            })
            .catch(err => {
                console.error("Error:", err);
                alert('An error occurred updating the reading list.');
            });
        return;
    }

    // --- 2. Handle Adding/Removing Books from Shelves (Forms) ---
    // If we click a button inside an .add-to-shelf-form
    const shelfForm = e.target.closest('.add-to-shelf-form');
    // We listen for the button click specifically to avoid double-triggering
    if (e.target.closest('button') && shelfForm) {
        e.preventDefault();

        const btn = e.target.closest('button');
        const formData = new FormData(shelfForm);

        // Show loading state
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '...';
        btn.disabled = true;

        fetch('../api/add-to-shelf.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload to show the new tick or update the list
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update shelf.');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error("Error:", err);
                alert('An error occurred updating the shelf.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        return;
    }
});

// --- 3. Privacy Toggles (These are checkboxes, so we use 'change' event delegation) ---
document.addEventListener('change', function (e) {
    // Shelf Privacy Toggle
    if (e.target.classList.contains('privacy-toggle')) {
        const shelfId = e.target.dataset.shelfId;
        const isPublic = e.target.checked ? 1 : 0;

        const formData = new FormData();
        formData.append('action', 'update_privacy');
        formData.append('shelf_id', shelfId);
        formData.append('is_public', isPublic);
        formData.append('ajax', '1');

        fetch('../api/shelf-handler.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    e.target.checked = !e.target.checked;
                    alert('Could not update shelf privacy.');
                }
            });
    }

    // Profile Privacy Toggles
    if (e.target.classList.contains('reading-list-privacy-toggle')) {
        const listType = e.target.dataset.listType;
        const isPublic = e.target.checked ? 1 : 0;

        const formData = new FormData();
        formData.append('action', 'update_reading_list_privacy');
        formData.append('list_type', listType);
        formData.append('is_public', isPublic);

        fetch('../api/update-profile.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    e.target.checked = !e.target.checked;
                    alert('Could not update profile privacy.');
                }
            });
    }
});
