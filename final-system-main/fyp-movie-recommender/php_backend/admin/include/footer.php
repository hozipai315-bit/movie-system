<?php
// admin/includes/footer.php
?>
    <footer class="admin-footer-premium">
        <div class="container">
            <div class="row py-5">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="footer-brand mb-3">MoodAI<span>.</span>ADMIN</div>
                    <p class="footer-desc">The main control center for mood mapping and movie data. Helping admins manage users and system settings easily.</p>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-lg-0">
                    <h6 class="footer-title">Navigation</h6>
                    <ul class="footer-links">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="user.php">Users</a></li>
                        <li><a href="logs.php">System Logs</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-lg-0">
                    <h6 class="footer-title">Resources</h6>
                    <ul class="footer-links">
                        <li><a href="movies.php">Movie Engine</a></li>
                        <li><a href="settings.php">Global Settings</a></li>
                        <li><a href="#">API Docs</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h6 class="footer-title">System Health</h6>
                    <div class="footer-health-item">
                        <span class="dot pulse-green"></span> Core Engine: Operational
                    </div>
                    <div class="footer-health-item">
                        <span class="dot pulse-green"></span> Database: Optimized
                    </div>
                    <p class="mt-3 small opacity-75">Last Synchronized: <?php echo date('M d, Y - H:i'); ?></p>
                </div>
            </div>
            <div class="footer-bottom py-4">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0">&copy; <?php echo date('Y'); ?> MoodAI. Admin Control Center.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <p class="mb-0">Version 2.4.0 | Security: High</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Global AOS Init
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>
</body>
</html>
