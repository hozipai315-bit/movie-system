<?php
// admin/settings.php
require_once 'include/header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $site_name = $_POST['site_name'] ?? 'MoodAI';
    $api_key = $_POST['tmdb_api_key'] ?? '';

    try {
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value)
                               VALUES ('site_name', :site_name)
                               ON DUPLICATE KEY UPDATE setting_value = :site_name");
        $stmt->execute(['site_name' => $site_name]);

        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value)
                               VALUES ('tmdb_api_key', :api_key)
                               ON DUPLICATE KEY UPDATE setting_value = :api_key");
        $stmt->execute(['api_key' => $api_key]);

        $message = "System parameters updated successfully. Reloading core configurations.";
    } catch (Exception $e) {
        $message = "System Error: " . $e->getMessage();
    }
}

// Fetch current settings
$settings = [];
if ($pdo) {
    try {
        $res = $pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        $settings = $res;
    } catch (Exception $e) {}
}

$current_site_name = $settings['site_name'] ?? 'MoodAI';
$current_api_key = $settings['tmdb_api_key'] ?? TMDB_API_KEY;

?>

<main class="container pb-5">
    <div class="d-flex justify-content-between align-items-end mb-4" data-aos="fade-down">
        <div>
            <h2 class="fw-800 mb-0 text-white"><i class="bi bi-sliders text-purple me-2"></i>Global System Settings</h2>
            <p class="mb-0" style="color: var(--admin-text-secondary) !important;">Configure core platform parameters and API credentials.</p>
        </div>
        <div class="breadcrumb-admin">
            <span class="opacity-50">Admin</span> / <span class="fw-700 text-purple">Settings</span>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert" style="background: rgba(34, 197, 94, 0.1); border-color: #22c55e; color: #fff;">
            <i class="bi bi-check-circle-fill me-2 text-success"></i> <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-6" data-aos="fade-right">
            <div class="dashboard-card h-100">
                <h5 class="card-title-admin mb-4"><i class="bi bi-gear-fill"></i> Core Configuration</h5>
                <form action="settings.php" method="POST">

                    <div class="mb-4">
                        <label class="form-label fw-700 small opacity-50 text-white">PLATFORM BRAND NAME</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-purple"><i class="bi bi-type"></i></span>
                            <input type="text" name="site_name" class="form-control border-secondary bg-dark text-white py-2" value="<?php echo htmlspecialchars($current_site_name); ?>" placeholder="MoodAI">
                        </div>
                        <div class="form-text x-small text-white-50">This name appears in the navigation bar and page titles.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-700 small opacity-50 text-white">TMDB API VERSION 3 KEY</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-purple"><i class="bi bi-key-fill"></i></span>
                            <input type="text" name="tmdb_api_key" class="form-control border-secondary bg-dark text-white py-2" value="<?php echo htmlspecialchars($current_api_key); ?>" placeholder="Enter API Key">
                        </div>
                        <div class="form-text x-small text-white-50">Required for movie fetching and metadata retrieval.</div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn btn-primary-admin fw-800 w-100 py-3 rounded-pill shadow-sm">
                            <i class="bi bi-shield-check me-2"></i> COMMIT CHANGES
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <div class="col-lg-6" data-aos="fade-left">
            <div class="dashboard-card h-100">
                <h5 class="card-title-admin mb-4 text-white"><i class="bi bi-info-circle text-purple"></i> Environment Info</h5>
                <div class="mb-4 p-3 rounded-4" style="background: rgba(255,255,255,0.05);">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small opacity-50 text-white-50">PHP Version</span>
                        <span class="fw-700 text-white"><?php echo phpversion(); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small opacity-50 text-white-50">Server Engine</span>
                        <span class="fw-700 text-white">MoodAI Neural-S1</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small opacity-50 text-white-50">Database Connection</span>
                        <span class="text-success fw-700">OPTIMIZED</span>
                    </div>
                </div>

                <div class="p-3 border border-secondary rounded-4">
                    <h6 class="fw-800 text-purple mb-2">Notice:</h6>
                    <p class="small text-white-50 mb-0">Changes to API credentials take effect immediately across all movie recommendation endpoints. Ensure the key is active on TheMovieDB.org.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'include/footer.php'; ?>
