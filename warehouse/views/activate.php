<div class="panel_s">
    <div class="panel-body">
        <h4><?php echo html_escape($title ?? 'Module activation'); ?></h4>
        <p class="text-muted">Activation verification is disabled for this fork.</p>
        <a class="btn btn-primary" href="<?php echo isset($original_url) ? $original_url : admin_url('modules'); ?>">Continue</a>
    </div>
</div>
