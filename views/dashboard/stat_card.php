<?php
// Dashboard Statistics Card View
// Reusable widget for dashboard metrics
?>

<div class="stat-card card h-100 shadow">
    <div class="card-body">
        <div class="text-<?php echo $color ?? 'primary'; ?> text-uppercase small font-weight-bold mb-1">
            <i class="<?php echo $icon ?? 'fas fa-box'; ?>"></i> <?php echo $title ?? 'Metric'; ?>
        </div>
        <h3 class="mb-0"><?php echo $value ?? '0'; ?></h3>
        <?php if (isset($subtitle)): ?>
            <small class="text-<?php echo $subtitleColor ?? 'muted'; ?>"><?php echo $subtitle; ?></small>
        <?php endif; ?>
    </div>
    <?php if (isset($link)): ?>
        <a href="<?php echo $link; ?>" class="card-footer bg-light small text-muted">
            View All <i class="fas fa-arrow-right"></i>
        </a>
    <?php endif; ?>
</div>