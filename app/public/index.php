<?php
/**
 * Public Index / Home
 * Simple landing page linking to public sections.
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Home';

ob_start();
?>
<div class="row mb-4">
    <div class="col-12 text-center">
        <h1 class="mb-1">Welcome to <?php echo htmlspecialchars(SITE_FULL_NAME, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="text-muted mb-3">Follow the event: schedule, results, athletes and medal standings.</p>
        <div class="d-flex justify-content-center gap-2">
            <a class="btn btn-primary" href="<?php echo url('public/medal-standings.php'); ?>">Medal Standings</a>
            <a class="btn btn-outline-secondary" href="#">Schedule & Results</a>
            <a class="btn btn-outline-secondary" href="#">Athletes</a>
            <a class="btn btn-outline-secondary" href="#">Contingent</a>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card mb-4 shadow-sm">
            <div class="card-header"><strong>Latest Results</strong></div>
            <div class="card-body text-muted">Latest results will appear here (public view).</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4 shadow-sm">
            <div class="card-header"><strong>Medal Snapshot</strong></div>
            <div class="card-body text-muted">Quick medal summary and highlights.</div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout_public.php';
?>