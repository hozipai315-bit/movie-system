document.addEventListener('DOMContentLoaded', function() {
    const movieGrid = document.getElementById('movieGrid');
    const toastContainer = document.getElementById('toastContainer');

    // Simple mock set to track favorited movies locally (to prevent duplicates)
    // In production, this would be fetched from the database on page load.
    let localFavorites = new Set();

    // --- 0. Live Filtering Logic ---
    const liveSearch = document.getElementById('liveSearch');
    const moodFilters = document.getElementById('moodFilters');
    const genreFilters = document.getElementById('genreFilters');
    const liveSort = document.getElementById('liveSort');
    const resetFilters = document.getElementById('resetFilters');
    const noMoviesFound = document.getElementById('noMoviesFound');
    const movieCards = Array.from(document.querySelectorAll('.movie-card-col'));

    let activeMood = 'all';
    let activeGenres = new Set(['all']);

    function filterMovies() {
        const searchTerm = liveSearch.value.toLowerCase();
        let visibleCount = 0;

        movieCards.forEach(card => {
            const title = card.getAttribute('data-title') || '';
            const mood = card.getAttribute('data-mood') || '';
            const genres = (card.getAttribute('data-genres') || '').split(',');

            const matchesSearch = title.includes(searchTerm);
            const matchesMood = (activeMood === 'all' || mood === activeMood);

            let matchesGenre = false;
            if (activeGenres.has('all')) {
                matchesGenre = true;
            } else {
                // OR Logic for multi-select genres
                matchesGenre = genres.some(g => activeGenres.has(g));
            }

            if (matchesSearch && matchesMood && matchesGenre) {
                card.style.display = '';
                setTimeout(() => card.classList.remove('opacity-0'), 10);
                visibleCount++;
            } else {
                card.classList.add('opacity-0');
                setTimeout(() => {
                    if (card.classList.contains('opacity-0')) {
                        card.style.display = 'none';
                    }
                }, 400);
            }
        });

        // Toggle No Movies Found message
        if (visibleCount === 0) {
            noMoviesFound.classList.remove('d-none');
            movieGrid.classList.add('d-none');
        } else {
            noMoviesFound.classList.add('d-none');
            movieGrid.classList.remove('d-none');
        }
    }

    function sortMovies() {
        const sortBy = liveSort.value;
        const sortedCards = [...movieCards];

        sortedCards.sort((a, b) => {
            if (sortBy === 'rating') {
                return parseFloat(b.getAttribute('data-rating')) - parseFloat(a.getAttribute('data-rating'));
            } else if (sortBy === 'newest') {
                return new Date(b.getAttribute('data-release-date')) - new Date(a.getAttribute('data-release-date'));
            } else if (sortBy === 'title') {
                return a.getAttribute('data-title').localeCompare(b.getAttribute('data-title'));
            } else {
                // Best Match (Original Order)
                return parseInt(movieCards.indexOf(a)) - parseInt(movieCards.indexOf(b));
            }
        });

        // Re-append sorted cards
        sortedCards.forEach(card => movieGrid.appendChild(card));
    }

    // Event Listeners for Filters
    if (liveSearch) {
        liveSearch.addEventListener('input', filterMovies);
    }

    if (moodFilters) {
        moodFilters.addEventListener('click', (e) => {
            if (e.target.classList.contains('pill-filter')) {
                moodFilters.querySelectorAll('.pill-filter').forEach(btn => btn.classList.remove('active'));
                e.target.classList.add('active');
                activeMood = e.target.getAttribute('data-mood');
                filterMovies();
            }
        });
    }

    if (genreFilters) {
        genreFilters.addEventListener('click', (e) => {
            if (e.target.classList.contains('pill-filter')) {
                const genre = e.target.getAttribute('data-genre');

                if (genre === 'all') {
                    activeGenres.clear();
                    activeGenres.add('all');
                    genreFilters.querySelectorAll('.pill-filter').forEach(btn => btn.classList.remove('active'));
                    e.target.classList.add('active');
                } else {
                    activeGenres.delete('all');
                    genreFilters.querySelector('[data-genre="all"]').classList.remove('active');

                    if (activeGenres.has(genre)) {
                        activeGenres.delete(genre);
                        e.target.classList.remove('active');
                    } else {
                        activeGenres.add(genre);
                        e.target.classList.add('active');
                    }

                    if (activeGenres.size === 0) {
                        activeGenres.add('all');
                        genreFilters.querySelector('[data-genre="all"]').classList.add('active');
                    }
                }
                filterMovies();
            }
        });
    }

    if (liveSort) {
        liveSort.addEventListener('change', sortMovies);
    }

    if (resetFilters) {
        resetFilters.addEventListener('click', () => {
            // Reset Search
            liveSearch.value = '';

            // Reset Mood
            activeMood = 'all';
            moodFilters.querySelectorAll('.pill-filter').forEach(btn => btn.classList.remove('active'));
            moodFilters.querySelector('[data-mood="all"]').classList.add('active');

            // Reset Genre
            activeGenres.clear();
            activeGenres.add('all');
            genreFilters.querySelectorAll('.pill-filter').forEach(btn => btn.classList.remove('active'));
            genreFilters.querySelector('[data-genre="all"]').classList.add('active');

            // Reset Sort
            liveSort.value = 'best';

            filterMovies();
            sortMovies();
        });
    }

    // --- 1. Event Listener for 'Add to Favorites' Buttons ---
    if (movieGrid) {
        movieGrid.addEventListener('click', function(e) {
            const favoriteButton = e.target.closest('.favorite-btn');
            if (favoriteButton) {
                const movieId = favoriteButton.getAttribute('data-movie-id');
                const movieTitle = favoriteButton.getAttribute('data-movie-title');
                const moviePoster = favoriteButton.getAttribute('data-movie-poster');
                const movieVote = favoriteButton.getAttribute('data-movie-vote');
                const movieOverview = favoriteButton.getAttribute('data-movie-overview');

                if (localFavorites.has(movieId)) {
                    showToast('Movie Already Saved', `${movieTitle} is already in your favorites!`, 'warning');
                    return;
                }

                addFavorite(movieId, movieTitle, moviePoster, movieVote, movieOverview, favoriteButton);
            }
        });

        // Initial staggered fade-in animation
        document.querySelectorAll('.movie-card-col').forEach((card, index) => {
            card.style.animationDelay = `${0.05 * index}s`;
            card.style.opacity = '1';
        });

        // Neural Vibe Stats Animation
        document.querySelectorAll('.vibe-stat-item').forEach((stat, index) => {
            stat.style.opacity = '0';
            stat.style.transform = 'translateX(-10px)';
            stat.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                stat.style.opacity = '1';
                stat.style.transform = 'translateX(0)';
            }, 500 + (index * 200));
        });
    }

    // --- 2. Handle Add to Favorites (AJAX/Fetch) ---
    async function addFavorite(movieId, movieTitle, moviePoster, movieVote, movieOverview, button) {
        const originalText = button.innerHTML;

        // Show loading state with High-Tech "Syncing" feel
        button.disabled = true;
        button.classList.add('syncing');
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> SYNCING_NEURAL_DATA...';

        try {
            const formData = new URLSearchParams();
            formData.append('movie_id', movieId);
            formData.append('title', movieTitle);
            formData.append('poster', moviePoster);
            formData.append('vote_average', movieVote);
            formData.append('overview', movieOverview);

            const response = await fetch('api/save_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok && (result.status === 'success' || result.status === 'info')) {
                // SUCCESS: Update UI state
                localFavorites.add(movieId);
                button.classList.add('added');
                button.innerHTML = '<i class="bi bi-heart-fill me-1"></i> SAVED';

                showToast(result.status === 'success' ? 'Movie Saved!' : 'Already in Favorites', result.message, result.status === 'success' ? 'success' : 'info');
            } else {
                throw new Error(result.message || 'Server response failed.');
            }

        } catch (error) {
            console.error('Error adding favorite:', error);

            // FAILURE: Revert button state and show error
            button.innerHTML = originalText;
            button.disabled = false;
            showToast('Error', `Failed to add ${movieTitle} to favorites: ${error.message}`, 'danger');
        }
    }

    // --- 3. Bootstrap Toast Notification Handler ---
    function showToast(title, message, type = 'primary') {
        const toastId = `toast-${Date.now()}`;
        const toastHtml = `
            <div id="${toastId}" class="toast toast-tech align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="toast-header toast-header-tech">
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        const toastEl = document.getElementById(toastId);
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        } else {
             console.warn("Bootstrap JS Toast component not found.");
        }
    }
});