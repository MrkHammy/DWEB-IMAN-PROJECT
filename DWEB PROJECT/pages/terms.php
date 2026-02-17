<?php
/**
 * Fox Lab – Terminologies / Glossary
 * Sidebar filtering by category + A-Z index, detail view
 * AJAX search suggestions endpoint
 */
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();

// --- AJAX: Toggle Bookmark ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_bookmark') {
    header('Content-Type: application/json');
    if (!isLoggedIn()) {
        echo json_encode(['error' => 'Login required']);
        exit;
    }
    $termId = (int)($_POST['term_id'] ?? 0);
    $userId = (int)$_SESSION['user_id'];
    if ($termId <= 0) {
        echo json_encode(['error' => 'Invalid term']);
        exit;
    }
    // Check if already bookmarked
    $stmtCheck = $pdo->prepare("SELECT id FROM user_bookmarks WHERE user_id = :uid AND term_id = :tid");
    $stmtCheck->execute([':uid' => $userId, ':tid' => $termId]);
    if ($stmtCheck->fetch()) {
        // Remove bookmark
        $pdo->prepare("DELETE FROM user_bookmarks WHERE user_id = :uid AND term_id = :tid")->execute([':uid' => $userId, ':tid' => $termId]);
        echo json_encode(['bookmarked' => false]);
    } else {
        // Add bookmark
        $pdo->prepare("INSERT INTO user_bookmarks (user_id, term_id) VALUES (:uid, :tid)")->execute([':uid' => $userId, ':tid' => $termId]);
        echo json_encode(['bookmarked' => true]);
    }
    exit;
}

// --- AJAX Search Suggestions ---
if (isset($_GET['action']) && $_GET['action'] === 'suggest') {
    header('Content-Type: application/json');
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $results = [];
    if (strlen($q) >= 1) {
        $stmt = $pdo->prepare("SELECT id, title, category FROM terms WHERE title LIKE :q OR definition LIKE :q2 ORDER BY 
            CASE WHEN title LIKE :q3 THEN 0 ELSE 1 END, title ASC LIMIT 8");
        $stmt->execute([':q' => '%' . $q . '%', ':q2' => '%' . $q . '%', ':q3' => $q . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode($results);
    exit;
}

$pageTitle = 'Terminologies';

// --- Filters ---
$filterCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
$filterLetter = isset($_GET['letter']) ? strtoupper(trim($_GET['letter'])) : '';
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$viewTermId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// --- Bookmark filter ---
$showBookmarks = isset($_GET['bookmarks']) && $_GET['bookmarks'] === '1';

// --- Get user bookmarks ---
$userBookmarkIds = [];
$bookmarkCount = 0;
if (isLoggedIn()) {
    $stmtBm = $pdo->prepare("SELECT term_id FROM user_bookmarks WHERE user_id = :uid");
    $stmtBm->execute([':uid' => (int)$_SESSION['user_id']]);
    $userBookmarkIds = array_column($stmtBm->fetchAll(), 'term_id');
    $bookmarkCount = count($userBookmarkIds);
}

// --- Category counts ---
$stmtCats = $pdo->query("SELECT category, COUNT(*) as cnt FROM terms GROUP BY category ORDER BY category ASC");
$catCounts = $stmtCats->fetchAll();
$totalTerms = array_sum(array_column($catCounts, 'cnt'));

// --- Build query ---
$where = [];
$params = [];

if ($filterCategory !== '' && $filterCategory !== 'All') {
    $where[] = "category = :cat";
    $params[':cat'] = $filterCategory;
}
if ($filterLetter !== '') {
    $where[] = "title LIKE :letter";
    $params[':letter'] = $filterLetter . '%';
}
if ($searchQuery !== '') {
    $where[] = "(title LIKE :q OR definition LIKE :q2)";
    $params[':q'] = '%' . $searchQuery . '%';
    $params[':q2'] = '%' . $searchQuery . '%';
}

// If showing bookmarks, filter to only bookmarked term IDs
if ($showBookmarks && !empty($userBookmarkIds)) {
    $placeholders = implode(',', array_fill(0, count($userBookmarkIds), '?'));
    $where[] = "id IN ($placeholders)";
    $params = array_merge(array_values($params), $userBookmarkIds);
    $whereClause = 'WHERE ' . implode(' AND ', $where);
    $stmtTerms = $pdo->prepare("SELECT id, title, category FROM terms $whereClause ORDER BY title ASC");
    $stmtTerms->execute(array_values($params));
} elseif ($showBookmarks && empty($userBookmarkIds)) {
    $termsList = [];
} else {
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $stmtTerms = $pdo->prepare("SELECT id, title, category FROM terms $whereClause ORDER BY title ASC");
    $stmtTerms->execute($params);
}
if (!isset($termsList)) {
    $termsList = $stmtTerms->fetchAll();
}

// --- Get selected term ---
$selectedTerm = null;
$relatedTerms = [];
$threats = [];

if ($viewTermId > 0) {
    $stmtTerm = $pdo->prepare("SELECT * FROM terms WHERE id = :id LIMIT 1");
    $stmtTerm->execute([':id' => $viewTermId]);
    $selectedTerm = $stmtTerm->fetch();
} elseif (!empty($termsList)) {
    $viewTermId = $termsList[0]['id'];
    $stmtTerm = $pdo->prepare("SELECT * FROM terms WHERE id = :id LIMIT 1");
    $stmtTerm->execute([':id' => $viewTermId]);
    $selectedTerm = $stmtTerm->fetch();
}

if ($selectedTerm) {
    $pageTitle = $selectedTerm['title'];
    
    $stmtRelated = $pdo->prepare("SELECT * FROM term_related WHERE term_id = :tid ORDER BY id ASC");
    $stmtRelated->execute([':tid' => $selectedTerm['id']]);
    $relatedTerms = $stmtRelated->fetchAll();
    
    $stmtThreats = $pdo->prepare("SELECT * FROM term_threats WHERE term_id = :tid ORDER BY id ASC");
    $stmtThreats->execute([':tid' => $selectedTerm['id']]);
    $threats = $stmtThreats->fetchAll();
    
    // Per-term learning resources
    $termResources = [];
    try {
        $stmtRes = $pdo->prepare("SELECT * FROM term_resources WHERE term_id = :tid ORDER BY id ASC");
        $stmtRes->execute([':tid' => $selectedTerm['id']]);
        $termResources = $stmtRes->fetchAll();
    } catch (PDOException $e) {
        // table may not exist yet – fall back to empty
        $termResources = [];
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="terms-page">
    <!-- ===== LEFT SIDEBAR ===== -->
    <aside class="terms-sidebar">
        <div class="terms-search">
            <form method="GET" action="terms.php" autocomplete="off">
                <div class="search-wrapper">
                    <input type="text" name="q" id="termSearchInput" placeholder="Search terms..." value="<?php echo e($searchQuery); ?>">
                    <div id="searchSuggestions" class="search-suggestions"></div>
                </div>
            </form>
        </div>

        <div class="filter-header">
            <i class="fas fa-filter"></i> Filter by Category
        </div>

        <ul class="category-filters">
            <li class="<?php echo ($filterCategory === '' || $filterCategory === 'All') && !$showBookmarks ? 'active' : ''; ?>" onclick="window.location='terms.php'">
                All Terms <span class="count">(<?php echo $totalTerms; ?>)</span>
            </li>
            <?php foreach ($catCounts as $cat): ?>
            <li class="<?php echo $filterCategory === $cat['category'] && !$showBookmarks ? 'active' : ''; ?>" onclick="window.location='terms.php?category=<?php echo urlencode($cat['category']); ?>'">
                <?php echo e($cat['category']); ?> <span class="count">(<?php echo $cat['cnt']; ?>)</span>
            </li>
            <?php endforeach; ?>
            <?php if (isLoggedIn()): ?>
            <li class="bookmark-filter-item <?php echo $showBookmarks ? 'active' : ''; ?>" onclick="window.location='terms.php?bookmarks=1'">
                <i class="fas fa-bookmark"></i> Bookmarked <span class="count">(<?php echo $bookmarkCount; ?>)</span>
            </li>
            <?php endif; ?>
        </ul>

        <div class="alpha-index">
            <?php foreach (range('A', 'Z') as $letter): ?>
            <a href="terms.php?letter=<?php echo $letter; ?>" class="<?php echo $filterLetter === $letter ? 'active' : ''; ?>"><?php echo $letter; ?></a>
            <?php endforeach; ?>
        </div>

        <ul class="terms-list">
            <?php foreach ($termsList as $t): ?>
            <li class="<?php echo $viewTermId == $t['id'] ? 'active' : ''; ?>" onclick="window.location='terms.php?id=<?php echo $t['id']; ?><?php echo $filterCategory ? '&category=' . urlencode($filterCategory) : ''; ?><?php echo $filterLetter ? '&letter=' . urlencode($filterLetter) : ''; ?>'">
                <?php echo e($t['title']); ?>
                <i class="fas fa-chevron-right"></i>
            </li>
            <?php endforeach; ?>
        </ul>

    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <?php if ($selectedTerm): ?>
    <div class="terms-main">
        <div class="term-content">
            <div class="term-header">
                <a href="terms.php" class="term-back"><i class="fas fa-arrow-left"></i></a>
                <div class="term-title-area">
                    <h1><?php echo e($selectedTerm['title']); ?></h1>
                    <span class="term-subcategory"><?php echo e($selectedTerm['subcategory']); ?></span>
                    <?php if ($selectedTerm['pronunciation']): ?>
                    <span class="term-pronunciation"><?php echo e($selectedTerm['pronunciation']); ?></span>
                    <?php endif; ?>
                </div>

            </div>

            <div class="term-actions-row">
                <?php if ($selectedTerm['pronunciation']): ?>
                <button class="audio-btn" onclick="speakTerm('<?php echo e($selectedTerm['title']); ?>')">
                    <i class="fas fa-volume-up"></i> Listen
                </button>
                <?php endif; ?>

                <?php if (isLoggedIn()): ?>
                <?php $isBookmarked = in_array($selectedTerm['id'], $userBookmarkIds); ?>
                <button class="bookmark-btn <?php echo $isBookmarked ? 'bookmarked' : ''; ?>" id="bookmarkBtn" data-term-id="<?php echo $selectedTerm['id']; ?>" onclick="toggleBookmark(this)">
                    <i class="<?php echo $isBookmarked ? 'fas' : 'far'; ?> fa-bookmark"></i>
                    <span><?php echo $isBookmarked ? 'Bookmarked' : 'Bookmark'; ?></span>
                </button>
                <?php endif; ?>
            </div>

            <!-- Definition -->
            <div class="term-section">
                <h2><i class="fas fa-book"></i> Definition</h2>
                <?php 
                $paragraphs = explode("\n\n", $selectedTerm['definition']);
                foreach ($paragraphs as $para): ?>
                <p><?php echo nl2br(e($para)); ?></p>
                <?php endforeach; ?>
            </div>



            <!-- Usage Context -->
            <?php if ($selectedTerm['usage_context']): ?>
            <div class="term-section">
                <h2><i class="fas fa-code"></i> Usage Context</h2>
                <?php 
                $examples = explode("\n\n", $selectedTerm['usage_context']);
                foreach ($examples as $ex): ?>
                <div class="usage-example">
                    <?php echo nl2br(e($ex)); ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Common Related Threats -->
            <?php if (!empty($threats)): ?>
            <div class="term-section">
                <h2><i class="fas fa-exclamation-triangle"></i> Common Related Threats</h2>
                <div class="threat-grid">
                    <?php foreach ($threats as $threat): ?>
                    <div class="threat-card">
                        <h4><?php echo e($threat['threat_title']); ?></h4>
                        <p><?php echo e($threat['threat_desc']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ===== RIGHT SIDEBAR ===== -->
        <div class="terms-right">
            <?php if (!empty($relatedTerms)): ?>
            <div class="related-terms-card">
                <h3><i class="fas fa-link"></i> Related Terms</h3>
                <ul class="related-list">
                    <?php foreach ($relatedTerms as $rt): ?>
                    <li onclick="window.location='<?php echo e($rt['related_url']); ?>'">
                        <?php echo e($rt['related_title']); ?>
                        <i class="fas fa-arrow-right"></i>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="learning-resources-card">
                <h3><i class="fas fa-graduation-cap"></i> Learning Resources</h3>
                <?php if (!empty($termResources)): ?>
                    <?php foreach ($termResources as $res): ?>
                    <a href="<?php echo e($res['resource_url']); ?>" target="_blank" rel="noopener" class="resource-item" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:10px;padding:10px;border-radius:6px;transition:var(--transition);">
                        <i class="<?php echo e($res['resource_icon']); ?>" style="color:var(--accent);"></i>
                        <span><?php echo e($res['resource_title']); ?></span>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <a href="https://owasp.org/www-project-top-ten/" target="_blank" rel="noopener" class="resource-item" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:10px;padding:10px;border-radius:6px;transition:var(--transition);">
                        <i class="fas fa-external-link-alt" style="color:var(--accent);"></i>
                        <span>OWASP Top 10 Security Risks</span>
                    </a>
                    <a href="https://www.nist.gov/cyberframework" target="_blank" rel="noopener" class="resource-item" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:10px;padding:10px;border-radius:6px;transition:var(--transition);">
                        <i class="fas fa-external-link-alt" style="color:var(--accent);"></i>
                        <span>NIST Cybersecurity Framework</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Recommended Pages -->
            <div class="recommended-pages-card">
                <h3><i class="fas fa-compass"></i> Explore Fox Lab</h3>
                <a href="phishing.php" class="recommended-page-item">
                    <div class="rec-page-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <i class="fas fa-fish"></i>
                    </div>
                    <div class="rec-page-info">
                        <span class="rec-page-title">Phishing Simulator</span>
                        <span class="rec-page-desc">Test your ability to spot phishing emails</span>
                    </div>
                </a>
                <a href="checker.php" class="recommended-page-item">
                    <div class="rec-page-icon" style="background: linear-gradient(135deg, #27ae60, #2ecc71);">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="rec-page-info">
                        <span class="rec-page-title">Password Tester</span>
                        <span class="rec-page-desc">Check how strong your passwords are</span>
                    </div>
                </a>
                <a href="compiler.php" class="recommended-page-item">
                    <div class="rec-page-icon" style="background: linear-gradient(135deg, #0074D9, #005fa3);">
                        <i class="fas fa-code"></i>
                    </div>
                    <div class="rec-page-info">
                        <span class="rec-page-title">Code Online</span>
                        <span class="rec-page-desc">Practice Python & Java with tutorials</span>
                    </div>
                </a>
                <a href="blog.php" class="recommended-page-item">
                    <div class="rec-page-icon" style="background: linear-gradient(135deg, #8e44ad, #9b59b6);">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="rec-page-info">
                        <span class="rec-page-title">Security Blogs</span>
                        <span class="rec-page-desc">Read the latest cybersecurity articles</span>
                    </div>
                </a>
                <a href="partners.php" class="recommended-page-item">
                    <div class="rec-page-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="rec-page-info">
                        <span class="rec-page-title">Organizations</span>
                        <span class="rec-page-desc">Our partner organizations & communities</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="terms-main" style="display:flex;align-items:center;justify-content:center;">
        <div style="text-align:center;color:var(--text-muted);">
            <i class="fas fa-search" style="font-size:3rem;margin-bottom:15px;display:block;"></i>
            <h3>No terms found</h3>
            <p>Try adjusting your filters or search query.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function speakTerm(text) {
    if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-US';
        speechSynthesis.speak(utterance);
    }
}

// --- Bookmark Toggle ---
function toggleBookmark(btn) {
    const termId = btn.dataset.termId;
    const icon = btn.querySelector('i');
    const label = btn.querySelector('span');

    fetch('terms.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=toggle_bookmark&term_id=' + encodeURIComponent(termId)
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        if (data.bookmarked) {
            btn.classList.add('bookmarked');
            icon.className = 'fas fa-bookmark';
            label.textContent = 'Bookmarked';
        } else {
            btn.classList.remove('bookmarked');
            icon.className = 'far fa-bookmark';
            label.textContent = 'Bookmark';
        }
        // Update sidebar bookmark count
        const countEl = document.querySelector('.bookmark-filter-item .count');
        if (countEl) {
            let c = parseInt(countEl.textContent.replace(/[()]/g, '')) || 0;
            c = data.bookmarked ? c + 1 : Math.max(0, c - 1);
            countEl.textContent = '(' + c + ')';
        }
    })
    .catch(() => {});
}

// --- Glossary Search Autocomplete ---
(function() {
    const input = document.getElementById('termSearchInput');
    const box   = document.getElementById('searchSuggestions');
    if (!input || !box) return;

    let debounce = null;

    input.addEventListener('input', function() {
        clearTimeout(debounce);
        const q = this.value.trim();
        if (q.length < 1) { box.innerHTML = ''; box.style.display = 'none'; return; }

        debounce = setTimeout(() => {
            fetch('terms.php?action=suggest&q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    if (!data.length) { box.innerHTML = ''; box.style.display = 'none'; return; }
                    box.style.display = 'block';
                    box.innerHTML = data.map(t =>
                        `<div class="suggestion-item" data-id="${t.id}">
                            <span class="suggestion-title">${escHtml(t.title)}</span>
                            <span class="suggestion-cat">${escHtml(t.category)}</span>
                        </div>`
                    ).join('');
                })
                .catch(() => { box.innerHTML = ''; box.style.display = 'none'; });
        }, 200);
    });

    box.addEventListener('click', function(e) {
        const item = e.target.closest('.suggestion-item');
        if (item) window.location = 'terms.php?id=' + item.dataset.id;
    });

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-wrapper')) { box.style.display = 'none'; }
    });

    // Keyboard navigation
    input.addEventListener('keydown', function(e) {
        const items = box.querySelectorAll('.suggestion-item');
        if (!items.length) return;
        let active = box.querySelector('.suggestion-item.active');
        let idx = Array.from(items).indexOf(active);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (active) active.classList.remove('active');
            idx = (idx + 1) % items.length;
            items[idx].classList.add('active');
            items[idx].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (active) active.classList.remove('active');
            idx = idx <= 0 ? items.length - 1 : idx - 1;
            items[idx].classList.add('active');
            items[idx].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter' && active) {
            e.preventDefault();
            window.location = 'terms.php?id=' + active.dataset.id;
        }
    });

    function escHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
