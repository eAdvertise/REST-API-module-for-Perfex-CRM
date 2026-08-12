<div class="form-group">
    <label class="control-label"
        for="my_shopify_url"><?= _l('settings_my_shopify_url'); ?></label>
    <input type="text" name="settings[my_shopify_url]" class="form-control"
        value="<?= html_escape(get_option('my_shopify_url')); ?>">
</div>
<div class="form-group">
    <label class="control-label"><?= _l('settings_my_shopify_webhook_secret'); ?></label>
    <input type="password" name="settings[my_shopify_webhook_secret]" class="form-control"
        value="<?= html_escape(get_option('my_shopify_webhook_secret')); ?>">
    <p class="help-block"><?= _l('settings_my_shopify_webhook_url'); ?>: <code><?= site_url('myshopify/webhook'); ?></code></p>
</div>
<div class="form-group">
    <label class="control-label"><?= _l('settings_my_shopify_location_id'); ?></label>
    <input type="text" name="settings[my_shopify_location_id]" class="form-control"
        value="<?= html_escape(get_option('my_shopify_location_id')); ?>">
</div>
<div class="form-group">
    <label class="control-label"><?= _l('settings_my_shopify_api_version'); ?></label>
    <input type="text" pattern="20[0-9]{2}-(01|04|07|10)" name="settings[my_shopify_api_version]" class="form-control"
        value="<?= html_escape(get_option('my_shopify_api_version')); ?>">
</div>
<div class="form-group">
    <label class="control-label"><?= _l('settings_my_shopify_warehouse_id'); ?></label>
    <input type="number" min="1" name="settings[my_shopify_warehouse_id]" class="form-control"
        value="<?= html_escape(get_option('my_shopify_warehouse_id')); ?>">
</div>
<div class="checkbox checkbox-primary">
    <input type="hidden" name="settings[my_shopify_sync_enabled]" value="0">
    <input type="checkbox" id="my_shopify_sync_enabled" name="settings[my_shopify_sync_enabled]" value="1" <?= get_option('my_shopify_sync_enabled') === '1' ? 'checked' : ''; ?>>
    <label for="my_shopify_sync_enabled"><?= _l('settings_my_shopify_sync_enabled'); ?></label>
</div>
<div class="form-group mtop15">
    <a href="<?= admin_url('myshopify/sync_now'); ?>" class="btn btn-info">
        <i class="fa fa-refresh"></i> Synchronize Shopify now
    </a>
</div>
<div class="form-group">
    <label class="control-label"
        for="my_shopify_url"><?= _l('settings_my_shopify_access_token'); ?></label>
    <input type="text" name="settings[my_shopify_access_token]" class="form-control"
        value="<?= html_escape(get_option('my_shopify_access_token')); ?>">
</div>
