<?php
/**
 * Fox Lab – Security Tips Page
 * Detailed cybersecurity tips with expanded guidance.
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Security Tips';
$pdo = getDBConnection();

// Static tips data (no DB table needed)
$tips = [
    ['title' => 'Verify Sender Addresses',        'description' => 'Always check the email sender\'s address carefully. Phishers often use addresses that look similar to legitimate ones.', 'icon' => 'fas fa-envelope-open-text', 'display_order' => 1, 'created_at' => null],
    ['title' => 'Think Before You Click',          'description' => 'Hover over links to preview the URL before clicking. Be wary of shortened URLs or suspicious domains.', 'icon' => 'fas fa-mouse-pointer', 'display_order' => 2, 'created_at' => null],
    ['title' => 'Enable Two-Factor Authentication','description' => 'Add an extra layer of security to your accounts with 2FA. This significantly reduces the risk of unauthorized access.', 'icon' => 'fas fa-lock', 'display_order' => 3, 'created_at' => null],
    ['title' => 'Keep Software Updated',           'description' => 'Regularly update your operating system, browser, and applications to patch known security vulnerabilities.', 'icon' => 'fas fa-sync-alt', 'display_order' => 4, 'created_at' => null],
    ['title' => 'Use Strong Unique Passwords',     'description' => 'Create complex passwords for each account. Consider using a password manager to keep track of them securely.', 'icon' => 'fas fa-key', 'display_order' => 5, 'created_at' => null],
];

// Extended detail for each tip (keyed by display_order)
$tipDetails = [
    1 => [
        'why' => 'Phishing emails rely on forged sender addresses to impersonate trusted organizations. Attackers use domain spoofing, look-alike domains (e.g., "support@g00gle.com" instead of "support@google.com"), and display name tricks to appear legitimate.',
        'steps' => ['Check the full email address, not just the display name.', 'Look for misspellings or extra characters in the domain.', 'Compare the sender to previous legitimate emails from that organization.', 'Hover over the sender name to reveal the actual email address.'],
    ],
    2 => [
        'why' => 'Malicious links are the primary delivery method for phishing attacks, malware downloads, and credential-harvesting pages. A single careless click can compromise an entire account or device.',
        'steps' => ['Hover over any link to preview the full URL before clicking.', 'Be cautious of shortened URLs (bit.ly, tinyurl) — use a URL expander to check.', 'Never click links in emails that create urgency or threaten consequences.', 'When in doubt, navigate to the website directly by typing the URL yourself.'],
    ],
    3 => [
        'why' => 'Even strong passwords can be compromised through data breaches, keyloggers, or social engineering. Two-factor authentication adds a second verification step that an attacker cannot easily bypass, reducing unauthorized access risk by over 99%.',
        'steps' => ['Enable 2FA on all accounts that support it — especially email, banking, and social media.', 'Use an authenticator app (Google Authenticator, Authy) instead of SMS when available.', 'Keep backup recovery codes stored securely offline.', 'Consider hardware security keys (YubiKey) for your most sensitive accounts.'],
    ],
    4 => [
        'why' => 'Cybercriminals actively exploit known vulnerabilities in outdated software. Security patches fix these weaknesses, but only if you install them. Unpatched systems are the leading cause of successful cyberattacks in organizations.',
        'steps' => ['Enable automatic updates on your operating system and browser.', 'Update mobile apps regularly through your device\'s app store.', 'Replace software that is no longer receiving security updates (end-of-life).', 'Restart your devices after updates to ensure patches take effect.'],
    ],
    5 => [
        'why' => 'Password reuse is one of the biggest security risks. When one service is breached, attackers use credential stuffing to try the same password across hundreds of other sites. A unique, complex password for each account limits the blast radius of any single breach.',
        'steps' => ['Use at least 12–16 characters mixing uppercase, lowercase, numbers, and symbols.', 'Never reuse a password across multiple accounts.', 'Use a reputable password manager (Bitwarden, 1Password) to generate and store passwords.', 'Change passwords immediately if a service you use announces a data breach.'],
    ],
];

include __DIR__ . '/../includes/header.php';
?>

<section class="tips-page">
    <div class="container">
        <div class="tips-page-top">
            <a href="javascript:history.back()" class="back-link"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <div class="tips-page-header">
            <h1>Security Tips</h1>
            <p>Actionable cybersecurity tips with detailed explanations and step-by-step guidance to protect yourself and your organization from common threats.</p>
        </div>

        <?php if (!empty($tips)): ?>
        <div class="tips-detail-list">
            <?php foreach ($tips as $index => $tip): 
                $order = (int)$tip['display_order'];
                $detail = $tipDetails[$order] ?? null;
            ?>
            <article class="tip-detail-card">
                <div class="tip-detail-header">
                    <div class="tip-detail-icon">
                        <i class="<?php echo e($tip['icon'] ?: 'fas fa-shield-alt'); ?>"></i>
                    </div>
                    <div class="tip-detail-title">
                        <span class="tip-number">Tip <?php echo $index + 1; ?></span>
                        <h2><?php echo e($tip['title']); ?></h2>
                    </div>
                </div>

                <p class="tip-detail-summary"><?php echo e($tip['description']); ?></p>

                <?php if ($detail): ?>
                <div class="tip-detail-why">
                    <h4><i class="fas fa-question-circle"></i> Why This Matters</h4>
                    <p><?php echo e($detail['why']); ?></p>
                </div>

                <div class="tip-detail-steps">
                    <h4><i class="fas fa-list-check"></i> What You Should Do</h4>
                    <ol>
                        <?php foreach ($detail['steps'] as $step): ?>
                        <li><?php echo e($step); ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="tips-empty-state">
            <i class="fas fa-lightbulb"></i>
            <h3>No tips available right now</h3>
            <p>Please check back later for updated cybersecurity guidance.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php $excludePage = 'tips'; include __DIR__ . '/../includes/recommended.php'; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
