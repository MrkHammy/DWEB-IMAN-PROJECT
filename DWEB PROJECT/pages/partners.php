<?php
/**
 * Fox Lab – Partner Organizations
 * Grid of partner cards + partnership benefits
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Organizations';
$pdo = getDBConnection();

// Fetch partners with features
$stmtPartners = $pdo->query("SELECT * FROM partners ORDER BY display_order ASC");
$partners = $stmtPartners->fetchAll();

$partnerFeatures = [];
foreach ($partners as $p) {
    $stmtFeatures = $pdo->prepare("SELECT feature, icon FROM partner_features WHERE partner_id = :pid ORDER BY id ASC");
    $stmtFeatures->execute([':pid' => $p['id']]);
    $partnerFeatures[$p['id']] = $stmtFeatures->fetchAll();
}

// Fetch benefits
$stmtBenefits = $pdo->query("SELECT * FROM partnership_benefits ORDER BY display_order ASC");
$benefits = $stmtBenefits->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- ===== PARTNER ORGANIZATIONS ===== -->
<section class="partners-page">
    <div class="container">
        <div class="partners-page-header">
            <h1>Partner Organizations</h1>
            <p>Discover our network of professional technology organizations and industry partners driving innovation and collaboration in the tech community.</p>
        </div>

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

                <a href="<?php echo e($partner['website_url'] ?? '#'); ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener noreferrer">Learn More</a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ===== PARTNERSHIP BENEFITS ===== -->
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
