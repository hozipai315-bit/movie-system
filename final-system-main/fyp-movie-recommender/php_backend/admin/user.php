<?php
// admin/users.php
require_once 'include/header.php';

// Search and Filter Logic
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// --- ANALYTICS DATA ---
$stats = ['total' => 0, 'active' => 0, 'banned' => 0, 'new_today' => 0];

if ($pdo) {
    try {
        $stats['total'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['active'] = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
        $stats['banned'] = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'banned'")->fetchColumn();
        $stats['new_today'] = $pdo->query("SELECT COUNT(*) FROM user_mood_history WHERE DATE(detected_at) = CURDATE()")->fetchColumn();
    } catch (Exception $e) {}
}

// --- USER LIST DATA ---
$users = [];
if ($pdo) {
    try {
        $query = "SELECT * FROM users WHERE 1=1";
        $params = [];
        if ($search) {
            $query .= " AND (username LIKE :search OR email LIKE :search)";
            $params['search'] = "%$search%";
        }
        if ($status_filter) {
            $query .= " AND status = :status";
            $params['status'] = $status_filter;
        }
        $query .= " ORDER BY created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
    } catch (Exception $e) {}
}
?>

<main class="container">
    <!-- Header Section -->
    <?php if (!$pdo): ?>
        <div class="alert alert-danger rounded-4 mb-4 border-2 shadow-sm" role="alert" style="background: rgba(220, 53, 69, 0.1); border-color: #dc3545; color: #fff;">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-danger"></i>
                <div>
                    <h5 class="alert-heading fw-800 mb-1">DATABASE OFFLINE</h5>
                    <p class="mb-0 small opacity-75">The central neural database is unreachable. Operative registry cannot be synchronized. Error: <?php echo htmlspecialchars($db_error ?? 'Unknown connection error'); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-end mb-4" data-aos="fade-down">
        <div>
            <h2 class="fw-800 mb-0 text-white">User Registry</h2>
            <p class="mb-0" style="color: #949494;">Manage system access and monitor user activity.</p>
        </div>
        <div class="breadcrumb-admin">
            <span class="opacity-50">Admin</span> / <span class="fw-700 text-purple">Users</span>
        </div>
    </div>

    <!-- Analytics Overview -->
    <div class="row g-4 mb-5">
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
            <div class="dashboard-card text-center py-4 h-100">
                <div class="text-purple mb-2" style="font-size: 2rem;"><i class="bi bi-people-fill"></i></div>
                <div class="fw-800 h3 mb-0 text-white"><?php echo number_format($stats['total']); ?></div>
                <div class="small text-uppercase fw-700 opacity-50 text-white-50">Total Users</div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="dashboard-card text-center py-4 h-100">
                <div class="text-success mb-2" style="font-size: 2rem;"><i class="bi bi-shield-check"></i></div>
                <div class="fw-800 h3 mb-0 text-white"><?php echo number_format($stats['active']); ?></div>
                <div class="small text-uppercase fw-700 opacity-50 text-white-50">Active Users</div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
            <div class="dashboard-card text-center py-4 h-100">
                <div class="text-secondary mb-2" style="font-size: 2rem;"><i class="bi bi-shield-slash"></i></div>
                <div class="fw-800 h3 mb-0 text-white"><?php echo number_format($stats['banned']); ?></div>
                <div class="small text-uppercase fw-700 opacity-50 text-white-50">Banned Users</div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
            <div class="dashboard-card text-center py-4 h-100" style="border: 2px solid var(--admin-purple);">
                <div class="text-purple mb-2" style="font-size: 2rem;"><i class="bi bi-lightning-fill"></i></div>
                <div class="fw-800 h3 mb-0 text-white"><?php echo number_format($stats['new_today']); ?></div>
                <div class="small text-uppercase fw-700 opacity-50 text-white-50">New Detections Today</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="dashboard-card mb-4" data-aos="fade-up">
        <form action="user.php" method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-dark border-secondary border-start-0 ps-0 text-white" placeholder="Search by username or email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select bg-dark border-secondary text-white">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="banned" <?php echo $status_filter == 'banned' ? 'selected' : ''; ?>>Banned</option>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-primary-admin fw-700">Apply Filters</button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="dashboard-card" data-aos="fade-up">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email Address</th>
                        <th>Joined Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No users found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar-small me-3">
                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                        </div>
                                        <span class="fw-800 text-white"><?php echo htmlspecialchars($user['username']); ?></span>
                                    </div>
                                </td>
                                <td class="text-white-50"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="text-white-50"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['status'] == 'active'): ?>
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-700 border border-success">ACTIVE</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-700 border border-danger">BANNED</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="user_details.php?id=<?php echo $user['user_id']; ?>" class="btn btn-dark border-secondary btn-sm text-purple" title="View Profile">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <button type="button" class="btn btn-dark border-secondary btn-sm text-danger ms-2" onclick="toggleStatus(<?php echo $user['user_id']; ?>, '<?php echo $user['status']; ?>')" title="Toggle Status">
                                            <i class="bi bi-shield-<?php echo $user['status'] == 'active' ? 'slash' : 'check'; ?>"></i>
                                        </button>
                                    </div>
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
