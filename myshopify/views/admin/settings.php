<div class="form-group">
    <label class="control-label"
        for="my_shopify_url"><?= _l('settings_my_shopify_url'); ?></label>
    <input type="text" name="settings[my_shopify_url]" class="form-control"
        value="<?= get_option('my_shopify_url'); ?>">
</div>
<div class="form-group">
    <label class="control-label"
        for="my_shopify_url"><?= _l('settings_my_shopify_access_token'); ?></label>
    <input type="text" name="settings[my_shopify_access_token]" class="form-control"
        value="<?= get_option('my_shopify_access_token'); ?>">
</div>
