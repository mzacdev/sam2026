<?php
/**
 * Public Layout Template
 * Lightweight layout that reuses header/footer but hides admin sidebar and auth checks.
 */
require_once __DIR__ . '/header-public.php';
?>

<!-- Public Navigation (top) - simplified static nav for public view -->
<nav class="navbar navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo url('public/index.php'); ?>"><?php echo SITE_NAME; ?></a>
        <ul class="nav">
            <li class="nav-item"><a class="nav-link" href="<?php echo url('public/index.php'); ?>">Home</a></li>
            <!-- Schedule & Result and Athletes removed from public nav -->
            <li class="nav-item"><a class="nav-link" href="#">Contingent</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo url('public/medal-standings.php'); ?>">Medal Tally</a></li>
        </ul>
    </div>
</nav>

<!-- Content Body Start -->
<div class="content-body flex-grow-1">
    <div class="container-fluid mt-0">
        <?php echo $content; ?>
        <?php require_once __DIR__ . '/footer.php'; ?>
    </div>
</div>

</div>
