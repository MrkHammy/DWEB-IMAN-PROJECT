<?php
/**
 * Fox Lab – Online Code Compiler (Mock IDE)
 * Sidebar with projects, code editor, output panel (requires login)
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Fox Code – Code & Tutorials';
$pdo = getDBConnection();

// Require login
requireLogin();
$user = currentUser();
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// --- CRUD: Handle new project creation ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $filename = trim($_POST['filename'] ?? 'Untitled.py');
        $language = trim($_POST['language'] ?? 'python');
        $code = $_POST['code'] ?? '';
        $stmt = $pdo->prepare("INSERT INTO projects (user_id, filename, language, code) VALUES (:uid, :fn, :lang, :code)");
        $stmt->execute([':uid' => $userId, ':fn' => $filename, ':lang' => $language, ':code' => $code]);
        header('Location: compiler.php?id=' . $pdo->lastInsertId());
        exit;
    }
    if ($_POST['action'] === 'save' && isset($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE projects SET code = :code, filename = :fn, language = :lang WHERE id = :id AND user_id = :uid");
        $stmt->execute([
            ':code' => $_POST['code'] ?? '',
            ':fn' => trim($_POST['filename'] ?? 'Untitled.py'),
            ':lang' => trim($_POST['language'] ?? 'python'),
            ':id' => (int)$_POST['id'],
            ':uid' => $userId
        ]);
        header('Location: compiler.php?id=' . (int)$_POST['id'] . '&saved=1');
        exit;
    }
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => (int)$_POST['id'], ':uid' => $userId]);
        header('Location: compiler.php');
        exit;
    }
}

// Fetch projects for this user (plus global demo projects with user_id NULL)
$stmtProjects = $pdo->prepare("SELECT id, filename, language, is_recent FROM projects WHERE user_id = :uid OR user_id IS NULL ORDER BY is_recent ASC, updated_at DESC");
$stmtProjects->execute([':uid' => $userId]);
$projects = $stmtProjects->fetchAll();

// Current project
$currentProject = null;
if (isset($_GET['id'])) {
    $stmtCurrent = $pdo->prepare("SELECT * FROM projects WHERE id = :id AND (user_id = :uid OR user_id IS NULL) LIMIT 1");
    $stmtCurrent->execute([':id' => (int)$_GET['id'], ':uid' => $userId]);
    $currentProject = $stmtCurrent->fetch();
} elseif (!empty($projects)) {
    $stmtCurrent = $pdo->prepare("SELECT * FROM projects WHERE id = :id LIMIT 1");
    $stmtCurrent->execute([':id' => $projects[0]['id']]);
    $currentProject = $stmtCurrent->fetch();
}

// Fetch courses
$stmtCourses = $pdo->query("SELECT * FROM courses ORDER BY id ASC LIMIT 3");
$courses = $stmtCourses->fetchAll();

// Language config (from DB)
$stmtLang = $pdo->query("SELECT slug, label, icon FROM languages ORDER BY id ASC");
$langRows = $stmtLang->fetchAll();
$langIcons = [];
$langVersions = [];
foreach ($langRows as $lr) {
    $langIcons[$lr['slug']] = $lr['icon'];
    $langVersions[$lr['slug']] = $lr['label'];
}

$extraScripts = ['compiler.js'];
include __DIR__ . '/../includes/header.php';
?>

<div class="compiler-layout">
    <!-- ===== SIDEBAR ===== -->
    <aside class="compiler-sidebar">
        <h3>My Projects</h3>
        <button class="new-project-btn" onclick="document.getElementById('newProjectModal').style.display='flex'">
            <i class="fas fa-plus"></i> New Project
        </button>

        <ul class="project-list">
            <?php 
            $mainProjects = array_filter($projects, fn($p) => !$p['is_recent']);
            $recentProjects = array_filter($projects, fn($p) => $p['is_recent']);
            
            foreach ($mainProjects as $p): 
                $icon = $langIcons[$p['language']] ?? 'fas fa-file-code';
                $isActive = ($currentProject && $currentProject['id'] == $p['id']);
            ?>
            <li class="<?php echo $isActive ? 'active' : ''; ?>" onclick="window.location='compiler.php?id=<?php echo $p['id']; ?>'">
                <i class="<?php echo $icon; ?>"></i>
                <?php echo e($p['filename']); ?>
            </li>
            <?php endforeach; ?>
        </ul>

        <?php if (!empty($recentProjects)): ?>
        <span class="sidebar-section-label">Recent</span>
        <ul class="project-list">
            <?php foreach ($recentProjects as $p): 
                $icon = $langIcons[$p['language']] ?? 'fas fa-file-code';
            ?>
            <li onclick="window.location='compiler.php?id=<?php echo $p['id']; ?>'">
                <i class="<?php echo $icon; ?>"></i>
                <?php echo e($p['filename']); ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <hr class="sidebar-divider">

        <h3><i class="fas fa-graduation-cap" style="margin-right:6px;color:var(--accent);"></i> Tutorials</h3>
        <ul class="project-list tutorial-list">
            <li class="tutorial-toggle" onclick="showTutorial('python')"><i class="fab fa-python"></i> Python Tutorials</li>
            <li class="tutorial-toggle" onclick="showTutorial('java')"><i class="fab fa-java"></i> Java Tutorials</li>
        </ul>

        <hr class="sidebar-divider">

        <h3>Quick Links</h3>
        <ul class="project-list">
            <li onclick="window.location='phishing.php'"><i class="fas fa-envelope-open-text"></i> Phishing Simulator</li>
            <li onclick="window.location='checker.php'"><i class="fas fa-key"></i> Password Tester</li>
            <li onclick="window.location='blog.php'"><i class="fas fa-newspaper"></i> Latest Blogs</li>
        </ul>

        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="sidebar-user-info">
                <span><?php echo e($user['full_name']); ?></span>
                <small><?php echo e(ucfirst($user['role'] ?? 'Student')); ?></small>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN EDITOR ===== -->
    <div class="compiler-main">
        <?php if (isset($_GET['saved'])): ?>
        <div class="flash-message flash-success" style="margin:10px 20px;"><i class="fas fa-check-circle"></i> Project saved successfully!</div>
        <?php endif; ?>

        <div class="compiler-toolbar">
            <div class="compiler-toolbar-left">
                <div class="file-tab">
                    <i class="<?php echo $langIcons[$currentProject['language'] ?? 'python'] ?? 'fas fa-file-code'; ?>"></i>
                    <span id="currentFileName"><?php echo e($currentProject['filename'] ?? 'Untitled.py'); ?></span>
                </div>
                <select class="lang-select" id="languageSelect">
                    <?php foreach ($langVersions as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo (($currentProject['language'] ?? 'python') === $key) ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="compiler-toolbar-right">
                <form method="POST" action="compiler.php" id="saveForm" style="display:inline;">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="<?php echo $currentProject['id'] ?? ''; ?>">
                    <input type="hidden" name="filename" id="saveFilename" value="<?php echo e($currentProject['filename'] ?? ''); ?>">
                    <input type="hidden" name="language" id="saveLanguage" value="<?php echo e($currentProject['language'] ?? 'python'); ?>">
                    <input type="hidden" name="code" id="saveCode" value="">
                    <button type="submit" class="btn-save" onclick="document.getElementById('saveCode').value=document.getElementById('codeEditor').value;document.getElementById('saveLanguage').value=document.getElementById('languageSelect').value;">
                        <i class="fas fa-save"></i> Save
                    </button>
                </form>
                <button class="btn-share"><i class="fas fa-share-alt"></i> Share</button>
                <button class="btn-run" id="runBtn" onclick="runCode()">
                    <i class="fas fa-play"></i> Run Code
                </button>
            </div>
        </div>

        <div class="compiler-body">
            <div class="code-editor-wrap">
                <div class="code-editor">
                    <pre id="lineNumbers" class="line-numbers">1</pre>
                    <textarea id="codeEditor" spellcheck="false" placeholder="// Write your code here..." oninput="if(typeof updateLineNumbers==='function')updateLineNumbers()" onscroll="if(typeof syncScroll==='function')syncScroll()"><?php echo e($currentProject['code'] ?? '# Write your code here\nprint("Hello, World!")'); ?></textarea>
                </div>
                <div class="code-course-section" id="quickRefSection">
                    <h3><i class="fas fa-terminal" style="margin-right:8px;color:var(--accent);"></i>Quick Reference: <?php echo ucfirst($currentProject['language'] ?? 'Python'); ?></h3>
                    <div class="quick-ref-list">
                        <?php
                        $lang = $currentProject['language'] ?? 'python';
                        $stmtQR = $pdo->prepare("SELECT qr.command, qr.description FROM quick_refs qr JOIN languages l ON qr.language_id = l.id WHERE l.slug = :lang ORDER BY qr.display_order ASC");
                        $stmtQR->execute([':lang' => $lang]);
                        $cmds = $stmtQR->fetchAll();
                        foreach ($cmds as $qr): $cmd = $qr['command']; $desc = $qr['description']; ?>
                        <div class="quick-ref-item">
                            <code><?php echo $cmd; ?></code>
                            <span><?php echo e($desc); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tutorial Panel (hidden by default, shown when clicking sidebar tutorials) -->
                <div class="code-course-section tutorial-panel" id="tutorialPanel" style="display:none;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                        <h3 id="tutorialTitle" style="margin:0;"><i class="fas fa-graduation-cap" style="margin-right:8px;color:var(--accent);"></i>Python Tutorials</h3>
                        <button onclick="closeTutorial()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.1rem;"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="tutorial-lessons" id="tutorialLessons">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
            <div class="output-panel">
                <div class="output-tabs">
                    <span class="output-tab active" data-tab="output">Output</span>
                    <span class="output-tab" data-tab="console">Console</span>
                    <span class="output-tab" data-tab="errors">Errors</span>
                    <span class="output-close"><i class="fas fa-times"></i></span>
                </div>
                <div class="output-content" id="outputContent">
                    <div class="output-panel-tab active" id="outputPanel">
                        <div id="outputArea" style="font-family:'Fira Code',monospace;padding:15px;">Click "Run Code" to execute...</div>
                    </div>
                    <div class="output-panel-tab" id="consolePanel" style="display:none;">
                        <div id="consoleArea" style="font-family:'Fira Code',monospace;padding:15px;color:#6c757d;">No console messages.</div>
                    </div>
                    <div class="output-panel-tab" id="errorsPanel" style="display:none;">
                        <div id="errorsArea" style="font-family:'Fira Code',monospace;padding:15px;color:#27ae60;">No errors detected.</div>
                    </div>
                    <p class="exec-time" style="padding:0 15px;font-size:0.8rem;color:var(--text-muted);">Exec time: <span id="execTime">0.00s</span></p>
                </div>
                <div class="memory-usage">
                    <div class="memory-bar">
                        <span>Memory Usage</span>
                        <span id="memoryText">0% used</span>
                    </div>
                    <div class="memory-bar-track"><div class="memory-bar-fill" id="memoryBar" style="width:0%"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Project Modal -->
<div id="newProjectModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--white);border-radius:var(--border-radius);padding:30px;width:400px;max-width:90%;">
        <h3 style="margin-bottom:20px;">Create New Project</h3>
        <form method="POST" action="compiler.php">
            <input type="hidden" name="action" value="create">
            <div style="margin-bottom:15px;">
                <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:6px;">Filename</label>
                <input type="text" name="filename" placeholder="e.g. MyApp.py" required style="width:100%;padding:10px;border:1px solid var(--light-gray);border-radius:6px;font-family:'Inter',sans-serif;">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:6px;">Language</label>
                <select name="language" style="width:100%;padding:10px;border:1px solid var(--light-gray);border-radius:6px;font-family:'Inter',sans-serif;">
                    <option value="python">Python</option>
                    <option value="java">Java</option>
                </select>
            </div>
            <input type="hidden" name="code" value="# New project">
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('newProjectModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Create</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete form (hidden) -->
<form id="deleteForm" method="POST" action="compiler.php" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId" value="">
</form>

<?php $excludePage = 'compiler'; include __DIR__ . '/../includes/recommended.php'; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
