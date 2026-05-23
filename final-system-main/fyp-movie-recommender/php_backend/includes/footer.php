<?php
// PHP file for site footer, included at the end of every page.
?>
    <footer class="footer mt-auto py-5 text-center">
        <div class="container">
            <h5 class="fw-bold mb-3" style="color: var(--text-white); letter-spacing: -0.5px;">Mood<span style="color: var(--accent-red);">AI</span>.</h5>
            <p class="small mb-4 animate-reveal" style="color: var(--text-white); opacity: 0.8;">Redefining movie discovery through the lens of high-fidelity neural intelligence and human-centric emotional mapping.</p>
            <hr class="my-4 border-dark" style="opacity: 0.1;">
            <div class="small animate-reveal" style="color: var(--text-white); opacity: 0.6;">
                &copy; <?php echo date("Y"); ?> MoodAI. Engineered for high-performance cinematic resolution and emotional synchronization.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Global Animation Init
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                once: true,
                easing: 'ease-out-cubic'
            });
        }

        // Navbar Scroll Effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    </script>
    </body>
</html>
