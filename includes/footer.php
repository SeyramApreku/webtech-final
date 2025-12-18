<footer>
    <div class="container text-center">
        <p style="margin-bottom: 0.5rem;">GriotShelf</p>
        <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 0;">&copy; 2025 GriotShelf.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Spoiler Click-to-Reveal Logic
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('spoiler-blur')) {
            e.target.classList.toggle('revealed');
        }
    });

    // Initialize tooltips/popovers if needed
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>