/**
 * favorites.js - Neural Favorites Interface Logic
 * Handles filtering, removal, and interface state for the Favorites module.
 */

document.addEventListener('DOMContentLoaded', function() {
    const favoritesContainer = document.getElementById('favoritesContainer');
    const filterItems = document.querySelectorAll('.dropdown-item-tech');
    const recordCountDisplay = document.getElementById('recordCount');

    // --- 1. Linguistic Filter Protocols (Filtering Logic) ---
    filterItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const filterValue = this.getAttribute('data-filter');

            // Toggle active state
            filterItems.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Update dropdown button text for visual feedback (Match History Unit)
            const dropdownBtn = document.querySelector('.dropdown-toggle');
            if (dropdownBtn) {
                const label = filterValue === 'all' ? 'MOOD FILTER' : this.textContent.trim().toUpperCase();
                dropdownBtn.innerHTML = `<i class="bi bi-funnel me-1"></i> ${label}`;
            }

            // Filter the movie cards
            const items = document.querySelectorAll('.favorite-item');
            let visibleCount = 0;

            items.forEach(item => {
                const itemMood = item.getAttribute('data-mood');

                if (filterValue === 'all' || itemMood === filterValue) {
                    item.style.display = 'block';
                    // Re-trigger animation
                    item.style.animation = 'none';
                    void item.offsetWidth; // trigger reflow
                    item.style.animation = null; 
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Update record count display for visual consistency
            if (recordCountDisplay) {
                recordCountDisplay.textContent = `${visibleCount} MOVIES`;
            }
        });
    });

    // --- 2. Event Listener for Remove (Terminate) Button ---
    if (favoritesContainer) {
        favoritesContainer.addEventListener('click', function(e) {
            const removeButton = e.target.closest('.remove-btn');
            if (removeButton) {
                const movieId = removeButton.getAttribute('data-movie-id');
                // Use a more technical sounding confirmation or just proceed
                if (confirm(`Are you sure you want to remove this movie?`)) {
                    removeFavorite(movieId, removeButton);
                }
            }
        });
    }

    // --- 3. Handle Remove Favorite (AJAX/Fetch) ---
    async function removeFavorite(movieId, button) {
        const originalContent = button.innerHTML;
        const favoriteItem = button.closest('.favorite-item');

        // Show technical loading state
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> REMOVING...';

        try {
            // Real application would point to 'api/remove_favorite.php'
            const response = await fetch('api/remove_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&movie_id=${encodeURIComponent(movieId)}`
            });

            if (response.ok) {
                // SUCCESS: Visual feedback
                favoriteItem.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                favoriteItem.style.opacity = '0';
                favoriteItem.style.transform = 'scale(0.8) translateY(20px)';
                favoriteItem.style.filter = 'grayscale(1) brightness(2)';

                setTimeout(() => {
                    favoriteItem.remove();
                    updateTotalCount();
                    checkEmptyState();
                }, 500);
            } else {
                alert("REMOVAL FAILED: Connection error.");
                button.innerHTML = originalContent;
                button.disabled = false;
            }
        } catch (error) {
            console.error('Error removing favorite:', error);
            alert("Connection Error: Could not reach the server.");
            button.innerHTML = originalContent;
            button.disabled = false;
        }
    }

    // --- 4. Interface State Management ---
    function updateTotalCount() {
        const totalCount = document.querySelectorAll('.favorite-item').length;
        if (recordCountDisplay) {
            recordCountDisplay.textContent = `${totalCount} MOVIES`;
        }
    }

    function checkEmptyState() {
        const remainingItems = document.querySelectorAll('.favorite-item').length;
        
        if (remainingItems === 0 && favoritesContainer) {
            favoritesContainer.innerHTML = `
                    <div class="empty-archive animate-reveal">
                        <i class="bi bi-film text-danger mb-3 d-block" style="font-size: 3rem;"></i>
                        <h4 class="fw-bold text-white">No Favorites Yet</h4>
                        <p class="text-white small mb-4">You haven't saved any movies to your favorites list yet.</p>
                        <a href="dashboard.php" class="btn btn-initiate mt-2">Find Movies</a>
                    </div>
                </div>
            `;
        }
    }
    
    // Initial reveal staggered animation
    document.querySelectorAll('.favorite-item').forEach((item, index) => {
        item.style.animationDelay = `${0.05 * index}s`;
    });
});
