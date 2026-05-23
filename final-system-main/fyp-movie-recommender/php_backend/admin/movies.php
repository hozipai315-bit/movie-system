<?php
// admin/movies.php
require_once 'include/header.php';

$message = '';
$message_type = 'info';

// --- HANDLE ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $action = $_POST['action'] ?? '';

    // 1. Mood Management
    if ($action === 'add_mood') {
        $name = trim($_POST['mood_name'] ?? '');
        if ($name) {
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO mood_definitions (mood_name) VALUES (?)");
                $stmt->execute([$name]);
                $message = "Mood '$name' initialized in neural database.";
                $message_type = 'success';
            } catch (Exception $e) { $message = $e->getMessage(); $message_type = 'danger'; }
        }
    }

    // 2. Mapping Management
    if ($action === 'save_mapping') {
        $mood_id = (int)($_POST['mood_id'] ?? 0);
        $genre_id = (int)($_POST['genre_id'] ?? 0);
        $genre_name = $_POST['genre_name'] ?? '';
        $weight = (int)($_POST['weight'] ?? 100);

        if ($mood_id && $genre_id) {
            try {
                // Get mood name first
                $mood_name = $pdo->query("SELECT mood_name FROM mood_definitions WHERE id = $mood_id")->fetchColumn();

                $stmt = $pdo->prepare("INSERT INTO mood_genre_mapping (mood_id, mood_name, genre_id, genre_name, weight)
                                       VALUES (:mid, :mname, :gid, :gname, :w)
                                       ON DUPLICATE KEY UPDATE weight = :w, genre_name = :gname");
                $stmt->execute(['mid' => $mood_id, 'mname' => $mood_name, 'gid' => $genre_id, 'gname' => $genre_name, 'w' => $weight]);
                $message = "Neural link updated for $mood_name -> $genre_name ($weight%)";
                $message_type = 'success';
            } catch (Exception $e) { $message = $e->getMessage(); $message_type = 'danger'; }
        }
    }

    if ($action === 'delete_mapping') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $pdo->prepare("DELETE FROM mood_genre_mapping WHERE id = ?")->execute([$id]);
            $message = "Neural link terminated.";
        } catch (Exception $e) { $message = $e->getMessage(); }
    }

    if ($action === 'delete_mood') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $pdo->prepare("DELETE FROM mood_definitions WHERE id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM mood_genre_mapping WHERE mood_id = ?")->execute([$id]);
            $message = "Mood and all its links purged from system.";
        } catch (Exception $e) { $message = $e->getMessage(); }
    }
}

// --- FETCH DATA ---
$moods = $pdo ? $pdo->query("SELECT * FROM mood_definitions ORDER BY mood_name ASC")->fetchAll() : [];
$mappings = $pdo ? $pdo->query("SELECT m.*, d.mood_name FROM mood_genre_mapping m JOIN mood_definitions d ON m.mood_id = d.id ORDER BY d.mood_name ASC, m.weight DESC")->fetchAll() : [];

$tmdb_genres = [
    28 => 'Action', 12 => 'Adventure', 16 => 'Animation', 35 => 'Comedy', 80 => 'Crime',
    99 => 'Documentary', 18 => 'Drama', 10751 => 'Family', 14 => 'Fantasy', 36 => 'History',
    27 => 'Horror', 10402 => 'Music', 9648 => 'Mystery', 10749 => 'Romance', 878 => 'Sci-Fi',
    10770 => 'TV Movie', 53 => 'Thriller', 10752 => 'War', 37 => 'Western'
];
?>

<main class="container pb-5">
    <div class="d-flex justify-content-between align-items-end mb-4" data-aos="fade-down">
        <div>
            <h2 class="fw-800 mb-0 text-white"><i class="bi bi-cpu-fill text-purple me-2"></i>Movie Data Engine</h2>
            <p class="mb-0" style="color: #949494;">Connect moods to movie genres and set their strength.</p>
        </div>
        <div class="breadcrumb-admin">
            <span class="opacity-50">Admin</span> / <span class="fw-700 text-purple">Movie Engine</span>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show rounded-4 mb-4" role="alert" style="background: rgba(168, 144, 254, 0.1); border-color: var(--admin-purple); color: #fff;">
            <i class="bi bi-info-circle-fill me-2 text-purple"></i> <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Sidebar: Mood Management -->
        <div class="col-lg-4">
            <!-- Add New Mood -->
            <div class="dashboard-card mb-4" data-aos="fade-right">
                <h5 class="card-title-admin mb-3"><i class="bi bi-plus-circle text-purple"></i> Add New Mood</h5>
                <form action="movies.php" method="POST" class="d-flex gap-2">
                    <input type="hidden" name="action" value="add_mood">
                    <input type="text" name="mood_name" class="form-control rounded-pill bg-dark border-secondary text-white" placeholder="e.g. Happy" required>
                    <button type="submit" class="btn btn-primary-admin rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="bi bi-plus fs-4"></i></button>
                </form>
            </div>

            <!-- Mood List -->
            <div class="dashboard-card" data-aos="fade-right" data-aos-delay="100">
                <h5 class="card-title-admin mb-4"><i class="bi bi-list-ul"></i> System Moods</h5>
                <div class="list-group list-group-flush history-scroll-container" style="max-height: 400px;">
                    <?php foreach ($moods as $m): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0 py-3 border-bottom border-secondary">
                            <span class="fw-800 text-uppercase small text-white" style="letter-spacing: 1px;"><?php echo htmlspecialchars($m['mood_name']); ?></span>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-purple border-0" onclick="deleteMood(<?php echo $m['id']; ?>, '<?php echo addslashes($m['mood_name']); ?>')"><i class="bi bi-trash3"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Main Content: Linker & Preview -->
        <div class="col-lg-8">

            <!-- Linker Editor -->
            <div class="dashboard-card mb-4" data-aos="fade-up">
                <h5 class="card-title-admin mb-4"><i class="bi bi-link-45deg"></i> Link Moods to Genres</h5>
                <form action="movies.php" method="POST" id="mappingForm">
                    <input type="hidden" name="action" value="save_mapping">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-700 opacity-50 text-white">TARGET MOOD</label>
                            <select name="mood_id" class="form-select rounded-3 bg-dark border-secondary text-white" required>
                                <?php foreach ($moods as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['mood_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-700 opacity-50 text-white">TMDB GENRE</label>
                            <select name="genre_id" class="form-select rounded-3 bg-dark border-secondary text-white" required onchange="updateGenreName(this)">
                                <?php foreach ($tmdb_genres as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" data-name="<?php echo $name; ?>"><?php echo $name; ?> (ID: <?php echo $id; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="genre_name" id="genreNameInput" value="Action">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-700 opacity-50 text-white">STRENGTH %</label>
                            <input type="number" name="weight" class="form-control rounded-3 bg-dark border-secondary text-white" value="100" min="1" max="100">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary-admin w-100 rounded-3 py-2 fw-800">LINK</button>
                        </div>
                    </div>
                </form>

                <div class="mt-4 table-responsive favorites-scroll-container" style="max-height: 300px;">
                    <table class="table admin-table">
                        <thead class="sticky-top bg-dark">
                            <tr><th>Mood</th><th>Genre</th><th>Strength</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mappings as $row): ?>
                                <tr>
                                    <td class="fw-800 text-purple text-uppercase"><?php echo htmlspecialchars($row['mood_name']); ?></td>
                                    <td class="text-white"><?php echo htmlspecialchars($row['genre_name']); ?> <small class="text-muted">(<?php echo $row['genre_id']; ?>)</small></td>
                                    <td>
                                        <div class="progress bg-dark" style="height: 6px; width: 60px; border: 1px solid rgba(255,255,255,0.1);">
                                            <div class="progress-bar bg-purple" style="width: <?php echo $row['weight']; ?>%"></div>
                                        </div>
                                        <span class="x-small fw-700 text-white-50"><?php echo $row['weight']; ?>%</span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-dark text-purple border-secondary" onclick="testMapping('<?php echo addslashes($row['mood_name']); ?>', <?php echo $row['genre_id']; ?>)"><i class="bi bi-play-circle-fill"></i> TEST</button>
                                        <form action="movies.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete_mapping">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-dark text-danger border-secondary"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recommendation Preview (AJAX) -->
            <div id="previewCard" class="dashboard-card d-none" data-aos="fade-up">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title-admin mb-0"><i class="bi bi-lightning-charge-fill text-warning"></i> Neural Recommendation Preview</h5>
                    <span id="previewLabel" class="badge bg-purple rounded-pill text-dark"></span>
                </div>
                <div id="previewGrid" class="row g-3">
                    <!-- Movies injected here -->
                </div>
            </div>

        </div>
    </div>
</main>

<script>
function updateGenreName(select) {
    document.getElementById('genreNameInput').value = select.options[select.selectedIndex].getAttribute('data-name');
}

function deleteMood(id, name) {
    if (confirm(`CRITICAL: Purge Mood '${name}' and all associated neural links?`)) {
        const f = document.createElement('form');
        f.method = 'POST'; f.action = 'movies.php';
        f.innerHTML = `<input type="hidden" name="action" value="delete_mood"><input type="hidden" name="id" value="${id}">`;
        document.body.appendChild(f); f.submit();
    }
}

function testMapping(mood, genreId) {
    const card = document.getElementById('previewCard');
    const grid = document.getElementById('previewGrid');
    const label = document.getElementById('previewLabel');

    card.classList.remove('d-none');
    label.innerText = `TESTING: ${mood} -> Genre ${genreId}`;
    grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-purple" role="status"></div><p class="mt-2 text-muted">Synchronizing with TMDB...</p></div>';

    fetch(`../api/get_movies_api.php?genre=${genreId}`)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success' && data.results.length > 0) {
                grid.innerHTML = '';
                data.results.slice(0, 4).forEach(movie => {
                    grid.innerHTML += `
                        <div class="col-md-3">
                            <div class="bg-dark rounded-4 overflow-hidden h-100 border border-secondary">
                                <img src="${movie.poster_full_path}" class="w-100" style="height: 150px; object-fit: cover;">
                                <div class="p-2">
                                    <h6 class="fw-800 small text-truncate mb-1 text-white">${movie.title}</h6>
                                    <span class="badge bg-secondary text-white border-0 small"><i class="bi bi-star-fill text-warning"></i> ${movie.vote_average}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                grid.innerHTML = '<div class="col-12 alert alert-warning bg-dark text-warning border-warning">No preview results found for this mapping.</div>';
            }
        })
        .catch(e => {
            grid.innerHTML = '<div class="col-12 alert alert-danger bg-dark text-danger border-danger">Preview Engine Offline. Check API credentials.</div>';
        });
}
</script>

<?php require_once 'include/footer.php'; ?>
