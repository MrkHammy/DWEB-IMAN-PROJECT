<?php
/**
 * Fox Lab â€“ About Us
 * Team overview, mission, partners, and collaboration benefits.
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'About Us';
$pdo = getDBConnection();

// Fetch partners with features
$stmtPartners = $pdo->query("SELECT * FROM partners ORDER BY display_order ASC");
$partners = $stmtPartners->fetchAll();

$partnerFeatures = [];
foreach ($partners as $p) {
    $stmtFeatures = $pdo->prepare("SELECT feature, icon FROM org_features WHERE partner_id = :pid ORDER BY id ASC");
    $stmtFeatures->execute([':pid' => $p['id']]);
    $partnerFeatures[$p['id']] = $stmtFeatures->fetchAll();
}

// Fetch benefits (static — no DB table needed)
$benefits = [
    ['title' => 'Collaboration', 'description' => 'Joint projects and initiatives', 'icon' => 'fas fa-comments'],
    ['title' => 'Learning',      'description' => 'Shared knowledge and training',  'icon' => 'fas fa-graduation-cap'],
    ['title' => 'Networking',    'description' => 'Professional connections',        'icon' => 'fas fa-project-diagram'],
    ['title' => 'Innovation',    'description' => 'Technology advancement',          'icon' => 'fas fa-rocket'],
];

// About stats
$totalPartners = count($partners);
$totalBenefits = count($benefits);
$activeTips = 5;

include __DIR__ . '/../includes/header.php';
?>

<section class="partners-page about-page">
    <div class="container">
        <div class="partners-page-header about-header">
            <h1>About Us</h1>
            <p>Fox Lab is a cybersecurity learning platform built to help students and organizations strengthen practical security awareness through simulation, guided learning, and hands-on activities.</p>
        </div>

        <div class="about-stats-grid">
            <div class="about-stat-card">
                <span class="about-stat-value"><?php echo e((string)$totalPartners); ?>+</span>
                <span class="about-stat-label">Partner Organizations</span>
            </div>
            <div class="about-stat-card">
                <span class="about-stat-value"><?php echo e((string)$activeTips); ?>+</span>
                <span class="about-stat-label">Security Tips Published</span>
            </div>
            <div class="about-stat-card">
                <span class="about-stat-value"><?php echo e((string)$totalBenefits); ?>+</span>
                <span class="about-stat-label">Collaboration Benefits</span>
            </div>
        </div>

        <section class="about-info-grid">
            <article class="about-info-card">
                <h3><i class="fas fa-bullseye"></i> Our Mission</h3>
                <p>Deliver accessible cybersecurity education that builds confidence in identifying digital threats, improving account protection, and applying secure online behavior in real-world situations.</p>
            </article>
            <article class="about-info-card">
                <h3><i class="fas fa-eye"></i> Our Vision</h3>
                <p>Develop a trusted training environment where every learner can practice, assess, and improve cybersecurity skills through continuous, practical, and data-driven learning.</p>
            </article>
            <article class="about-info-card">
                <h3><i class="fas fa-handshake"></i> What We Do</h3>
                <p>We combine phishing simulations, password-strength learning, coding practice, glossary references, and expert content to create one complete cybersecurity awareness hub.</p>
            </article>
        </section>

        <section class="about-focus-section">
            <h2 class="section-title">How Fox Lab Supports Learners</h2>
            <div class="about-focus-grid">
                <div class="about-focus-item">
                    <i class="fas fa-fish"></i>
                    <h4>Threat Recognition</h4>
                    <p>Practice spotting suspicious emails and indicators through guided phishing simulations.</p>
                </div>
                <div class="about-focus-item">
                    <i class="fas fa-key"></i>
                    <h4>Password Awareness</h4>
                    <p>Understand password complexity and compromised-password risk through interactive checks.</p>
                </div>
                <div class="about-focus-item">
                    <i class="fas fa-book"></i>
                    <h4>Knowledge Reinforcement</h4>
                    <p>Learn key cybersecurity concepts quickly with tips, glossary terms, and practical references.</p>
                </div>
            </div>
        </section>

        <section class="about-partners-wrap">
            <h2 class="section-title">Partner Organizations</h2>
            <p class="section-subtitle">Our community and industry partners that help drive technology learning and collaboration.</p>

            <div class="partners-grid">
                <?php foreach ($partners as $partner): ?>
                <div class="partner-card">
                    <div class="partner-card-header">
                        <?php if ($partner['logo_url']): ?>
                        <img src="../<?php echo e($partner['logo_url']); ?>" alt="<?php echo e($partner['name']); ?>" class="partner-card-logo" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="partner-card-logo-placeholder" style="display:none;"><i class="fas fa-building"></i></div>
                        <?php else: ?>
                        <div class="partner-card-logo-placeholder"><i class="fas fa-building"></i></div>
                        <?php endif; ?>
                        <span class="partner-type"><?php echo e($partner['org_type']); ?></span>
                    </div>

                    <h3><?php echo e($partner['name']); ?></h3>
                    <p class="partner-desc"><?php echo e($partner['description']); ?></p>

                    <?php if ($partner['cover_url']): ?>
                    <img src="../<?php echo e($partner['cover_url']); ?>" alt="<?php echo e($partner['name']); ?> Cover" class="partner-cover">
                    <?php endif; ?>

                    <?php if (isset($partnerFeatures[$partner['id']]) && !empty($partnerFeatures[$partner['id']])): ?>
                    <ul class="partner-features">
                        <?php foreach ($partnerFeatures[$partner['id']] as $feature): ?>
                        <li>
                            <i class="<?php echo e($feature['icon']); ?>"></i>
                            <?php echo e($feature['feature']); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <a href="<?php echo e($partner['website_url'] ?? '#'); ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener noreferrer">Visit Website</a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <?php if (!empty($benefits)): ?>
        <section class="benefits-section">
            <div class="container">
                <h2 class="section-title" style="margin-bottom:40px;">Partnership Benefits</h2>
                <div class="benefits-grid">
                    <?php foreach ($benefits as $benefit): ?>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="<?php echo e($benefit['icon']); ?>"></i>
                        </div>
                        <h4><?php echo e($benefit['title']); ?></h4>
                        <p><?php echo e($benefit['description']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </div>
</section>

<?php $excludePage = 'partners'; include __DIR__ . '/../includes/recommended.php'; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
