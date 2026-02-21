<?php
/**
 * Fox Lab – Reset Password
 * Validates the reset token and lets the user set a new password.
 */
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();
$errors  = [];
$success = '';
$tokenValid = false;
$token = $_GET['token'] ?? $_POST['token'] ?? '';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// ===== VALIDATE TOKEN =====
if (!empty($token)) {
    $stmt = $pdo->prepare("
        SELECT pr.*, u.full_name, u.email 
        FROM password_resets pr 
        JOIN users u ON u.id = pr.user_id 
        WHERE pr.token = :token AND pr.used = 0 AND pr.expires_at > NOW() 
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $reset = $stmt->fetch();

    if ($reset) {
        $tokenValid = true;
    } else {
        $errors[] = 'This reset link is invalid or has expired. Please request a new one.';
    }
} else {
    $errors[] = 'No reset token provided. Please use the link from the password reset page.';
}

// ===== HANDLE NEW PASSWORD SUBMISSION =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirmPassword)) {
        $errors[] = 'Please fill in all fields.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    } else {
        // Update the user's password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = :pass WHERE id = :uid");
        $stmt->execute([
            ':pass' => $hashedPassword,
            ':uid'  => $reset['user_id'],
        ]);

        // Mark the token as used
        $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = :id")
            ->execute([':id' => $reset['id']]);

        $success = 'Your password has been reset successfully! You can now log in with your new password.';
        $tokenValid = false; // Hide the form
    }
}

$pageTitle = 'Reset Password';
include __DIR__ . '/../includes/header.php';
?>

<section class="auth-page">
    <div class="auth-container" style="max-width: 520px;">
        <div class="auth-card">
            <div class="auth-header">
                <img src="../IMGS/LOGO/logo.png" alt="Fox Lab" class="auth-logo" onerror="this.style.display='none'">
                <h1>Set New Password</h1>
                <?php if ($tokenValid): ?>
                <p>Enter a new password for <strong><?php echo e($reset['full_name']); ?></strong></p>
                <?php else: ?>
                <p>Password Recovery</p>
                <?php endif; ?>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error" style="opacity:1;transform:none;">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo e(implode(' ', $errors)); ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="flash-message flash-success" style="opacity:1;transform:none;">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
            <div style="text-align:center; margin-top: 20px;">
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
            </div>
            <?php endif; ?>

            <?php if ($tokenValid): ?>
            <form method="POST" action="reset-password.php" class="auth-form">
                <input type="hidden" name="token" value="<?php echo e($token); ?>">

                <div class="form-group">
                    <label for="newPassword"><i class="fas fa-lock"></i> New Password</label>
                    <div class="password-field">
                        <input type="password" name="password" id="newPassword" placeholder="Min. 8 characters" required minlength="8" autofocus>
                        <button type="button" class="toggle-pw" onclick="toggleField('newPassword', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmNewPassword"><i class="fas fa-lock"></i> Confirm New Password</label>
                    <div class="password-field">
                        <input type="password" name="confirm_password" id="confirmNewPassword" placeholder="Repeat your new password" required>
                        <button type="button" class="toggle-pw" onclick="toggleField('confirmNewPassword', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-auth">
                    <i class="fas fa-save"></i> Reset Password
                </button>
            </form>
            <?php elseif (!$success): ?>
            <div style="text-align:center; margin-top: 20px;">
                <a href="forgot-password.php" class="btn btn-outline">
                    <i class="fas fa-redo"></i> Request New Reset Link
                </a>
            </div>
            <?php endif; ?>

            <p class="auth-switch">
                <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </p>
        </div>
    </div>
</section>

<script>
function toggleField(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
