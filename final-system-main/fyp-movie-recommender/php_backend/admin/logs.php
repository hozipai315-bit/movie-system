<?php
// admin/logs.php
require_once 'include/header.php';

// Search and Filter Logic
$search = $_GET['search'] ?? '';
$method_filter = $_GET['method'] ?? '';
$mood_filter = $_GET['mood'] ?? '';

// --- LOGS DATA ---
$logs = [];
if ($pdo) {
    try {
        $query = "SELECT h.*, u.username
                  FROM user_mood_history h
                  JOIN users u ON h.user_id = u.user_id
                  WHERE 1=1";
        $params = [];

        if ($search) {
            $query .= " AND (u.username LIKE :search OR h.mood LIKE :search)";
            $params['search'] = "%$search%";
        }
        if ($method_filter) {
            $query .= " AND h.input_type = :method";
            $params['method'] = $method_filter;
        }
        if ($mood_filter) {
            $query .= " AND h.mood = :mood";
            $params['mood'] = $mood_filter;
        }

        $query .= " ORDER BY h.detected_at DESC LIMIT 100";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();
    } catch (Exception $e) {}
}


// --- ANALYTICS ---
$stats = ['total' => 0, 'top_mood' => 'N/A', 'most_active' => 'N/A'];
if ($pdo) {
    try {
        $stats['total'] = $pdo->query("SELECT COUNT(*) FROM user_mood_history")->fetchColumn();
        $stats['top_mood'] = $pdo->query("SELECT mood FROM user_mood_history GROUP BY mood ORDER BY COUNT(*) DESC LIMIT 1")->fetchColumn() ?: 'N/A';
        $stats['most_active'] = $pdo->query("SELECT u.username FROM user_mood_history h JOIN users u ON h.user_id = u.user_id GROUP BY h.user_id ORDER BY COUNT(*) DESC LIMIT 1")->fetchColumn() ?: 'N/A';
    } catch (Exception $e) {}
}

// Fetch unique moods for filter
$available_moods = [];
if ($pdo) {
    try {
        $available_moods = $pdo->query("SELECT DISTINCT mood FROM user_mood_history")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {}
}
if (empty($available_moods)) {
    $available_moods = ['Happy', 'Sad', 'Excited', 'Angry', 'Neutral'];
}

?>

<main class="container pb-5">
    <!-- Header Section -->
    <?php if (!$pdo): ?>
        <div class="alert alert-danger rounded-4 mb-4 border-2 shadow-sm" role="alert" style="background: rgba(220, 53, 69, 0.1); border-color: #dc3545; color: #fff;">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-danger"></i>
                <div>
                    <h5 class="alert-heading fw-800 mb-1">DATABASE OFFLINE</h5>
                    <p class="mb-0 small opacity-75">The central neural database is unreachable. System activity logs are not being synchronized. Error: <?php echo htmlspecialchars($db_error ?? 'Unknown connection error'); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-end mb-4" data-aos="fade-down">
        <div>
            <h2 class="fw-800 mb-0 text-white">System Activity Logs</h2>
            <p class="mb-0" style="color: #949494;">Check real-time mood scans and user activity.</p>
        </div>
        <div class="breadcrumb-admin">
            <span class="opacity-50">Admin</span> / <span class="fw-700 text-purple">System Logs</span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="dashboard-card py-3 px-4 h-100">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-purple-subtle p-3 me-3">
                        <i class="bi bi-activity text-purple h4 mb-0"></i>
                    </div>
                    <div>
                        <div class="small text-uppercase fw-700 opacity-50 text-white-50">Total Scans</div>
                        <div class="fw-800 h4 mb-0 text-white"><?php echo number_format($stats['total']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="dashboard-card py-3 px-4 h-100">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle p-3 me-3">
                        <i class="bi bi-graph-up-arrow text-primary h4 mb-0"></i>
                    </div>
                    <div>
                        <div class="small text-uppercase fw-700 opacity-50 text-white-50">Peak Mood</div>
                        <div class="fw-800 h4 mb-0 text-uppercase text-white"><?php echo htmlspecialchars($stats['top_mood']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="dashboard-card py-3 px-4 h-100">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle p-3 me-3">
                        <i class="bi bi-person-check text-success h4 mb-0"></i>
                    </div>
                    <div>
                        <div class="small text-uppercase fw-700 opacity-50 text-white-50">Top User</div>
                        <div class="fw-800 h4 mb-0 text-white"><?php echo htmlspecialchars($stats['most_active']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="dashboard-card mb-4" data-aos="fade-up">
        <form action="logs.php" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-dark border-secondary border-start-0 ps-0 text-white" placeholder="Search by user or mood..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="method" class="form-select bg-dark border-secondary text-white">
                    <option value="">All Methods</option>
                    <option value="text" <?php echo $method_filter == 'text' ? 'selected' : ''; ?>>Text</option>
                    <option value="voice" <?php echo $method_filter == 'voice' ? 'selected' : ''; ?>>Voice</option>
                    <option value="face" <?php echo $method_filter == 'face' ? 'selected' : ''; ?>>Face Analysis</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="mood" class="form-select bg-dark border-secondary text-white">
                    <option value="">All Moods</option>
                    <?php foreach ($available_moods as $m): ?>
                        <option value="<?php echo htmlspecialchars($m); ?>" <?php echo $mood_filter == $m ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary-admin fw-700">Filter</button>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="dashboard-card" data-aos="fade-up">
        <div class="table-responsive history-scroll-container" style="max-height: 600px;">
            <table class="table admin-table">
                <thead class="sticky-top bg-dark" style="z-index: 10;">
                    <tr>
                        <th>User</th>
                        <th>Detected Mood</th>
                        <th>Method</th>
                        <th>Detection Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">No activity logs found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle text-purple me-2"></i>
                                        <span class="fw-800 text-white"><?php echo htmlspecialchars($log['username']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-uppercase fw-800" style="letter-spacing: 1px; color: var(--admin-purple);">
                                        <?php echo htmlspecialchars($log['mood']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="method-badge badge-<?php echo $log['input_type']; ?>">
                                        <?php echo $log['input_type']; ?>
                                    </span>
                                </td>
                                <td class="text-white-50">
                                    <i class="bi bi-clock-history me-1 text-purple"></i>
                                    <?php echo date('M d, Y @ H:i:s', strtotime($log['detected_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once 'include/footer.php'; ?>
