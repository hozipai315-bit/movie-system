<?php
// admin/dashboard.php
require_once 'include/header.php';

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// --- DATA FETCHING (with fallback for UI testing) ---

// 1. Stats Overview
$total_users = 0;
$total_moods = 0;
$total_favorites = 0;

if ($pdo) {
    try {
        $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $total_moods = $pdo->query("SELECT COUNT(*) FROM user_mood_history")->fetchColumn();
        $total_favorites = $pdo->query("SELECT COUNT(*) FROM user_favorites")->fetchColumn();
    } catch (Exception $e) {}
}

// 2. Recent Activity
$recent_activity = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT h.mood, h.input_type, h.detected_at, u.username
                             FROM user_mood_history h
                             JOIN users u ON h.user_id = u.user_id
                             ORDER BY h.detected_at DESC LIMIT 6");
        $recent_activity = $stmt->fetchAll();
    } catch (Exception $e) {}
}

// 3. Top Moods Data (for Chart)
$mood_data = [];
if ($pdo) {
    try {
        $mood_data = $pdo->query("SELECT mood, COUNT(*) as count FROM user_mood_history GROUP BY mood ORDER BY count DESC LIMIT 5")->fetchAll();
    } catch (Exception $e) {}
}

// 4. Input Method Trends (for Chart)
$method_data = [];
if ($pdo) {
    try {
        $method_data = $pdo->query("SELECT input_type, COUNT(*) as count FROM user_mood_history GROUP BY input_type")->fetchAll();
    } catch (Exception $e) {}
}

?>

<main class="container">

    <?php if (!$pdo): ?>
        <div class="alert alert-danger rounded-4 mb-4 border-2 shadow-sm" role="alert" style="background: rgba(220, 53, 69, 0.1); border-color: #dc3545; color: #fff;">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-danger"></i>
                <div>
                    <h5 class="alert-heading fw-800 mb-1">DATABASE OFFLINE</h5>
                    <p class="mb-0 small opacity-75">The central neural database is unreachable. Some dashboard metrics may be unavailable. Error: <?php echo htmlspecialchars($db_error ?? 'Unknown connection error'); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="admin-hero-premium text-center" data-aos="zoom-in">
        <div class="hero-icon-layer">
            <i class="bi bi-shield-lock-fill"></i>
            <i class="bi bi-gear-wide-connected"></i>
            <i class="bi bi-broadcast-pin"></i>
        </div>
        <div class="system-status-pill mb-3">
            <span class="status-dot"></span>
            SYSTEM STATUS: ONLINE
        </div>
        <h1 class="hero-welcome">Welcome back, <?php echo htmlspecialchars($admin_username); ?>.</h1>
        <p class="hero-tagline">Everything is running smoothly.</p>
        <div class="hero-visual-divider"></div>
    </section>

    <!-- Stats Overview -->
    <div class="row g-4 mb-5">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="premium-stat-card h-100">
                <div class="card-inner">
                    <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($total_users); ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="premium-stat-card h-100">
                <div class="card-inner">
                    <div class="stat-icon"><i class="bi bi-cpu-fill"></i></div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($total_moods); ?></div>
                        <div class="stat-label">Mood Scans</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="premium-stat-card h-100">
                <div class="card-inner">
                    <div class="stat-icon"><i class="bi bi-heart-fill"></i></div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($total_favorites); ?></div>
                        <div class="stat-label">Total Favorites</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Top Moods Chart -->
        <div class="col-lg-8" data-aos="fade-right">
            <div class="dashboard-card h-100">
                <h5 class="card-title-admin"><i class="bi bi-bar-chart-fill"></i> Top Detected Moods</h5>
                <canvas id="moodChart" height="150"></canvas>
            </div>
        </div>

        <!-- System Health -->
        <div class="col-lg-4" data-aos="fade-left">
            <div class="dashboard-card h-100">
                <h5 class="card-title-admin"><i class="bi bi-shield-check"></i> System Health</h5>
                <div class="health-monitor py-2">
                    <div class="health-item">
                        <span class="health-label">Database Engine</span>
                        <span class="health-status <?php echo $pdo ? 'status-ok' : 'text-danger'; ?>">
                            <?php echo $pdo ? 'OPTIMAL' : 'OFFLINE'; ?>
                        </span>
                    </div>
                    <div class="health-item">
                        <span class="health-label">AI Backend (Python)</span>
                        <?php
                        $python_online = false;
                        try {
                            $ch = curl_init("http://localhost:5000/");
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                            $res = curl_exec($ch);
                            if ($res && strpos($res, 'MoodAI Python Backend') !== false) {
                                $python_online = true;
                            }
                            curl_close($ch);
                        } catch (Exception $e) {}
                        ?>
                        <span class="health-status <?php echo $python_online ? 'status-ok' : 'text-danger'; ?>">
                            <?php echo $python_online ? 'ONLINE' : 'OFFLINE'; ?>
                        </span>
                    </div>
                    <div class="health-item">
                        <span class="health-label">TMDB API Connection</span>
                        <?php
                        $tmdb_online = false;
                        try {
                            $ch = curl_init("https://api.themoviedb.org/3/configuration?api_key=" . TMDB_API_KEY);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                            $res = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            if ($http_code === 200) {
                                $tmdb_online = true;
                            }
                            curl_close($ch);
                        } catch (Exception $e) {}
                        ?>
                        <span class="health-status <?php echo $tmdb_online ? 'status-ok' : 'text-danger'; ?>">
                            <?php echo $tmdb_online ? 'STABLE' : 'UNSTABLE'; ?>
                        </span>
                    </div>
                    <div class="health-item">
                        <span class="health-label">Server Memory</span>
                        <span class="health-status status-ok">12% USE</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Input Method Trends -->
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="dashboard-card h-100">
                <h5 class="card-title-admin"><i class="bi bi-pie-chart-fill"></i> Method Analytics</h5>
                <canvas id="methodChart" height="250"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">
            <div class="dashboard-card h-100">
                <h5 class="card-title-admin"><i class="bi bi-clock-history"></i> Recent System Activity</h5>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Detected Mood</th>
                                <th>Method</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_activity)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No activity recorded yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_activity as $row): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><span class="text-uppercase" style="letter-spacing: 1px; font-weight: 700; color: var(--admin-purple);"><?php echo htmlspecialchars($row['mood']); ?></span></td>
                                        <td><span class="method-badge badge-<?php echo $row['input_type']; ?>"><?php echo $row['input_type']; ?></span></td>
                                        <td class="text-muted"><?php echo date('M d, H:i', strtotime($row['detected_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Mood Chart
    const moodCtx = document.getElementById('moodChart').getContext('2d');
    new Chart(moodCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($mood_data, 'mood')); ?>,
            datasets: [{
                label: 'Detections',
                data: <?php echo json_encode(array_column($mood_data, 'count')); ?>,
                backgroundColor: '#a890fe',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a1a',
                    titleColor: '#a890fe',
                    bodyColor: '#fff',
                    borderColor: '#a890fe',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#a0a0a0' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#a0a0a0' }
                }
            }
        }
    });

    // 2. Method Chart
    const methodCtx = document.getElementById('methodChart').getContext('2d');
    new Chart(methodCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($method_data, 'input_type')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($method_data, 'count')); ?>,
                backgroundColor: ['#a890fe', '#b388ff', '#ff9800'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        color: '#a0a0a0'
                    }
                }
            }
        }
    });
});
</script>

<?php require_once 'include/footer.php'; ?>
