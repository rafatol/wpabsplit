<div id="wpab_settings">
    <h1><?=__('WP A/B Split Settings')?></h1>
    <form action="<?=admin_url('options-general.php?page=wpab_settings')?>" method="post" enctype="application/x-www-form-urlencoded">
<?php
    $isActivated = false;
    if(\WpAbSplit\License::isActivated()):
        $isActivated = true;
?>
        <div class="notice notice-info">
            <p><?=__('Your <strong>WP A/B Split</strong> license is active. To deactivate it, simply remove the license in the field below and click "Save Changes".')?></p>
        </div>
<?php
    else:
?>
        <div class="notice notice-error">
            <p><?=__('You do not have a valid license to use <strong>WP A/B Split</strong>. Please access our website and purchase a license. If you already have one, enter it in the field below and click "Save Changes".')?></p>
        </div>
<?php
    endif;
?>
        <div class="notice notice-info">
            <p><?=sprintf(__('Do you have any questions about how to use the plugin? <a href="%s" target="_blank">Check out our quick guide!</a>'), 'https://wpabsplit.com/docs/')?></p>
        </div>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="<?=\WpAbSplit\License::LICENSE_KEY?>"><?=__('License Key')?></label>
                    </th>
                    <td>
                        <div class="form-group<?=(($isActivated)?' with-icon':'')?>">
                            <input name="<?=\WpAbSplit\License::LICENSE_KEY?>" type="text" id="<?=\WpAbSplit\License::LICENSE_KEY?>" value="<?=\WpAbSplit\License::getLicenseKey()?>" class="regular-text">
                            <?=(($isActivated)?'<i class="dashicons-before dashicons-saved"></i>':'')?>
                        </div>
                        <p class="description"><?=__('Enter your license key to activate the plugin.')?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="hidden" name="action" value="wpab_settings">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?=__('Save Changes')?>">
        </p>
    </form>
</div>