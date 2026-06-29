<?php
// admin/settings.php
require_once 'include/header.php';

$message = '';
$error = '';
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    try {
        if ($action === 'update_site_name') {
            $site_name = $_POST['site_name'] ?? 'MoodAI';
            $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value)
                                   VALUES ('site_name', :site_name)
                                   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute(['site_name' => $site_name]);
            $message = "Site name updated successfully.";
        } elseif ($action === 'update_api_key') {
            $api_key = $_POST['tmdb_api_key'] ?? '';
            $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value)
                                   VALUES ('tmdb_api_key', :api_key)
                                   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute(['api_key' => $api_key]);
            $message = "API key updated successfully.";
        }
    } catch (Exception $e) {
        $error = "System Error: " . $e->getMessage();
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

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4" role="alert" style="background: rgba(220, 53, 69, 0.1); border-color: #dc3545; color: #fff;">
            <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="row g-4">
                <!-- Section 1: Site Name -->
                <div class="col-12" data-aos="fade-right">
                    <div class="dashboard-card">
                        <h5 class="card-title-admin mb-4"><i class="bi bi-type"></i> Platform Brand Name</h5>
                        <form action="settings.php" method="POST">
                            <input type="hidden" name="action" value="update_site_name">
                            <div class="mb-4">
                                <label class="form-label fw-700 small opacity-50 text-white text-uppercase">Site Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-purple"><i class="bi bi-fonts"></i></span>
                                    <input type="text" name="site_name" class="form-control border-secondary bg-dark text-white py-2" value="<?php echo htmlspecialchars($current_site_name); ?>" placeholder="MoodAI" required>
                                </div>
                                <div class="form-text x-small text-white-50">This name appears in the navigation bar and page titles.</div>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn btn-primary-admin fw-800 w-100 py-3 rounded-pill shadow-sm text-uppercase">
                                    <i class="bi bi-shield-check me-2"></i> Update Site Name
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Section 2: TMDB API Key -->
                <div class="col-12" data-aos="fade-right" data-aos-delay="100">
                    <div class="dashboard-card">
                        <h5 class="card-title-admin mb-4"><i class="bi bi-key-fill"></i> TMDB API Key</h5>
                        <form action="settings.php" method="POST">
                            <input type="hidden" name="action" value="update_api_key">
                            <div class="mb-4">
                                <label class="form-label fw-700 small opacity-50 text-white text-uppercase">TMDB API VERSION 3 KEY</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-purple"><i class="bi bi-plug-fill"></i></span>
                                    <input type="text" name="tmdb_api_key" class="form-control border-secondary bg-dark text-white py-2" value="<?php echo htmlspecialchars($current_api_key); ?>" placeholder="Enter API Key" required>
                                </div>
                                <div class="form-text x-small text-white-50">Required for movie fetching and metadata retrieval.</div>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn btn-primary-admin fw-800 w-100 py-3 rounded-pill shadow-sm text-uppercase">
                                    <i class="bi bi-shield-check me-2"></i> Update API Key
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Environment Info -->
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
