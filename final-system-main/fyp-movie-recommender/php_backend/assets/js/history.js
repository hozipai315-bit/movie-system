document.addEventListener('DOMContentLoaded', function() {
    const filterItems = document.querySelectorAll('.dropdown-item-tech');
    const historyItems = document.querySelectorAll('.history-item');
    
    // --- Client-Side Filtering ---
    filterItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the filter value (e.g., 'face', 'text', 'all')
            const filterValue = this.getAttribute('data-filter');

            // Update active state in dropdown
            filterItems.forEach(link => link.classList.remove('active'));
            this.classList.add('active');

            // Update button text for feedback
            const filterBtn = document.querySelector('.btn-filter');
            if (filterBtn) {
                const label = filterValue === 'all' ? 'METHOD FILTER' : this.textContent.trim().toUpperCase();
                filterBtn.innerHTML = `<i class="bi bi-funnel me-1"></i> ${label}`;
            }

            // Apply filter to history cards
            historyItems.forEach(card => {
                const cardType = card.getAttribute('data-type');
                
                if (filterValue === 'all' || cardType === filterValue) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    
    // Initial staggered animation
    historyItems.forEach((card, index) => {
        card.style.animationDelay = `${0.05 * index}s`;
    });
});
