<?php
/**
 * Fox Lab â€“ Phishing Email Simulator
 * Interactive phishing detection training (requires login)
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Phishing Simulations';
$pdo = getDBConnection();

// Require login for phishing simulation
requireLogin();
$user = currentUser();
if (!$user) {
    // Session references a user that no longer exists in the DB â€“ force re-login
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle AJAX response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_response') {
    header('Content-Type: application/json');
    
    $scenarioId = (int)($_POST['scenario_id'] ?? 0);
    $userResponse = $_POST['user_response'] ?? '';
    
    // Get scenario info
    $stmtCheck = $pdo->prepare("SELECT is_phishing FROM scenarios WHERE id = :id");
    $stmtCheck->execute([':id' => $scenarioId]);
    $scenario = $stmtCheck->fetch();
    
    if (!$scenario) {
        echo json_encode(['error' => 'Scenario not found']);
        exit;
    }
    
    $isCorrect = false;
    $message = '';
    if ($userResponse === 'phishing' && $scenario['is_phishing'] == 1) {
        $isCorrect = true;
        $message = 'Correct! This is indeed a phishing email. You successfully identified the threat.';
    } elseif ($userResponse === 'legitimate' && $scenario['is_phishing'] == 0) {
        $isCorrect = true;
        $message = 'Correct! This is a legitimate email. Good job recognizing safe communication.';
    } elseif ($userResponse === 'phishing' && $scenario['is_phishing'] == 0) {
        $message = 'Incorrect. This was actually a legitimate email. Not every email is a threat.';
    } else {
        $message = 'Incorrect. This was a phishing email! Review the red flags below to learn what to look for.';
    }
    
    // Save progress
    $stmtSave = $pdo->prepare("INSERT INTO quiz_results (user_id, scenario_id, user_response, is_correct) VALUES (:uid, :sid, :resp, :correct)");
    $stmtSave->execute([
        ':uid' => $_SESSION['user_id'],
        ':sid' => $scenarioId,
        ':resp' => $userResponse,
        ':correct' => $isCorrect ? 1 : 0
    ]);
    
    // Get red flags for this scenario
    $stmtRF = $pdo->prepare("SELECT flag_title, flag_description, flag_icon FROM red_flags WHERE scenario_id = :sid");
    $stmtRF->execute([':sid' => $scenarioId]);
    $flags = $stmtRF->fetchAll();
    
    // Get session totals
    $stmtCorrect = $pdo->prepare("SELECT 
        SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_count,
        SUM(CASE WHEN is_correct = 0 THEN 1 ELSE 0 END) as incorrect_count
        FROM quiz_results WHERE user_id = :uid");
    $stmtCorrect->execute([':uid' => $_SESSION['user_id']]);
    $counts = $stmtCorrect->fetch();
    
    echo json_encode([
        'correct' => $isCorrect,
        'message' => $message,
        'is_phishing' => (bool)$scenario['is_phishing'],
        'red_flags' => $flags,
        'session_correct' => (int)($counts['correct_count'] ?? 0),
        'session_incorrect' => (int)($counts['incorrect_count'] ?? 0)
    ]);
    exit;
}

// Get all scenarios
$stmtScenarios = $pdo->query("SELECT * FROM scenarios ORDER BY id ASC");
$allScenarios = $stmtScenarios->fetchAll();

// â”€â”€ Session-based quiz: pick 4 random scenarios per session â”€â”€
if (!isset($_SESSION['phishing_quiz_ids']) || isset($_GET['restart'])) {
    // Shuffle and pick 4 (or fewer if not enough scenarios)
    $ids = array_column($allScenarios, 'id');
    shuffle($ids);
    $_SESSION['phishing_quiz_ids'] = array_slice($ids, 0, min(4, count($ids)));
    $_SESSION['phishing_quiz_pos'] = 0;
}

$quizIds = $_SESSION['phishing_quiz_ids'];
$quizPos = $_SESSION['phishing_quiz_pos'] ?? 0;
$totalScenarios = count($quizIds);

// Build ordered scenario list for this quiz session
$scenarios = [];
foreach ($quizIds as $qid) {
    foreach ($allScenarios as $s) {
        if ((int)$s['id'] === (int)$qid) { $scenarios[] = $s; break; }
    }
}

// Current scenario (0-based index from URL)
$currentIndex = isset($_GET['scenario']) ? max(0, (int)$_GET['scenario']) : $quizPos;
if ($currentIndex >= $totalScenarios) $currentIndex = 0;
$currentScenario = $scenarios[$currentIndex] ?? null;

// Get indicators for current scenario
$indicators = [];
if ($currentScenario) {
    $stmtInd = $pdo->prepare("SELECT * FROM indicators WHERE scenario_id = :sid ORDER BY id ASC");
    $stmtInd->execute([':sid' => $currentScenario['id']]);
    $indicators = $stmtInd->fetchAll();
}

// Get user session stats
$stmtUserStats = $pdo->prepare("SELECT 
    SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_count,
    SUM(CASE WHEN is_correct = 0 THEN 1 ELSE 0 END) as incorrect_count
    FROM quiz_results WHERE user_id = :uid");
$stmtUserStats->execute([':uid' => $_SESSION['user_id']]);
$userStats = $stmtUserStats->fetch();
$sessionCorrect = (int)($userStats['correct_count'] ?? 0);
$sessionIncorrect = (int)($userStats['incorrect_count'] ?? 0);

$extraScripts = ['phishing.js'];
include __DIR__ . '/../includes/header.php';
?>

<?php if (!$currentScenario): ?>
<section style="padding:60px 0;text-align:center;">
    <div class="container">
        <h2>No phishing scenarios available.</h2>
        <p>Please check the database for scenarios entries.</p>
    </div>
</section>
<?php else: ?>

<!-- ===== PHISHING SIMULATOR ===== -->
<section class="phishing-page">
    <div class="container">
        <div class="phishing-header">
            <div class="phishing-header-left">
                <h1>Phishing Email Simulator</h1>
                <p>Test your ability to identify phishing attempts in realistic email scenarios</p>
            </div>
            <div class="phishing-header-right">
                <div class="session-mode">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo e($user['full_name']); ?></span> | Training Mode
                </div>
                <button class="btn-start-test" onclick="skipScenario()">
                    <i class="fas fa-forward"></i> Skip Scenario
                </button>
            </div>
        </div>

        <div class="phishing-layout">
            <!-- ===== EMAIL SIMULATION ===== -->
            <div>
                <div class="email-simulation">
                    <div class="email-sim-header">
                        <i class="fas fa-envelope"></i> Email Simulation
                    </div>
                    <div class="email-meta-table">
                        <table>
                            <tr><td>From:</td><td><?php echo e($currentScenario['sender_email']); ?></td></tr>
                            <tr><td>To:</td><td><?php echo e($currentScenario['recipient_email']); ?></td></tr>
                            <tr><td>Subject:</td><td><?php echo e($currentScenario['subject']); ?></td></tr>
                            <tr><td>Date:</td><td><?php echo date('F d, Y, g:i A', strtotime($currentScenario['email_date'])); ?></td></tr>
                        </table>
                    </div>
                    <div class="email-body">
                        <?php echo $currentScenario['body_html']; ?>
                    </div>
                    <?php if ($currentScenario['cta_text']): ?>
                    <div class="email-cta">
                        <a href="#" class="btn" onclick="event.preventDefault();"><?php echo e($currentScenario['cta_text']); ?></a>
                    </div>
                    <?php endif; ?>
                    <div class="email-disclaimer">
                        This verification must be completed within 24 hours to avoid permanent account suspension.
                    </div>
                    <div class="email-sender-info">
                        <?php echo e($currentScenario['sender_name'] ?? ''); ?><br>
                        Email: <?php echo e($currentScenario['sender_email']); ?><br>
                        Reference ID: RF-012-2025-0315-7894
                    </div>
                </div>

                <!-- Analysis Section (hidden until response submitted) -->
                <div class="analysis-section" id="analysisSection" style="display:none;">
                    <div class="analysis-result" style="text-align:center;margin-bottom:20px;">
                        <div id="resultIcon"></div>
                        <h2 id="resultTitle" style="margin:10px 0;"></h2>
                        <p id="resultMessage" style="color:var(--text-muted);"></p>
                    </div>
                    <h3 id="redFlagsHeading">Detected Red Flags</h3>
                    <ul class="red-flag-list" id="redFlagsList">
                        <!-- Populated by JS -->
                    </ul>
                    
                    <!-- Indicator Accuracy (how well user identified indicators) -->
                    <div id="indicatorAccuracy" style="margin-top:20px;padding:16px;background:var(--off-white);border-radius:8px;display:none;">
                        <h4 style="margin-bottom:8px;font-size:0.95rem;"><i class="fas fa-clipboard-check" style="color:var(--accent);margin-right:6px;"></i>Indicator Check Accuracy</h4>
                        <p id="indicatorAccuracyText" style="font-size:0.88rem;color:var(--text-secondary);"></p>
                    </div>
                    
                    <!-- Navigation buttons after answering -->
                    <div class="analysis-actions" style="margin-top:24px;display:flex;gap:12px;justify-content:center;">
                        <button class="btn btn-primary" id="nextScenarioBtn" onclick="goToNextScenario()" style="display:none;">
                            <i class="fas fa-arrow-right"></i> Next Scenario
                        </button>
                        <button class="btn btn-outline" id="restartBtn" onclick="window.location.href='phishing.php?restart=1'" style="display:none;">
                            <i class="fas fa-redo"></i> Take Another Quiz
                        </button>
                    </div>
                </div>
                
                <!-- Final Results (shown after all scenarios) -->
                <div class="analysis-section" id="finalResults" style="display:none;">
                    <div style="text-align:center;padding:30px 0;">
                        <i class="fas fa-trophy" style="font-size:3rem;color:var(--warning);margin-bottom:16px;display:block;"></i>
                        <h2 style="margin-bottom:8px;">Training Complete!</h2>
                        <p style="color:var(--text-muted);margin-bottom:20px;">You've completed all available phishing scenarios.</p>
                        <div style="display:flex;gap:30px;justify-content:center;margin-bottom:24px;">
                            <div style="text-align:center;">
                                <span id="finalCorrect" style="font-size:2rem;font-weight:700;color:var(--success);display:block;">0</span>
                                <span style="font-size:0.85rem;color:var(--text-muted);">Correct</span>
                            </div>
                            <div style="text-align:center;">
                                <span id="finalIncorrect" style="font-size:2rem;font-weight:700;color:var(--danger);display:block;">0</span>
                                <span style="font-size:0.85rem;color:var(--text-muted);">Incorrect</span>
                            </div>
                            <div style="text-align:center;">
                                <span id="finalAccuracy" style="font-size:2rem;font-weight:700;color:var(--accent);display:block;">0%</span>
                                <span style="font-size:0.85rem;color:var(--text-muted);">Accuracy</span>
                            </div>
                        </div>
                        <a href="phishing.php?restart=1" class="btn btn-primary"><i class="fas fa-redo"></i> Take Another Quiz</a>
                    </div>
                </div>
            </div>

            <!-- ===== RIGHT PANELS ===== -->
            <div class="phishing-panels">
                <!-- Indicators -->
                <div class="phishing-panel">
                    <h3>Phishing Indicators</h3>
                    <ul class="indicator-list">
                        <?php foreach ($indicators as $ind): ?>
                        <li class="indicator-item">
                            <input type="checkbox" class="indicator-checkbox" id="ind_<?php echo $ind['id']; ?>" 
                                   value="<?php echo e($ind['indicator_text']); ?>" data-correct="<?php echo $ind['is_correct']; ?>">
                            <label for="ind_<?php echo $ind['id']; ?>"><?php echo e($ind['indicator_text']); ?></label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <small id="indicatorCount" style="display:block;margin-top:10px;color:var(--text-muted);">0 of <?php echo count($indicators); ?> indicators checked</small>
                </div>

                <!-- Your Response -->
                <div class="phishing-panel">
                    <h3>Your Response</h3>
                    <button class="btn-report btn-report-phishing phishing-action-btn" onclick="submitResponse('phishing', <?php echo $currentScenario['id']; ?>)">
                        <i class="fas fa-exclamation-triangle"></i> Report as Phishing
                    </button>
                    <button class="btn-report btn-report-legit phishing-action-btn" onclick="submitResponse('legitimate', <?php echo $currentScenario['id']; ?>)">
                        <i class="fas fa-check"></i> Mark as Legitimate
                    </button>
                </div>

                <!-- Session Progress -->
                <div class="phishing-panel">
                    <h3>Session Progress</h3>
                    <div class="session-progress">
                        <div class="progress-text">
                            <span>Email <?php echo $currentIndex + 1; ?> of <?php echo $totalScenarios; ?></span>
                            <span><?php echo round((($currentIndex + 1) / $totalScenarios) * 100); ?>% Complete</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:<?php echo round((($currentIndex + 1) / $totalScenarios) * 100); ?>%"></div>
                        </div>
                        <table class="score-table">
                            <tr><td>Correct:</td><td id="correctCount"><?php echo $sessionCorrect; ?></td></tr>
                            <tr><td>Incorrect:</td><td id="incorrectCount"><?php echo $sessionIncorrect; ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hidden fields for JS -->
<input type="hidden" id="totalScenarios" value="<?php echo $totalScenarios; ?>">
<input type="hidden" id="currentScenarioIndex" value="<?php echo $currentIndex; ?>">

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
