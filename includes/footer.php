<?php
/**
 * Footer Include File
 * Contains common footer HTML
 */
?>
    <?php if (isLoggedIn()): ?>
    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2024 Travel Agency Inc. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>
    </footer>
    <?php endif; ?>
    
    <script src="assets/js/main.js"></script>
    <script src="assets/js/animations.js"></script>
</body>
</html>

