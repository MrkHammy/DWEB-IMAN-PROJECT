<?php
/**
 * Fox Lab – Shared Footer Component
 */
$isSubPage = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'pages');
$basePath = $isSubPage ? '../' : '';
$pagesPath = $isSubPage ? '' : 'pages/';
$homePath = $isSubPage ? '../index.php' : 'index.php';
?>
    </main>
    <!-- ===== FOOTER ===== -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3>Fox Lab</h3>
                    <p>Empowering organizations with cybersecurity education and training solutions.</p>
                </div>
                <div class="footer-col">
                    <h4>Fox Simulations</h4>
                    <a href="<?php echo $pagesPath; ?>phishing.php">Phishing Simulations</a>
                    <a href="<?php echo $pagesPath; ?>checker.php">Password Tester</a>
                </div>
                <div class="footer-col">
                    <h4>More</h4>
                    <a href="<?php echo $pagesPath; ?>compiler.php">Fox Code &amp; Tutorials</a>
                    <a href="<?php echo $pagesPath; ?>tips.php">Security Tips</a>
                    <a href="<?php echo $pagesPath; ?>terms.php">Glossary</a>
                    <a href="<?php echo $pagesPath; ?>blog.php">Fox Blogs</a>
                    <a href="<?php echo $pagesPath; ?>partners.php">Who The Fox?</a>
                </div>

                <div class="footer-col">
                    <h4>Connect</h4>
                    <div class="footer-social">
                        <a href="https://twitter.com" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://linkedin.com" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://github.com" target="_blank" rel="noopener" aria-label="GitHub"><i class="fab fa-github"></i></a>
                        <a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> HAU Fox Lab. All rights reserved.</p>
        </div>
    </footer>
    <!-- ===== END FOOTER ===== -->

    <!-- Main JS -->
    <script src="<?php echo $basePath; ?>assets/js/main.js"></script>

    <!-- Dark Mode Toggle Script -->
    <script>
    (function() {
        const toggle = document.getElementById('darkModeToggle');
        if (!toggle) return;

        // Sync checkbox with current state
        const isDark = document.documentElement.classList.contains('dark-mode');
        toggle.checked = isDark;

        toggle.addEventListener('change', function() {
            const checked = this.checked;
            // Small delay lets the toggle CSS animation start before
            // the massive style recalculation from CSS-variable changes
            setTimeout(function() {
                if (checked) {
                    document.documentElement.classList.add('dark-mode');
                    localStorage.setItem('foxlab-dark-mode', 'true');
                } else {
                    document.documentElement.classList.remove('dark-mode');
                    localStorage.setItem('foxlab-dark-mode', 'false');
                }
            }, 80);
        });
    })();
    </script>

    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?php echo $basePath; ?>assets/js/<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
