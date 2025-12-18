// GriotShelf Interactions
// This script handles all the dynamic parts of the website like adding books 
// to shelves and updating your reading status without reloading the page.

document.addEventListener('DOMContentLoaded', function () {

    // --- 1. Handle Adding/Removing Books from Shelves (AJAX) ---
    // This finds every shelf item in the dropdowns and listens for a click.
    document.querySelectorAll('.shelf-item').forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();

            // Get the IDs needed for the database update
            const shelfId = this.dataset.shelfId;
            const bookId = this.dataset.bookId;
            const btn = this;
            const originalHtml = btn.innerHTML;

            // Show a simple loading message
            btn.innerHTML = 'Updating...';

            // Send the data to the PHP handler
            const formData = new FormData();
            formData.append('shelf_id', shelfId);
            formData.append('book_id', bookId);

            fetch('../api/add-to-shelf.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Put back the original label
                        btn.innerHTML = originalHtml;
                        // Find the checkmark icon if it exists
                        const tick = btn.querySelector('.text-success');

                        // Show or hide the tick depending on whether the book was added or removed
                        if (data.action === 'added') {
                            if (tick) tick.style.display = 'inline';
                        } else {
                            if (tick) tick.style.display = 'none';
                        }
                    } else {
                        alert('Sorry, we could not update the shelf.');
                        btn.innerHTML = originalHtml;
                    }
                })
                .catch(err => {
                    console.error("Error:", err);
                    alert('An error occurred. Please try again.');
                    btn.innerHTML = originalHtml;
                });
        });
    });

    // --- 2. Handle Reading Status Updates (Want to Read, etc.) ---
    document.querySelectorAll('.reading-status-item').forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();

            const status = this.dataset.status;
            const bookId = this.dataset.bookId;
            const dropdownBtn = this.closest('.dropdown').querySelector('.dropdown-toggle');
            const originalText = dropdownBtn.innerHTML;

            dropdownBtn.innerHTML = 'Updating...';

            const formData = new FormData();
            formData.append('book_id', bookId);
            formData.append('status', status);

            fetch('../api/reading-list-handler.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // We reload the page to refresh all the checkmarks and UI states easily
                        location.reload();
                    } else {
                        alert('Failed to update status.');
                        dropdownBtn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    console.error("Error:", err);
                    alert('An error occurred.');
                    dropdownBtn.innerHTML = originalText;
                });
        });
    });

    // --- 3. Privacy Toggles for Shelves (Profile Page) ---
    document.querySelectorAll('.privacy-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const shelfId = this.dataset.shelfId;
            const isPublic = this.checked ? 1 : 0;

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
                        // If update failed, flip the switch back
                        this.checked = !this.checked;
                        alert('Could not update privacy setting.');
                    }
                });
        });
    });

    // --- 4. Privacy Toggles for Reading Lists (Profile Page) ---
    document.querySelectorAll('.reading-list-privacy-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const listType = this.dataset.listType;
            const isPublic = this.checked ? 1 : 0;

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
                        this.checked = !this.checked;
                        alert('Could not update privacy setting.');
                    }
                });
        });
    });

});
