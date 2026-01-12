<footer class="footer footer-fixed">
    <div>
        <img src="<?php echo logo('apple-icon-60x60.png'); ?>" alt="<?php echo SITE_NAME; ?>" class="footer-logo" height="24">
        <span class="ms-2">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Hak cipta terpelihara.</span>
    </div>
    <div class="ms-auto">
        Dikuasakan oleh&nbsp;<a href="https://coreui.io/">CoreUI</a>
    </div>
</footer>

<!-- CoreUI JS -->
<script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.3.0/dist/js/coreui.bundle.min.js"></script>
<!-- Note: CoreUI bundle already includes all necessary utilities, so separate utils file is not needed -->

<!-- Custom JS -->
<script src="<?php echo asset('js/custom.js'); ?>"></script>
</body>
</html>

