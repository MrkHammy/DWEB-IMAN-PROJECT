<?php
/**
 * Fox Lab – Admin Dashboard
 * View user stats, platform activity, and system overview
 */
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$pageTitle = 'Admin Dashboard';
$pdo = getDBConnection();

// ===== PLATFORM OVERVIEW STATS =====
$totalUsers     = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalStudents  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalAdmins    = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$newUsersWeek   = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$newUsersMonth  = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

$totalBlogs     = $pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
$totalBlogViews = $pdo->query("SELECT COALESCE(SUM(views), 0) FROM blogs")->fetchColumn();
$featuredBlogs  = $pdo->query("SELECT COUNT(*) FROM blogs WHERE is_featured = 1")->fetchColumn();

$totalQuizzes   = $pdo->query("SELECT COUNT(*) FROM quiz_results")->fetchColumn();
$totalPwChecks  = $pdo->query("SELECT COUNT(*) FROM pw_logs")->fetchColumn();
$totalBookmarks = $pdo->query("SELECT COUNT(*) FROM bookmarks")->fetchColumn();
$totalProjects  = $pdo->query("SELECT COUNT(*) FROM projects WHERE user_id IS NOT NULL")->fetchColumn();
$totalEnrolled  = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();

$totalTerms     = $pdo->query("SELECT COUNT(*) FROM terms")->fetchColumn();
$totalScenarios = $pdo->query("SELECT COUNT(*) FROM scenarios")->fetchColumn();
$totalCourses   = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();

// ===== PER-USER ACTIVITY =====
$users = $pdo->query("
    SELECT 
        u.id,
        u.full_name,
        u.email,
        u.role,
        u.avatar_url,
        u.created_at,
        COALESCE(qr.quiz_count, 0)       AS quiz_count,
        COALESCE(qr.correct_total, 0)    AS correct_total,
        COALESCE(qr.incorrect_total, 0)  AS incorrect_total,
        COALESCE(pw.pw_checks, 0)        AS pw_checks,
        COALESCE(bk.bookmark_count, 0)   AS bookmark_count,
        COALESCE(pj.project_count, 0)    AS project_count,
        COALESCE(en.enrollment_count, 0) AS enrollment_count,
        COALESCE(en.avg_progress, 0)     AS avg_progress,
        COALESCE(bl.blog_count, 0)       AS blog_count
    FROM users u
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS quiz_count, 
               SUM(is_correct) AS correct_total, 
               SUM(CASE WHEN is_correct = 0 THEN 1 ELSE 0 END) AS incorrect_total
        FROM quiz_results GROUP BY user_id
    ) qr ON u.id = qr.user_id
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS pw_checks 
        FROM pw_logs GROUP BY user_id
    ) pw ON u.id = pw.user_id
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS bookmark_count 
        FROM bookmarks GROUP BY user_id
    ) bk ON u.id = bk.user_id
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS project_count 
        FROM projects WHERE user_id IS NOT NULL GROUP BY user_id
    ) pj ON u.id = pj.user_id
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS enrollment_count, ROUND(AVG(progress)) AS avg_progress 
        FROM enrollments GROUP BY user_id
    ) en ON u.id = en.user_id
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS blog_count 
        FROM blogs WHERE user_id IS NOT NULL GROUP BY user_id
    ) bl ON u.id = bl.user_id
    ORDER BY u.created_at DESC
")->fetchAll();

// ===== RECENT ACTIVITY =====
$recentQuizzes = $pdo->query("
    SELECT qr.*, u.full_name, s.subject AS scenario_title
    FROM quiz_results qr
    JOIN users u ON qr.user_id = u.id
    JOIN scenarios s ON qr.scenario_id = s.id
    ORDER BY qr.answered_at DESC
    LIMIT 10
")->fetchAll();

$recentPwChecks = $pdo->query("
    SELECT pl.*, u.full_name
    FROM pw_logs pl
    LEFT JOIN users u ON pl.user_id = u.id
    ORDER BY pl.checked_at DESC
    LIMIT 10
")->fetchAll();

// ===== STRENGTH DISTRIBUTION =====
$strengthDist = $pdo->query("
    SELECT strength_level, COUNT(*) AS count 
    FROM pw_logs 
    GROUP BY strength_level 
    ORDER BY count DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- ===== ADMIN DASHBOARD ===== -->
<section class="admin-dashboard">
    <div class="container">

        <!-- Header -->
        <div class="admin-header">
            <div class="admin-header-left">
                <h1><i class="fas fa-chart-line"></i> Admin Dashboard</h1>
                <p>Platform overview and user activity stats</p>
            </div>
            <div class="admin-header-actions">
                <a href="admin-blogs.php" class="admin-btn admin-btn-outline"><i class="fas fa-pen-to-square"></i> Manage Blogs</a>
            </div>
        </div>

        <!-- ===== OVERVIEW STAT CARDS ===== -->
        <div class="dash-section">
            <h2 class="dash-section-title"><i class="fas fa-users"></i> Platform Overview</h2>
            <div class="dash-stats-grid">
                <div class="dash-stat-card dash-stat-blue">
                    <div class="dash-stat-icon"><i class="fas fa-users"></i></div>
                    <div class="dash-stat-info">
                        <span class="dash-stat-value"><?php echo $totalUsers; ?></span>
                        <span class="dash-stat-label">Total Users</span>
                    </div>
                    <div class="dash-stat-detail">
                        <?php echo $totalStudents; ?> students · <?php echo $totalAdmins; ?> admins
                    </div>
                </div>
                <div class="dash-stat-card dash-stat-green">
                    <div class="dash-stat-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="dash-stat-info">
                        <span class="dash-stat-value"><?php echo $newUsersWeek; ?></span>
                        <span class="dash-stat-label">New This Week</span>
                    </div>
                    <div class="dash-stat-detail">
                        <?php echo $newUsersMonth; ?> this month
                    </div>
                </div>
                <div class="dash-stat-card dash-stat-purple">
                    <div class="dash-stat-icon"><i class="fas fa-shield-halved"></i></div>
                    <div class="dash-stat-info">
                        <span class="dash-stat-value"><?php echo $totalQuizzes; ?></span>
                        <span class="dash-stat-label">Quiz Attempts</span>
                    </div>
                    <div class="dash-stat-detail">
                        Across <?php echo $totalScenarios; ?> phishing scenarios
                    </div>
                </div>
                <div class="dash-stat-card dash-stat-orange">
                    <div class="dash-stat-icon"><i class="fas fa-key"></i></div>
                    <div class="dash-stat-info">
                        <span class="dash-stat-value"><?php echo $totalPwChecks; ?></span>
                        <span class="dash-stat-label">Password Checks</span>
                    </div>
                    <div class="dash-stat-detail">
                        Security strength tests
                    </div>
                </div>
                <div class="dash-stat-card dash-stat-cyan">
                    <div class="dash-stat-icon"><i class="fas fa-bookmark"></i></div>
                    <div class="dash-stat-info">
                        <span class="dash-stat-value"><?php echo $totalBookmarks; ?></span>
                        <span class="dash-stat-label">Term Bookmarks</span>
                    </div>
                    <div class="dash-stat-detail">
                        From <?php echo $totalTerms; ?> glossary terms
                    </div>
                </div>
                <div class="dash-stat-card dash-stat-pink">
                    <div class="dash-stat-icon"><i class="fas fa-code"></i></div>
                    <div class="dash-stat-info">
                        <span class="dash-stat-value"><?php echo $totalProjects; ?></span>
                        <span class="dash-stat-label">User Projects</span>
                    </div>
                    <div class="dash-stat-detail">
                        Code compiler projects
                    </div>
                </div>
                <div class="dash-stat-card dash-stat-blue">
                    <div class="dash-stat-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div class="dash-stat-info">
                        <span class="dash-stat-value"><?php echo $totalEnrolled; ?></span>
                        <span class="dash-stat-label">Enrollments</span>
                    </div>
                    <div class="dash-stat-detail">
                        Across <?php echo $totalCourses; ?> courses
                    </div>
                </div>
                <div class="dash-stat-card dash-stat-green">
                    <div class="dash-stat-icon"><i class="fas fa-newspaper"></i></div>
                    <div class="dash-stat-info">
                        <span class="dash-stat-value"><?php echo $totalBlogs; ?></span>
                        <span class="dash-stat-label">Blog Posts</span>
                    </div>
                    <div class="dash-stat-detail">
                        <?php echo number_format($totalBlogViews); ?> views · <?php echo $featuredBlogs; ?> featured
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== USER ACTIVITY TABLE ===== -->
        <div class="dash-section">
            <h2 class="dash-section-title"><i class="fas fa-table"></i> User Activity Breakdown</h2>
            <div class="dash-table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th><i class="fas fa-shield-halved"></i> Quizzes</th>
                            <th><i class="fas fa-key"></i> PW Checks</th>
                            <th><i class="fas fa-bookmark"></i> Bookmarks</th>
                            <th><i class="fas fa-code"></i> Projects</th>
                            <th><i class="fas fa-graduation-cap"></i> Courses</th>
                            <th><i class="fas fa-newspaper"></i> Blogs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="9" class="dash-table-empty">No users found</td></tr>
                        <?php else: ?>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="dash-user-cell">
                                <div class="dash-user-avatar">
                                    <?php if (!empty($u['avatar_url'])): ?>
                                        <img src="../<?php echo e($u['avatar_url']); ?>" alt="">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="dash-user-info">
                                    <span class="dash-user-name"><?php echo e($u['full_name']); ?></span>
                                    <span class="dash-user-email"><?php echo e($u['email']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="dash-role-badge dash-role-<?php echo e($u['role']); ?>">
                                    <?php echo ucfirst(e($u['role'])); ?>
                                </span>
                            </td>
                            <td class="dash-date"><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <?php if ($u['quiz_count'] > 0): ?>
                                    <span class="dash-activity-count"><?php echo $u['quiz_count']; ?></span>
                                    <span class="dash-activity-sub"><?php echo $u['correct_total']; ?>✓ <?php echo $u['incorrect_total']; ?>✗</span>
                                <?php else: ?>
                                    <span class="dash-activity-none">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['pw_checks'] > 0): ?>
                                    <span class="dash-activity-count"><?php echo $u['pw_checks']; ?></span>
                                <?php else: ?>
                                    <span class="dash-activity-none">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['bookmark_count'] > 0): ?>
                                    <span class="dash-activity-count"><?php echo $u['bookmark_count']; ?></span>
                                <?php else: ?>
                                    <span class="dash-activity-none">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['project_count'] > 0): ?>
                                    <span class="dash-activity-count"><?php echo $u['project_count']; ?></span>
                                <?php else: ?>
                                    <span class="dash-activity-none">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['enrollment_count'] > 0): ?>
                                    <span class="dash-activity-count"><?php echo $u['enrollment_count']; ?></span>
                                    <span class="dash-activity-sub"><?php echo $u['avg_progress']; ?>% avg</span>
                                <?php else: ?>
                                    <span class="dash-activity-none">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['blog_count'] > 0): ?>
                                    <span class="dash-activity-count"><?php echo $u['blog_count']; ?></span>
                                <?php else: ?>
                                    <span class="dash-activity-none">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== RECENT ACTIVITY PANELS ===== -->
        <div class="dash-two-col">

            <!-- Recent Quiz Results -->
            <div class="dash-section">
                <h2 class="dash-section-title"><i class="fas fa-shield-halved"></i> Recent Quiz Results</h2>
                <?php if (empty($recentQuizzes)): ?>
                    <p class="dash-empty-msg">No quiz results yet.</p>
                <?php else: ?>
                <div class="dash-activity-list">
                    <?php foreach ($recentQuizzes as $q): ?>
                    <div class="dash-activity-item">
                        <div class="dash-activity-icon <?php echo $q['is_correct'] ? 'dash-icon-success' : 'dash-icon-danger'; ?>">
                            <i class="fas fa-<?php echo $q['is_correct'] ? 'check' : 'xmark'; ?>"></i>
                        </div>
                        <div class="dash-activity-body">
                            <strong><?php echo e($q['full_name']); ?></strong>
                            <span><?php echo $q['is_correct'] ? 'correctly identified' : 'missed'; ?> "<?php echo e($q['scenario_title']); ?>"</span>
                            <small>Responded: <?php echo e($q['user_response']); ?> · <?php echo date('M j, g:ia', strtotime($q['answered_at'])); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Password Checks -->
            <div class="dash-section">
                <h2 class="dash-section-title"><i class="fas fa-key"></i> Recent Password Checks</h2>
                <?php if (empty($recentPwChecks)): ?>
                    <p class="dash-empty-msg">No password checks yet.</p>
                <?php else: ?>
                <div class="dash-activity-list">
                    <?php foreach ($recentPwChecks as $p): ?>
                    <div class="dash-activity-item">
                        <div class="dash-activity-icon <?php
                            $lvl = strtolower($p['strength_level']);
                            if (str_contains($lvl, 'strong') || str_contains($lvl, 'excellent')) echo 'dash-icon-success';
                            elseif (str_contains($lvl, 'medium') || str_contains($lvl, 'fair')) echo 'dash-icon-warning';
                            else echo 'dash-icon-danger';
                        ?>">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="dash-activity-body">
                            <strong><?php echo $p['full_name'] ? e($p['full_name']) : 'Guest'; ?></strong>
                            <span>
                                <?php echo e($p['strength_level']); ?> · <?php echo $p['char_count']; ?> chars
                                <?php if ($p['is_compromised']): ?><span class="dash-compromised">COMPROMISED</span><?php endif; ?>
                            </span>
                            <small>
                                <?php
                                    $flags = [];
                                    if ($p['has_uppercase']) $flags[] = 'A-Z';
                                    if ($p['has_lowercase']) $flags[] = 'a-z';
                                    if ($p['has_numbers'])   $flags[] = '0-9';
                                    if ($p['has_symbols'])   $flags[] = '!@#';
                                    echo implode(' · ', $flags);
                                ?> · <?php echo date('M j, g:ia', strtotime($p['checked_at'])); ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- ===== PASSWORD STRENGTH DISTRIBUTION ===== -->
        <?php if (!empty($strengthDist)): ?>
        <div class="dash-section">
            <h2 class="dash-section-title"><i class="fas fa-chart-bar"></i> Password Strength Distribution</h2>
            <div class="dash-bar-chart">
                <?php
                    $maxCount = max(array_column($strengthDist, 'count'));
                    foreach ($strengthDist as $s):
                        $pct = $maxCount > 0 ? round(($s['count'] / $maxCount) * 100) : 0;
                        $lvl = strtolower($s['strength_level']);
                        if (str_contains($lvl, 'strong') || str_contains($lvl, 'excellent')) $barColor = 'var(--success)';
                        elseif (str_contains($lvl, 'medium') || str_contains($lvl, 'fair')) $barColor = 'var(--warning)';
                        else $barColor = 'var(--danger)';
                ?>
                <div class="dash-bar-row">
                    <span class="dash-bar-label"><?php echo e($s['strength_level']); ?></span>
                    <div class="dash-bar-track">
                        <div class="dash-bar-fill" style="width: <?php echo $pct; ?>%; background: <?php echo $barColor; ?>;"></div>
                    </div>
                    <span class="dash-bar-value"><?php echo $s['count']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
