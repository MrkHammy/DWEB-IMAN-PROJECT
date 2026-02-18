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

    <!-- Dark Mode: prevent flash of wrong theme -->
    <script>
        (function() {
            if (localStorage.getItem('foxlab-dark-mode') === 'true') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
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
                <div class="nav-dropdown <?php echo in_array($currentPage, ['phishing', 'checker']) ? 'active' : ''; ?>" id="securitySimDropdown">
                    <button class="nav-dropdown-toggle" type="button" id="securitySimToggle" aria-expanded="false">
                        <span>Fox Simulations</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="nav-dropdown-menu" id="securitySimMenu">
                        <a href="<?php echo $pagesPath; ?>phishing.php" class="<?php echo $currentPage === 'phishing' ? 'active' : ''; ?>"><i class="fas fa-fish"></i> Phishing Simulator</a>
                        <a href="<?php echo $pagesPath; ?>checker.php" class="<?php echo $currentPage === 'checker' ? 'active' : ''; ?>"><i class="fas fa-key"></i> Password Tester</a>
                    </div>
                </div>
                <div class="nav-dropdown <?php echo in_array($currentPage, ['tips', 'terms']) ? 'active' : ''; ?>" id="learnDropdown">
                    <button class="nav-dropdown-toggle" type="button" id="learnToggle" aria-expanded="false">
                        <span>Fox Learn</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="nav-dropdown-menu" id="learnMenu">
                        <a href="<?php echo $pagesPath; ?>tips.php" class="<?php echo $currentPage === 'tips' ? 'active' : ''; ?>"><i class="fas fa-lightbulb"></i> Security Tips</a>
                        <a href="<?php echo $pagesPath; ?>terms.php" class="<?php echo $currentPage === 'terms' ? 'active' : ''; ?>"><i class="fas fa-book"></i> Glossary</a>
                    </div>
                </div>
                <a href="<?php echo $pagesPath; ?>compiler.php" class="<?php echo $currentPage === 'compiler' ? 'active' : ''; ?>">Fox Code</a>
                <a href="<?php echo $pagesPath; ?>blog.php" class="<?php echo $currentPage === 'blog' ? 'active' : ''; ?>">Fox Blogs</a>
                <a href="<?php echo $pagesPath; ?>partners.php" class="<?php echo $currentPage === 'partners' ? 'active' : ''; ?>">Who The Fox?</a>

                <!-- Dark Mode Toggle -->
                <label class="switch" title="Toggle Dark Mode">
                    <input id="darkModeToggle" type="checkbox">
                    <div class="slider round">
                        <div class="sun-moon">
                            <svg id="moon-dot-1" class="moon-dot" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                            <svg id="moon-dot-2" class="moon-dot" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                            <svg id="moon-dot-3" class="moon-dot" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                            <svg id="light-ray-1" class="light-ray" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                            <svg id="light-ray-2" class="light-ray" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                            <svg id="light-ray-3" class="light-ray" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                            <svg id="cloud-1" class="cloud-dark" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                            <svg id="cloud-2" class="cloud-dark" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                            <svg id="cloud-3" class="cloud-dark" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                            <svg id="cloud-4" class="cloud-light" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                            <svg id="cloud-5" class="cloud-light" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                            <svg id="cloud-6" class="cloud-light" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                        </div>
                        <div class="stars">
                            <svg id="star-1" class="star" viewBox="0 0 20 20"><path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path></svg>
                            <svg id="star-2" class="star" viewBox="0 0 20 20"><path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path></svg>
                            <svg id="star-3" class="star" viewBox="0 0 20 20"><path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path></svg>
                            <svg id="star-4" class="star" viewBox="0 0 20 20"><path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path></svg>
                        </div>
                    </div>
                </label>

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
                        <a href="<?php echo $pagesPath; ?>admin-dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
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
