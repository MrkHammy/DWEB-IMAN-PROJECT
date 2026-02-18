<?php
/**
 * Fox Lab – Recommended Pages Component
 * Include at the bottom of any page to show exploration links.
 * Set $excludePage before including to hide current page from the list.
 */
$_recPages = [
    ['href' => 'phishing.php',  'icon' => 'fas fa-fish',       'bg' => 'linear-gradient(135deg, #e74c3c, #c0392b)', 'title' => 'Fox Simulations',     'desc' => 'Test your ability to spot phishing emails'],
    ['href' => 'checker.php',   'icon' => 'fas fa-key',        'bg' => 'linear-gradient(135deg, #27ae60, #2ecc71)', 'title' => 'Password Tester',     'desc' => 'Check how strong your passwords are'],
    ['href' => 'compiler.php',  'icon' => 'fas fa-code',       'bg' => 'linear-gradient(135deg, #0074D9, #005fa3)', 'title' => 'Fox Code',            'desc' => 'Code editor with Python & Java tutorials'],
    ['href' => 'tips.php',      'icon' => 'fas fa-lightbulb',  'bg' => 'linear-gradient(135deg, #16a085, #1abc9c)', 'title' => 'Security Tips',       'desc' => 'Browse practical cybersecurity tips'],
    ['href' => 'terms.php',     'icon' => 'fas fa-book',       'bg' => 'linear-gradient(135deg, #8e44ad, #9b59b6)', 'title' => 'Glossary',            'desc' => 'Explore cybersecurity terminology'],
    ['href' => 'blog.php',      'icon' => 'fas fa-newspaper',  'bg' => 'linear-gradient(135deg, #2c3e50, #34495e)', 'title' => 'Fox Blogs',            'desc' => 'Read the latest cybersecurity articles'],
    ['href' => 'partners.php',  'icon' => 'fas fa-handshake',  'bg' => 'linear-gradient(135deg, #f39c12, #e67e22)', 'title' => 'Who The Fox?',        'desc' => 'Learn about Fox Lab and our partners'],
];

// Filter out current page
$_exclude = isset($excludePage) ? $excludePage : '';
$_recFiltered = array_filter($_recPages, fn($p) => $p['href'] !== $_exclude . '.php');
?>

<section class="recommended-section">
    <div class="container">
        <h2 class="section-title">Explore Fox Lab</h2>
        <p class="section-subtitle">Continue your cybersecurity learning journey</p>
        <div class="recommended-grid">
            <?php foreach ($_recFiltered as $rec): ?>
            <a href="<?php echo $rec['href']; ?>" class="recommended-card">
                <div class="recommended-card-icon" style="background: <?php echo $rec['bg']; ?>;">
                    <i class="<?php echo $rec['icon']; ?>"></i>
                </div>
                <div class="recommended-card-info">
                    <span class="recommended-card-title"><?php echo $rec['title']; ?></span>
                    <span class="recommended-card-desc"><?php echo $rec['desc']; ?></span>
                </div>
                <i class="fas fa-arrow-right recommended-card-arrow"></i>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
