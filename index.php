<?php
/**
 * Fox Lab â€“ Home Page (index.php)
 * Hero, Platform Features, Statistics, Security Tips, Partners Banner
 */
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Home';
$pdo = getDBConnection();

// Fetch stats
$stmtStats = $pdo->query("SELECT stat_label, stat_value, stat_icon FROM stats ORDER BY display_order ASC");
$stats = $stmtStats->fetchAll();

// Fetch security tips
$stmtTips = $pdo->query("SELECT title, description, icon FROM tips WHERE is_active = 1 ORDER BY display_order ASC LIMIT 5");
$tips = $stmtTips->fetchAll();

// Fetch partners
$stmtPartners = $pdo->query("SELECT name, logo_url FROM partners ORDER BY display_order ASC");
$partners = $stmtPartners->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO SECTION ===== -->
<section class="hero">
    <div class="container">
        <div class="hero-inner">
            <div class="hero-content">
                <h1>Strengthen Your Cybersecurity Awareness</h1>
                <p>Train your team to identify and prevent phishing attacks with our comprehensive simulation platform and educational resources.</p>
                <div class="hero-buttons">
                    <?php if (isLoggedIn()): ?>
                    <a href="pages/phishing.php" class="btn btn-primary">Get Started</a>
                    <?php else: ?>
                    <a href="pages/login.php" class="btn btn-primary">Get Started</a>
                    <?php endif; ?>
                    <a href="#features" class="btn btn-outline">Learn More</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="IMGS/log_in image.png" alt="Cybersecurity Training" class="hero-image-actual" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="hero-image-placeholder" style="display:none;">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== PLATFORM FEATURES ===== -->
<section class="features-section" id="features">
    <div class="container">
        <h2 class="section-title">Platform Features</h2>
        <p class="section-subtitle">Comprehensive tools for cybersecurity training and assessment</p>
        
        <div class="features-grid">
            <!-- Phishing Simulations -->
            <a href="pages/phishing.php" class="feature-card" style="text-decoration:none;color:inherit;">
                <img src="IMGS/platform_features_imgs/phishing.png" alt="Phishing Simulations" class="feature-card-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="feature-card-img-placeholder" style="display:none;">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div class="feature-card-body">
                    <div class="feature-icon">
                        <i class="fas fa-envelope"></i>
                        <h3>Phishing Simulations</h3>
                    </div>
                    <p>Realistic email simulations to test and train your team ability to identify phishing attempts.</p>
                    <span class="learn-more">Learn More <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            
            <!-- Password Tester -->
            <a href="pages/checker.php" class="feature-card" style="text-decoration:none;color:inherit;">
                <img src="IMGS/platform_features_imgs/how strong is ur password.png" alt="Password Tester" class="feature-card-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="feature-card-img-placeholder" style="display:none;">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="feature-card-body">
                    <div class="feature-icon">
                        <i class="fas fa-question-circle"></i>
                        <h3>How Strong is your Password?</h3>
                    </div>
                    <p>Interactive assessments to evaluate knowledge and reinforce security best practices.</p>
                    <span class="learn-more">Learn More <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            
            <!-- Online Coding -->
            <a href="pages/compiler.php" class="feature-card" style="text-decoration:none;color:inherit;">
                <img src="IMGS/platform_features_imgs/online compiler.png" alt="Online Compiler" class="feature-card-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="feature-card-img-placeholder" style="display:none;">
                    <i class="fas fa-code"></i>
                </div>
                <div class="feature-card-body">
                    <div class="feature-icon">
                        <i class="fas fa-code"></i>
                        <h3>Online Coding</h3>
                    </div>
                    <p>Accessible and free online compiler available to all!</p>
                    <span class="learn-more">Learn More <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- ===== STATISTICS ===== -->
<section class="stats-section">
    <div class="container">
        <h2 class="section-title">Why HAU Fox Lab?</h2>
        <div class="stats-grid">
            <?php foreach ($stats as $stat): ?>
            <div class="stat-card">
                <span class="stat-value"><?php echo e($stat['stat_value']); ?></span>
                <span class="stat-label"><?php echo e($stat['stat_label']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== SECURITY TIPS ===== -->
<section class="tips-section">
    <div class="container">
        <div class="tips-inner">
            <div class="tips-image">
                <img src="IMGS/latest_securityimage.png" alt="Security Tips" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="tips-image-placeholder" style="display:none;">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
            <div class="tips-content">
                <h2>Latest Security Tips</h2>
                <?php foreach ($tips as $tip): ?>
                <div class="tip-item">
                    <h4><?php echo e($tip['title']); ?></h4>
                    <p><?php echo e($tip['description']); ?></p>
                </div>
                <?php endforeach; ?>
                <div class="tips-buttons">
                    <a href="pages/terms.php" class="btn btn-primary">View All Tips</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== PARTNERS BANNER ===== -->
<section class="partners-banner">
    <div class="container">
        <h2>Organizations We Have Partnered With</h2>
        <div class="partners-logos">
            <?php foreach ($partners as $partner): ?>
            <div class="partner-logo-item">
                <?php if (strtoupper($partner['name']) === 'CISCO'): ?>
                <img src="<?php echo e($partner['logo_url']); ?>" alt="<?php echo e($partner['name']); ?>" onerror="this.parentElement.innerHTML='<span class=\'partner-text-logo\'><?php echo e($partner['name']); ?></span>'">
                <?php else: ?>
                <span class="partner-text-logo"><?php echo e($partner['name']); ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="partners-banner-buttons">
            <a href="pages/partners.php" class="btn btn-outline">View Organizations</a>
            <a href="pages/blog.php" class="btn btn-primary">See Blogs</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
