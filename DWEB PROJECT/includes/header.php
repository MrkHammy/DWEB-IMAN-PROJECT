<?php
/**
 * Fox Lab – Shared Header Component
 * Included at the top of every page.
 */

// Determine if we are in /pages/ or root
$isSubPage = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'pages');
$basePath = $isSubPage ? '../' : '';
$pagesPath = $isSubPage ? '' : 'pages/';
$homePath = $isSubPage ? '../index.php' : 'index.php';

// Current page for active nav
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');

// Get current user for auth display
$_currentUser = currentUser();
$_flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Fox Lab – Cybersecurity Awareness & Training Platform. Strengthen your cybersecurity awareness with phishing simulations, password testing, and educational resources.">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' | Fox Lab' : 'Fox Lab – Cybersecurity Awareness'; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
</head>
<body>
    <!-- ===== HEADER ===== -->
    <header class="header">
        <div class="header-inner">
            <a href="<?php echo $homePath; ?>" class="logo">
                <img src="<?php echo $basePath; ?>IMGS/LOGO/logo.png" alt="Fox Lab Logo">
                <span class="logo-text">Fox Lab</span>
            </a>

            <div class="search-bar">
                <i class="fas fa-search search-icon"></i>
                <input type="text" placeholder="Search Cybersecurity Terms..." id="globalSearch" autocomplete="off">
                <div class="search-suggestions" id="globalSearchSuggestions"></div>
            </div>

            <button class="hamburger" id="hamburgerBtn" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>

            <nav class="main-nav" id="mainNav">
                <a href="<?php echo $pagesPath; ?>phishing.php" class="<?php echo $currentPage === 'phishing' ? 'active' : ''; ?>">Phishing Simulations</a>
                <a href="<?php echo $pagesPath; ?>checker.php" class="<?php echo $currentPage === 'checker' ? 'active' : ''; ?>">Password Tester</a>
                <a href="<?php echo $pagesPath; ?>compiler.php" class="<?php echo $currentPage === 'compiler' ? 'active' : ''; ?>">Code Online</a>
                <a href="<?php echo $pagesPath; ?>partners.php" class="<?php echo $currentPage === 'partners' ? 'active' : ''; ?>">Organizations</a>
                <a href="<?php echo $pagesPath; ?>blog.php" class="<?php echo $currentPage === 'blog' ? 'active' : ''; ?>">Blogs</a>

                <?php if (isLoggedIn() && $_currentUser): ?>
                <div class="user-menu">
                    <button class="user-menu-btn" id="userMenuBtn">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo e($_currentUser['full_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <div class="user-dropdown-header">
                            <strong><?php echo e($_currentUser['full_name']); ?></strong>
                            <small><?php echo e($_currentUser['email']); ?></small>
                        </div>
                        <?php if (isAdmin()): ?>
                        <a href="<?php echo $pagesPath; ?>admin-blogs.php"><i class="fas fa-pen-to-square"></i> Manage Blogs</a>
                        <?php endif; ?>
                        <a href="<?php echo $pagesPath; ?>login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?php echo $pagesPath; ?>login.php" class="btn-login">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <!-- ===== END HEADER ===== -->

    <?php if ($_flash): ?>
    <div class="flash-message flash-<?php echo e($_flash['type']); ?>" style="margin:0 auto;max-width:800px;margin-top:10px;">
        <i class="fas fa-<?php echo $_flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo e($_flash['message']); ?>
    </div>
    <?php endif; ?>

    <main>
