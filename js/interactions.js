// GriotShelf Interactions
// This script handles dynamic parts of the website using Event Delegation
// so it works for both static and dynamically loaded content without reloads.

document.addEventListener('click', function (e) {
    // --- 1. Handle Reading Status Updates (Want to Read, etc.) ---
    const readingBtn = e.target.closest('.btn-reading-list');
    if (readingBtn) {
        e.preventDefault();

        const status = readingBtn.dataset.status;
        const bookId = readingBtn.dataset.bookId;
        const dropdown = readingBtn.closest('.dropdown');
        const dropdownToggle = dropdown.querySelector('.dropdown-toggle');

        const formData = new FormData();
        formData.append('book_id', bookId);
        formData.append('status', status);
        formData.append('ajax', '1');

        // Show loading state on the toggle button
        const originalToggleHtml = dropdownToggle.innerHTML;
        dropdownToggle.innerHTML = '...';

        fetch('../api/reading-list-handler.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI in-place:
                    // 1. Remove 'active' class and checkmarks from all sibling items
                    const items = dropdown.querySelectorAll('.btn-reading-list');
                    items.forEach(item => {
                        item.classList.remove('active');
                        const tick = item.querySelector('span');
                        if (tick) tick.remove();
                    });

                    if (data.status === 'removed') {
                        dropdownToggle.innerHTML = 'Select Status';
                    } else {
                        // 2. Mark this one as active and add checkmark
                        readingBtn.classList.add('active');
                        if (!readingBtn.querySelector('span')) {
                            readingBtn.insertAdjacentHTML('beforeend', '<span class="ms-2">✓</span>');
                        }
                        // 3. Update toggle button text
                        const newLabel = readingBtn.innerText.replace('✓', '').trim();
                        dropdownToggle.innerHTML = newLabel;
                    }
                } else {
                    alert('Failed to update reading status.');
                    dropdownToggle.innerHTML = originalToggleHtml;
                }
            })
            .catch(err => {
                console.error("Error:", err);
                dropdownToggle.innerHTML = originalToggleHtml;
                alert('An error occurred.');
            });
        return;
    }

    // --- 2. Handle Reading List Removals (Forms on Profile) ---
    const readingForm = e.target.closest('.reading-list-form');
    if (e.target.closest('button') && readingForm) {
        e.preventDefault();

        const btn = e.target.closest('button');
        const formData = new FormData(readingForm);
        formData.append('ajax', '1');

        // Show loading state
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '...';
        btn.disabled = true;

        fetch('../api/reading-list-handler.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the book card from the UI
                    const card = readingForm.closest('.col-md-3');
                    if (card) {
                        card.style.opacity = '0';
                        setTimeout(() => card.remove(), 300);
                    }
                } else {
                    alert('Failed to remove from list.');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error("Error:", err);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        return;
    }

    // --- 3. Handle Adding/Removing Books from Shelves (Forms) ---
    const shelfForm = e.target.closest('.add-to-shelf-form');
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
                    // Toggle checkmark in-place
                    // We recreate the button content to ensure the tick is handled correctly
                    const cleanName = btn.innerText.replace('✓', '').replace('...', '').trim();
                    btn.innerHTML = cleanName + (data.action === 'added' ? '<span class="text-success ms-2">✓</span>' : '');
                    btn.disabled = false;
                } else {
                    alert(data.message || 'Failed to update shelf.');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error("Error:", err);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                alert('An error occurred.');
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

    // Profile Reading List Privacy Toggles
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
