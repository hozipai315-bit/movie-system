<?php
// admin/user_details.php
require_once 'include/header.php';

$user_id = $_GET['id'] ?? 0;

// --- DATA FETCHING ---
$user = null;
$history = [];
$favorites = [];

if ($pdo && $user_id) {
    try {
        // User info
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user) {
            // Mood History
            $stmt = $pdo->prepare("SELECT * FROM user_mood_history WHERE user_id = ? ORDER BY detected_at DESC");
            $stmt->execute([$user_id]);
            $history = $stmt->fetchAll();

            // Favorites
            $stmt = $pdo->prepare("SELECT * FROM user_favorites WHERE user_id = ? ORDER BY saved_at DESC");
            $stmt->execute([$user_id]);
            $favorites = $stmt->fetchAll();
        }
    } catch (Exception $e) {}
}

?>

<main class="container pb-5">
    <?php if (!$pdo): ?>
        <div class="alert alert-danger rounded-4 mb-4 border-2 shadow-sm" role="alert" style="background: rgba(220, 53, 69, 0.1); border-color: #dc3545; color: #fff;">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-danger"></i>
                <div>
                    <h5 class="alert-heading fw-800 mb-1">DATABASE OFFLINE</h5>
                    <p class="mb-0 small opacity-75">The central neural database is unreachable. Error: <?php echo htmlspecialchars($db_error ?? 'Unknown connection error'); ?></p>
                </div>
            </div>
        </div>
    <?php elseif (!$user): ?>
        <div class="alert alert-warning rounded-4 mb-4 border-2 shadow-sm" role="alert" style="background: rgba(255, 193, 7, 0.1); border-color: #ffc107; color: #fff;">
            <div class="d-flex align-items-center">
                <i class="bi bi-person-exclamation fs-3 me-3 text-warning"></i>
                <div>
                    <h5 class="alert-heading fw-800 mb-1">OPERATIVE NOT FOUND</h5>
                    <p class="mb-0 small opacity-75">The requested neural identity (ID: <?php echo htmlspecialchars($user_id); ?>) does not exist in the registry.</p>
                </div>
            </div>
        </div>
        <a href="user.php" class="btn btn-outline-light rounded-pill px-4"><i class="bi bi-arrow-left me-2"></i>Return to Registry</a>
        <?php exit(); ?>
    <?php endif; ?>

    <!-- Header with Back Button -->
    <div class="d-flex align-items-center mb-5" data-aos="fade-right">
        <a href="user.php" class="back-btn-neural me-4">
            <i class="bi bi-arrow-left" style="font-size: 1.5rem;"></i>
        </a>
        <div>
            <h2 class="mb-0 text-white">Operative Profile</h2>
            <p class="mb-0 tech-label">Neural ID: <span class="text-purple"><?php echo htmlspecialchars($user['username']); ?></span></p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar: User Info Card -->
        <div class="col-lg-4" data-aos="fade-up">
            <div class="dashboard-card text-center h-100">
                <div class="user-profile-avatar mx-auto mb-3">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <h3 class="fw-800 mb-1"><?php echo htmlspecialchars($user['username']); ?></h3>
                <p class="text-muted small mb-3"><?php echo htmlspecialchars($user['email']); ?></p>

                <div class="status-indicator-large mb-4">
                    <?php if ($user['status'] == 'active'): ?>
                        <span class="badge bg-success w-100 py-2 rounded-pill">ACCESS GRANTED</span>
                    <?php else: ?>
                        <span class="badge bg-danger w-100 py-2 rounded-pill">ACCESS TERMINATED</span>
                    <?php endif; ?>
                </div>

                <div class="row g-2 text-start mb-4">
                    <div class="col-6">
                        <div class="stats-box p-3 rounded-4">
                            <div class="tech-label" style="font-size: 0.65rem;">Neural Scans</div>
                            <div class="tech-value h4 mb-0"><?php echo count($history); ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stats-box p-3 rounded-4">
                            <div class="tech-label" style="font-size: 0.65rem;">Fav Records</div>
                            <div class="tech-value h4 mb-0"><?php echo count($favorites); ?></div>
                        </div>
                    </div>
                </div>

                <div class="border-top border-secondary pt-4 text-start">
                    <p class="small text-muted mb-1 tech-label">Registration Date</p>
                    <p class="tech-value mb-3"><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></p>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-outline-danger fw-700 py-2 rounded-pill tech-label" onclick="handleToggle(<?php echo $user_id; ?>, '<?php echo $user['status']; ?>')">
                        <i class="bi bi-shield-<?php echo $user['status'] == 'active' ? 'slash' : 'check'; ?> me-2"></i>
                        <?php echo $user['status'] == 'active' ? 'Terminate Access' : 'Restore Access'; ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content: History & Favorites -->
        <div class="col-lg-8 mb-5">
            <!-- Neural History -->
            <div class="dashboard-card mb-4" data-aos="fade-up" data-aos-delay="100">
                <h5 class="card-title-admin mb-4"><i class="bi bi-cpu-fill"></i> Neural Detection History</h5>
                <div class="table-responsive history-scroll-container">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Mood Detected</th>
                                <th>Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">No history found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($history as $row): ?>
                                    <tr>
                                        <td class="text-muted"><?php echo date('M d, H:i', strtotime($row['detected_at'])); ?></td>
                                        <td><span class="fw-800 text-danger text-uppercase" style="letter-spacing: 1px;"><?php echo htmlspecialchars($row['mood']); ?></span></td>
                                        <td><span class="method-badge badge-<?php echo $row['input_type']; ?>"><?php echo $row['input_type']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Favorite Archives -->
            <div class="dashboard-card" data-aos="fade-up" data-aos-delay="200">
                <h5 class="card-title-admin mb-4"><i class="bi bi-heart-fill"></i> Cinematic Favorites</h5>
                <div class="row g-3 favorites-scroll-container">
                    <?php if (empty($favorites)): ?>
                        <div class="col-12 text-center py-4 tech-label" style="color: var(--admin-text-secondary) !important;">No favorite records found.</div>
                    <?php else: ?>
                        <?php foreach ($favorites as $fav): ?>
                            <div class="col-md-6">
                                <div class="fav-card-mini p-3 d-flex align-items-center">
                                    <div class="fav-poster-mini me-3">
                                        <img src="<?php echo htmlspecialchars($fav['movie_poster']); ?>" alt="Poster" class="img-fluid rounded-3">
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="text-truncate mb-1" style="font-family: var(--admin-font-main); text-transform: none;"><?php echo htmlspecialchars($fav['movie_title']); ?></h6>
                                        <div class="badge bg-primary-admin text-dark x-small"><?php echo htmlspecialchars($fav['mood_tag']); ?></div>
                                        <div class="text-muted x-small mt-1 tech-label" style="font-size: 0.65rem;"><?php echo date('Y-m-d', strtotime($fav['saved_at'])); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function handleToggle(userId, currentStatus) {
    toggleStatus(userId, currentStatus, 'user_details.php?id=' + userId);
}
</script>
<?php require_once 'include/footer.php'; ?>
