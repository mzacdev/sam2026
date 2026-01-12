<?php
require_once __DIR__ . '/../config.php';
?>
<aside class="sidebar sidebar-dark sidebar-fixed" id="sidebar">
    <div class="sidebar-brand d-none d-md-flex">
        <div class="sidebar-brand-full">
            <strong><?php echo SITE_NAME; ?></strong>
        </div>
        <div class="sidebar-brand-narrow">
            <strong>S</strong>
        </div>
    </div>
    <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
        <?php foreach ($nav_items as $item): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $item['active'] ? 'active' : ''; ?>" href="<?php echo url($item['url']); ?>">
                    <i class="nav-icon cil <?php echo $item['icon']; ?>"></i>
                    <?php echo $item['title']; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
</aside>

