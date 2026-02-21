<?php
/**
 * Fox Lab – Login & Register
 * Handles authentication: login, register, and logout
 */
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();
$errors = [];
$success = '';
$activeTab = isset($_GET['tab']) && $_GET['tab'] === 'register' ? 'register' : 'login';

// ===== LOGOUT =====
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: login.php?logged_out=1');
    exit;
}

// Redirect if already logged in
if (isLoggedIn() && !isset($_GET['action'])) {
    header('Location: ../index.php');
    exit;
}

// ===== HANDLE LOGIN =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
            header('Location: ../index.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
    $activeTab = 'login';
}

// ===== HANDLE REGISTER =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'register') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errors[] = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (:name, :email, :pass)");
            $stmt->execute([
                ':name' => $fullName,
                ':email' => $email,
                ':pass' => $hashedPassword
            ]);

            // Auto-login after registration
            $userId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'student';

            setFlash('success', 'Account created successfully! Welcome, ' . $fullName . '!');
            header('Location: ../index.php');
            exit;
        }
    }
    $activeTab = 'register';
}

$pageTitle = 'Login';
include __DIR__ . '/../includes/header.php';
?>

<section class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="../IMGS/LOGO/logo.png" alt="Fox Lab" class="auth-logo" onerror="this.style.display='none'">
                <h1>Fox Lab</h1>
                <p>Cybersecurity Awareness & Training Platform</p>
            </div>

            <?php if (isset($_GET['logged_out'])): ?>
            <div class="flash-message flash-success">
                <i class="fas fa-check-circle"></i> You have been logged out successfully.
            </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo e(implode(' ', $errors)); ?>
            </div>
            <?php endif; ?>

            <!-- Tab Switcher -->
            <div class="auth-tabs">
                <button class="auth-tab <?php echo $activeTab === 'login' ? 'active' : ''; ?>" onclick="switchAuthTab('login')">Login</button>
                <button class="auth-tab <?php echo $activeTab === 'register' ? 'active' : ''; ?>" onclick="switchAuthTab('register')">Register</button>
            </div>

            <!-- LOGIN FORM -->
            <form method="POST" action="login.php" class="auth-form" id="loginForm" style="display:<?php echo $activeTab === 'login' ? 'block' : 'none'; ?>;">
                <input type="hidden" name="form_type" value="login">
                
                <div class="form-group">
                    <label for="loginEmail"><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" id="loginEmail" placeholder="Enter your email" required 
                           value="<?php echo e($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="loginPassword"><i class="fas fa-lock"></i> Password</label>
                    <div class="password-field">
                        <input type="password" name="password" id="loginPassword" placeholder="Enter your password" required>
                        <button type="button" class="toggle-pw" onclick="toggleField('loginPassword', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-auth">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>

                <p class="auth-forgot">
                    <a href="forgot-password.php"><i class="fas fa-question-circle"></i> Forgot your password?</a>
                </p>

                <p class="auth-switch">
                    Don't have an account? <a href="#" onclick="switchAuthTab('register'); return false;">Create one</a>
                </p>
            </form>

            <!-- REGISTER FORM -->
            <form method="POST" action="login.php" class="auth-form" id="registerForm" style="display:<?php echo $activeTab === 'register' ? 'block' : 'none'; ?>;">
                <input type="hidden" name="form_type" value="register">
                
                <div class="form-group">
                    <label for="regName"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="full_name" id="regName" placeholder="Enter your full name" required
                           value="<?php echo e($_POST['full_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="regEmail"><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" id="regEmail" placeholder="Enter your email" required
                           value="<?php echo e($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="regPassword"><i class="fas fa-lock"></i> Password</label>
                    <div class="password-field">
                        <input type="password" name="password" id="regPassword" placeholder="Min. 8 characters" required minlength="8">
                        <button type="button" class="toggle-pw" onclick="toggleField('regPassword', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="regConfirm"><i class="fas fa-lock"></i> Confirm Password</label>
                    <div class="password-field">
                        <input type="password" name="confirm_password" id="regConfirm" placeholder="Repeat your password" required>
                        <button type="button" class="toggle-pw" onclick="toggleField('regConfirm', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-auth">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>

                <p class="auth-switch">
                    Already have an account? <a href="#" onclick="switchAuthTab('login'); return false;">Sign in</a>
                </p>
            </form>
        </div>

        <div class="auth-image">
            <img src="../IMGS/log_in image.png" alt="Cybersecurity" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
            <div class="auth-image-placeholder" style="display:none;">
                <i class="fas fa-shield-alt"></i>
                <span>Protect Your Digital World</span>
            </div>
        </div>
    </div>
</section>

<script>
function switchAuthTab(tab) {
    document.getElementById('loginForm').style.display = tab === 'login' ? 'block' : 'none';
    document.getElementById('registerForm').style.display = tab === 'register' ? 'block' : 'none';
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.auth-tab:nth-child(${tab === 'login' ? 1 : 2})`).classList.add('active');
}

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
