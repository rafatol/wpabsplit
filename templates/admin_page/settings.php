<div id="wpab_settings">
    <h1><?=__('WP A/B Split Settings')?></h1>
    <form action="<?=admin_url('options-general.php?page=wpab_settings')?>" method="post" enctype="application/x-www-form-urlencoded">
<?php
    $isActivated = false;
    if(\WpAbSplit\License::isActivated()):
        $isActivated = true;
?>
        <p><?=__('Your <strong>WP A/B Split</strong> license is active. To deactivate it, simply remove the license in the field below and click "Save Changes".')?></p>
<?php
    else:
?>
        <p><?=__('You do not have a valid license to use <strong>WP A/B Split</strong>. Please access our website and purchase a license. If you already have one, enter it in the field below and click "Save Changes".')?></p>
<?php
    endif;
?>
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