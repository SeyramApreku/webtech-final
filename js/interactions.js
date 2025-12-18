// GriotShelf Interactions

document.addEventListener('DOMContentLoaded', function () {

    // 1. Reading List Buttons (Add/Update/Remove) - Event Delegation
    // 1. Reading List Buttons (Dropdown Items) - Event Delegation
    document.body.addEventListener('click', function (e) {
        // Target .btn-reading-list inside a dropdown menu usually
        const button = e.target.closest('.btn-reading-list');
        if (!button) return;

        e.preventDefault();
        const bookId = button.dataset.bookId;
        const status = button.dataset.status;
        const action = button.dataset.action || 'add';

        // Visual feedback immediately? Or wait for success?
        // Let's stick to wait for success but show loading state if possible
        const originalHtml = button.innerHTML;
        button.disabled = true;
        // Optional: button.innerHTML = '...'; 

        const formData = new FormData();
        formData.append('book_id', bookId);
        if (status) formData.append('status', status);
        formData.append('action', action);
        formData.append('ajax', '1');

        fetch('../api/reading-list-handler.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (action === 'remove') {
                        location.reload(); // Simplest for now since it changes "Remove" button visibility
                    } else {
                        // Update UI for Status Change
                        // 1. Find the container dropdown
                        const dropdownMenu = button.closest('.dropdown-menu');
                        const dropdown = dropdownMenu ? dropdownMenu.closest('.dropdown') : null;

                        if (dropdown) {
                            // Update Toggle Button Text
                            const toggleBtn = dropdown.querySelector('.dropdown-toggle');
                            // Map status to clean text
                            const statusTextMap = {
                                'want_to_read': 'Want to Read',
                                'currently_reading': 'Currently Reading',
                                'finished': 'Finished'
                            };
                            if (toggleBtn && statusTextMap[status]) {
                                toggleBtn.textContent = statusTextMap[status];
                            }

                            // Update Checkmarks
                            // Remove checkmark from all siblings
                            dropdownMenu.querySelectorAll('.btn-reading-list').forEach(btn => {
                                const check = btn.querySelector('span.ms-2'); // simplistic check
                                if (check && check.textContent.includes('✓')) {
                                    check.remove();
                                }
                                btn.classList.remove('active');
                            });

                            // Add checkmark to clicked button
                            button.classList.add('active');
                            const span = document.createElement('span');
                            span.className = 'ms-2';
                            span.textContent = '✓';
                            button.appendChild(span);
                        }
                    }
                } else {
                    alert('Error updating list.');
                    button.innerHTML = originalHtml;
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred.');
                button.innerHTML = originalHtml;
            })
            .finally(() => {
                button.disabled = false;
            });
    });

    // 2. Add to Shelf Dropdown Items - Event Delegation
    document.body.addEventListener('submit', function (e) {
        if (!e.target.matches('.add-to-shelf-form')) return;

        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        formData.append('ajax', '1');

        const btn = form.querySelector('button');
        const originalHtml = btn.innerHTML; // Save HTML (name + tick)
        // Don't change text to "Adding..." as it messes up the tick if we want to toggle it cleanly, 
        // or we can just disable it.
        btn.style.opacity = '0.7';
        btn.disabled = true;

        fetch('../api/add-to-shelf.php', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("Invalid JSON response:", text);
                        throw new Error("Server error: " + text.substring(0, 100));
                    }
                });
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // If the server says OK, we toggle the checkmark (✓)
                    btn.innerHTML = originalHtml;
                    const newTick = btn.querySelector('.text-success');
                    if (data.action === 'added') {
                        if (newTick) newTick.style.display = 'inline';
                    } else {
                        if (newTick) newTick.style.display = 'none';
                    }
                } else {
                    // Show an error message if the server failed
                    alert(data.message || 'Could not update shelf.');
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(err => {
                // Log the error for the developer and show an alert for the user
                console.error("AJAX Error:", err);
                alert('Something went wrong. Please try again later.');
                btn.innerHTML = originalHtml;
            });
    });
});

// --- Handle Reading List Updates ( AJAX ) ---
document.querySelectorAll('.reading-status-item').forEach(item => {
    item.addEventListener('click', function (e) {
        e.preventDefault();

        const status = this.dataset.status;
        const bookId = this.dataset.bookId;
        const btn = this;
        const parentDropdown = this.closest('.dropdown');
        const dropdownBtn = parentDropdown.querySelector('.dropdown-toggle');
        const originalDropdownText = dropdownBtn.innerHTML;

        // Visual feedback: show we are working on it
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
                    // Success! Refresh the page to show new status and ticks
                    // (This is easier for a student project than complex DOM updates)
                    location.reload();
                } else {
                    alert('Could not update reading status.');
                    dropdownBtn.innerHTML = originalDropdownText;
                }
            })
            .catch(err => {
                console.error("Reading List Error:", err);
                alert('Something went wrong. Please try refreshing!');
                dropdownBtn.innerHTML = originalDropdownText;
            });
    });
});

// 3. Privacy Toggle for Shelves (Profile Page)
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
                    this.checked = !this.checked; // Revert
                    alert('Failed to update privacy.');
                }
            });
    });
});

// 4. Privacy Toggle for Reading Lists (Profile Page)
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
                    this.checked = !this.checked; // Revert
                    alert('Failed to update privacy.');
                }
            });
    });
});
});
