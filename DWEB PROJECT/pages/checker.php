<?php
/**
 * Fox Lab â€“ Password Security Tester
 * Real-time JS strength meter + backend logging (requires login)
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Password Tester';
$pdo = getDBConnection();

// Require login
requireLogin();
$user = currentUser();
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle AJAX log submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'log_strength') {
    header('Content-Type: application/json');
    
    $stmt = $pdo->prepare("INSERT INTO pw_logs (user_id, strength_level, char_count, has_uppercase, has_lowercase, has_numbers, has_symbols, is_compromised, ip_address) VALUES (:uid, :sl, :cc, :hu, :hl, :hn, :hs, :ic, :ip)");
    $stmt->execute([
        ':uid' => $user['id'],
        ':sl' => $_POST['strength_level'] ?? 'Unknown',
        ':cc' => (int)($_POST['char_count'] ?? 0),
        ':hu' => (int)($_POST['has_uppercase'] ?? 0),
        ':hl' => (int)($_POST['has_lowercase'] ?? 0),
        ':hn' => (int)($_POST['has_numbers'] ?? 0),
        ':hs' => (int)($_POST['has_symbols'] ?? 0),
        ':ic' => (int)($_POST['is_compromised'] ?? 0),
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ]);
    
    echo json_encode(['success' => true]);
    exit;
}

$extraScripts = ['checker.js'];
include __DIR__ . '/../includes/header.php';
?>

<!-- ===== PASSWORD CHECKER ===== -->
<section class="checker-page">
    <div class="container">
        <div class="checker-header">
            <h1>Password Security Tester</h1>
            <p>Test the strength of your password and get instant feedback on security criteria. Ensure your passwords meet industry standards for maximum protection.</p>
        </div>

        <div class="checker-layout">
            <!-- Left: Input Panel -->
            <div class="checker-card">
                <div class="checker-card-header">
                    <h2>Test Your Password</h2>
                    <i class="fas fa-key"></i>
                </div>

                <div class="password-input-wrap">
                    <label for="passwordInput">Enter Password</label>
                    <input type="password" id="passwordInput" placeholder="Type your password here..." autocomplete="off">
                    <button class="toggle-password" type="button" onclick="togglePassword()" aria-label="Toggle password visibility">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>

                <div class="password-notice">
                    <i class="fas fa-info-circle"></i>
                    <span>Your password is never stored or transmitted. All testing happens locally in your browser.</span>
                </div>

                <button class="btn-analyze" id="analyzeBtn" onclick="analyzePassword()">
                    <i class="fas fa-search"></i> Analyze Password Strength
                </button>

            </div>

            <!-- Right: Analysis Panel -->
            <div class="checker-card">
                <div class="checker-card-header">
                    <h2>Security Analysis</h2>
                    <i class="fas fa-chart-bar"></i>
                </div>

                <div class="strength-overview">
                    <span>Overall Strength</span>
                    <span class="strength-status" id="strengthStatus">Waiting for Input...</span>
                </div>

                <!-- Strength Bar -->
                <div class="progress-bar" style="margin-bottom:20px;height:10px;">
                    <div class="progress-fill" id="strengthBar" style="width:0%;transition:width 0.5s ease,background 0.5s ease;"></div>
                </div>

                <h3 class="criteria-title">Security Criteria</h3>

                <ul class="criteria-list">
                    <li class="criteria-item">
                        <span class="criteria-dot" id="dot-length"></span>
                        <span class="criteria-text">Minimum 8 characters</span>
                        <span class="criteria-status" id="status-length">PENDING</span>
                    </li>
                    <li class="criteria-item">
                        <span class="criteria-dot" id="dot-upper"></span>
                        <span class="criteria-text">Contains uppercase letters</span>
                        <span class="criteria-status" id="status-upper">PENDING</span>
                    </li>
                    <li class="criteria-item">
                        <span class="criteria-dot" id="dot-lower"></span>
                        <span class="criteria-text">Contains lowercase letters</span>
                        <span class="criteria-status" id="status-lower">PENDING</span>
                    </li>
                    <li class="criteria-item">
                        <span class="criteria-dot" id="dot-number"></span>
                        <span class="criteria-text">Contains numbers</span>
                        <span class="criteria-status" id="status-number">PENDING</span>
                    </li>
                    <li class="criteria-item">
                        <span class="criteria-dot" id="dot-symbol"></span>
                        <span class="criteria-text">Contains special symbols</span>
                        <span class="criteria-status" id="status-symbol">PENDING</span>
                    </li>
                </ul>

                <div class="compromise-check">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="check-text">
                        <strong>Compromise Check</strong>
                        <small id="compromiseStatus">Checking against known compromised password databases...</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== BEST PRACTICES ===== -->
        <section class="best-practices">
            <div class="best-practices-header">
                <h2>Password Security Best Practices</h2>
                <i class="fas fa-lightbulb" style="color:var(--warning);font-size:1.5rem;"></i>
            </div>
            <div class="practices-grid">
                <div class="practice-card">
                    <div class="practice-icon">
                        <i class="fas fa-ruler-horizontal"></i>
                        <h4>Length Matters</h4>
                    </div>
                    <p>Use at least 12-16 characters. Longer passwords are exponentially harder to crack than shorter ones.</p>
                </div>
                <div class="practice-card">
                    <div class="practice-icon">
                        <i class="fas fa-font"></i>
                        <h4>Mix Character Types</h4>
                    </div>
                    <p>Combine uppercase, lowercase, numbers, and special characters for maximum complexity.</p>
                </div>
                <div class="practice-card">
                    <div class="practice-icon">
                        <i class="fas fa-ban"></i>
                        <h4>Avoid Common Patterns</h4>
                    </div>
                    <p>Don't use dictionary words, personal information, or predictable patterns like "123" or "abc".</p>
                </div>
                <div class="practice-card">
                    <div class="practice-icon">
                        <i class="fas fa-fingerprint"></i>
                        <h4>Unique for Each Account</h4>
                    </div>
                    <p>Never reuse passwords across multiple accounts. Each service should have its own unique password.</p>
                </div>
                <div class="practice-card">
                    <div class="practice-icon">
                        <i class="fas fa-vault"></i>
                        <h4>Use a Password Manager</h4>
                    </div>
                    <p>Let a password manager generate and store complex passwords for you. It's the safest approach.</p>
                </div>
                <div class="practice-card">
                    <div class="practice-icon">
                        <i class="fas fa-mobile-alt"></i>
                        <h4>Enable 2FA</h4>
                    </div>
                    <p>Add two-factor authentication whenever possible for an extra layer of security beyond passwords.</p>
                </div>
            </div>
        </section>
    </div>
</section>

<?php $excludePage = 'checker'; include __DIR__ . '/../includes/recommended.php'; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
