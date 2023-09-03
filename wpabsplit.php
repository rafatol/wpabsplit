<?php
/**
 * @package WpAbSplit
 */

/**
Plugin Name:  WP A/B Split
Plugin URI:   https://wpabsplit.com
Description:  Turning Traffic into Conversions, One Test at a Time!
Version:      0.1.0
Author:       WP A/B Split
Author URI:   https://wpabsplit.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wpabsplit
Domain Path:  /languages
 */

if(!function_exists('add_action')){
    echo __('Hi there!  I\'m just a plugin, not much I can do when called directly.');
    exit;
}

define('WPAB_VERSION', '0.1.0');
define('WPAB_PLUGIN_PATH', plugin_dir_path(__FILE__));

define('WPAB_POST_TYPE', 'wpab_test');
define('WPAB_NONCE_KEY', 'wpab_nonce');

require_once WPAB_PLUGIN_PATH . 'class.wpab.php';

register_activation_hook(__FILE__, [WpAbSplit::class, 'plugin_activation']);
register_deactivation_hook(__FILE__, [WpAbSplit::class, 'plugin_deactivation']);

add_action('init', [WpAbSplit::class, 'init']);
add_action('admin_init', [WpAbSplit::class, 'admin_init']);
add_action('save_post', [WpAbSplit::class, 'save_post']);

add_action('admin_enqueue_scripts', [WpAbSplit::class, 'admin_enqueue_scripts']);

add_action('wp', [WpAbSplit::class, 'wp']);

add_action('wp_ajax_nopriv_wpab_probe', [WpAbSplit::class, 'probe']);
add_action('wp_ajax_wpab_probe', [WpAbSplit::class, 'probe']);

function WPAB_get_test_subjects($post_id)
{
    $test_subjects = get_post_meta($post_id, 'wpab_test_subjects', true);

    if(!$test_subjects){
        return [];
    }

    return $test_subjects;
}

function WPAB_get_trigger_type($post_id)
{
    $triggers = get_post_meta($post_id, 'wpab_trigger_type', true);

    if(!$triggers){
        return [];
    }

    return $triggers;
}