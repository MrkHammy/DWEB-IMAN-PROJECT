<?php
/**
 * Fox Lab – Forgot Password
 * Generates a password-reset token and displays a reset link.
 * In production this link would be sent via email; here it is
 * shown on-screen so the feature can be demonstrated locally.
 */
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();
$errors  = [];
$success = '';
$resetLink = '';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// ===== CREATE TABLE IF NOT EXISTS =====
$pdo->exec("
    CREATE TABLE IF NOT EXISTS password_resets (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        user_id    INT NOT NULL,
        token      VARCHAR(64) NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        used       TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB
");

// ===== HANDLE FORM SUBMISSION =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        // Look up user
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Invalidate any previous unused tokens for this user
            $pdo->prepare("UPDATE password_resets SET used = 1 WHERE user_id = :uid AND used = 0")
                ->execute([':uid' => $user['id']]);

            // Generate a secure token (valid for 1 hour)
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:uid, :token, :exp)");
            $stmt->execute([
                ':uid'   => $user['id'],
                ':token' => $token,
                ':exp'   => $expires,
            ]);

            // Build the reset link (would normally be emailed)
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $dir  = dirname($_SERVER['SCRIPT_NAME']);
            $resetLink = $protocol . '://' . $host . $dir . '/reset-password.php?token=' . $token;

            $success = 'A password reset link has been generated for <strong>' . e($user['full_name']) . '</strong>. In a production environment this would be sent to your email. For demonstration purposes, the link is shown below.';
        } else {
            // Don't reveal whether the email exists (security best practice)
            $success = 'If an account with that email exists, a password reset link has been generated. Check below for the link.';
        }
    }
}

$pageTitle = 'Forgot Password';
include __DIR__ . '/../includes/header.php';
?>

<section class="auth-page">
    <div class="auth-container" style="max-width: 520px;">
        <div class="auth-card">
            <div class="auth-header">
                <img src="../IMGS/LOGO/logo.png" alt="Fox Lab" class="auth-logo" onerror="this.style.display='none'">
                <h1>Reset Password</h1>
                <p>Enter your email to receive a password reset link</p>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo e(implode(' ', $errors)); ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="flash-message flash-success" style="opacity:1;transform:none;">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>

                <?php if ($resetLink): ?>
                <div class="reset-link-box">
                    <p class="reset-link-label"><i class="fas fa-link"></i> Your Reset Link (demo only):</p>
                    <a href="<?php echo e($resetLink); ?>" class="reset-link-url"><?php echo e($resetLink); ?></a>
                    <p class="reset-link-note"><i class="fas fa-clock"></i> This link expires in 1 hour.</p>
                </div>
                <?php endif; ?>

            <?php else: ?>
            <form method="POST" action="forgot-password.php" class="auth-form">
                <div class="form-group">
                    <label for="resetEmail"><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" id="resetEmail" placeholder="Enter your registered email" required
                           value="<?php echo e($_POST['email'] ?? ''); ?>" autofocus>
                </div>

                <button type="submit" class="btn btn-primary btn-auth">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
                </button>
            </form>
            <?php endif; ?>

            <p class="auth-switch">
                <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
