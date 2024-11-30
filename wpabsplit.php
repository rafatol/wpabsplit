<?php
/**
 * @package WpAbSplit
 */

use WpAbSplit\WpAbSplit as WpAbSplit;
use WpAbSplit\Licence as Licence;
use WpAbSplit\LicenseException as LicenseException;
use WpAbSplit\Updater as Updater;

/**
Plugin Name:  WP A/B Split
Plugin URI:   https://wpabsplit.com
Description:  Turning Traffic into Conversions, One Test at a Time!
Version:      0.1.2
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

define('WPAB_VERSION', '0.1.2');
define('WPAB_PLUGIN_SLUG', 'wpabsplit');
define('WPAB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WPAB_PLUGIN_URL', plugin_dir_url(__FILE__));

define('WPAB_POST_TYPE', 'wpab_test');
define('WPAB_NONCE_KEY', 'wpab_nonce');

define('WPAB_PLATFORM_SMALL', 'small');
define('WPAB_PLATFORM_MEDIUM', 'medium');
define('WPAB_PLATFORM_LARGE', 'large');

define('WPAB_SESSION_NAME', 'wpab_session');

require_once WPAB_PLUGIN_PATH . 'include/class.wpab.php';
require_once WPAB_PLUGIN_PATH . 'include/class.license.php';
require_once WPAB_PLUGIN_PATH . 'include/class.updater.php';
require_once WPAB_PLUGIN_PATH . 'include/class.exception.php';

register_activation_hook(__FILE__, [WpAbSplit::class, 'plugin_activation']);
register_deactivation_hook(__FILE__, [WpAbSplit::class, 'plugin_deactivation']);

add_action('init', [WpAbSplit::class, 'init']);
add_action('admin_init', [WpAbSplit::class, 'admin_init']);
add_action('admin_notices', [WpAbSplit::class, 'admin_notices']);
add_action('save_post', [WpAbSplit::class, 'save_post']);

add_filter('user_has_cap', [WpAbSplit::class, 'user_has_cap'], 10, 3);

add_action('admin_enqueue_scripts', [WpAbSplit::class, 'admin_enqueue_scripts']);

add_action('pre_get_posts', [WpAbSplit::class, 'pre_get_posts']);

add_action('admin_menu', [WpAbSplit::class, 'admin_menu']);

add_action('wp_ajax_nopriv_wpab_probe', [WpAbSplit::class, 'probe']);
add_action('wp_ajax_wpab_probe', [WpAbSplit::class, 'probe']);

add_action('admin_action_wpab_report', [WpAbSplit::class, 'report']);
add_action('admin_action_wpab_settings', [WpAbSplit::class, 'settings']);
add_action('post_action_toggle_test_status', [WpAbSplit::class, 'toggle_test_status']);

add_filter('post_row_actions', [WpAbSplit::class, 'post_row_actions'], 10, 2);
add_filter('manage_wpab_test_posts_columns', [WpAbSplit::class, 'manage_wpab_test_posts_columns']);
add_action('manage_wpab_test_posts_custom_column', [WpAbSplit::class, 'manage_wpab_test_posts_custom_column'], 10, 2);
add_filter('manage_edit-wpab_test_sortable_columns', [WpAbSplit::class, 'manage_edit_wpab_test_sortable_columns']);
add_filter('display_post_states', [WpAbSplit::class, 'display_post_states'], 10, 2);

add_action('restrict_manage_posts', [WpAbSplit::class, 'restrict_manage_posts']);
add_filter('parse_query', [WpAbSplit::class, 'parse_query']);

add_filter('views_edit-wpab_test', [WpAbSplit::class, 'views_edit_wpab_test']);
add_filter('pre_option_page_on_front', [WpAbSplit::class, 'pre_option_page_on_front']);

add_filter('plugins_api', [Updater::class, 'plugin_info'], 20, 3);
add_filter('site_transient_update_plugins', [Updater::class, 'plugin_update']);

function WPAB_get_test_subjects($post_id)
{
    $test_subjects = get_post_meta($post_id, 'wpab_test_subjects', true);

    if(!$test_subjects){
        return [];
    }

    return $test_subjects;
}

function WPAB_get_control($post_id)
{
    $control_page = get_post_meta($post_id, 'wpab_control_page', true);

    if(!$control_page){
        return false;
    }

    return $control_page;
}

function WPAB_get_control_color($post_id)
{
	$control_page_color = get_post_meta($post_id, 'wpab_control_page_color', true);

	if(!$control_page_color){
		return '#FF0000';
	}

	return $control_page_color;
}

function WPAB_get_hypothesis($post_id)
{
    $hypothesis = get_post_meta($post_id, 'wpab_hypothesis_page', true);

    if(!$hypothesis){
        return false;
    }

    return $hypothesis;
}

function WPAB_get_hypothesis_color($post_id)
{
    $hypothesis_color = get_post_meta($post_id, 'wpab_hypothesis_page_color', true);

    if(!$hypothesis_color){
        return '#0000FF';
    }

    return $hypothesis_color;
}

function WPAB_get_selector($post_id)
{
    $selector = get_post_meta($post_id, 'wpab_trigger_selector', true);

    if(!$selector){
        return null;
    }

    return $selector;
}

function WPAB_get_event($post_id)
{
    $event = get_post_meta($post_id, 'wpab_trigger_event', true);

    if(!$event){
        return [];
    }

    return $event;
}

function WPAB_get_test_quantity($post_id, $default = 2)
{
    $test_quantity = get_post_meta($post_id, 'wpab_test_quantity', true);

    if(!$test_quantity){
        return $default;
    }

    return $test_quantity;
}

function WPAB_get_total_runs($post_id)
{
	$totalRuns = get_post_meta($post_id, 'wpab_runs', true);

	if(!is_numeric($totalRuns)){
		global $wpdb;

		$executionsTableName = $wpdb->prefix . 'wpab_executions';

		$checkQuery = <<<SQL
SELECT COALESCE(COUNT(id), 0) AS runs FROM {$executionsTableName} WHERE test_id = {$post_id};
SQL;

		$checkResult = $wpdb->get_row($checkQuery);
		$totalRuns = $checkResult->runs;

		update_post_meta($post_id, 'wpab_runs', $totalRuns);
	}


	return $totalRuns;
}

function WPAB_get_progress($post_id)
{
	$progress = get_post_meta($post_id, 'wpab_progress', true);

	if(!is_numeric($progress)){
		$testQuantity = WPAB_get_test_quantity($post_id);
		$testRuns = WPAB_get_total_runs($post_id);

		$progress = ceil(($testRuns / $testQuantity) * 100);

		update_post_meta($post_id, 'wpab_progress', $progress);
	}

	return $progress;
}

function WPAB_test_started($post_id)
{
	$totalRuns = WPAB_get_total_runs($post_id);
	return ($totalRuns > 0);
}

/**
 * @param $post_id
 *
 * @return DateTime
 * @throws Exception
 */
function WPAB_get_test_last_run($post_id)
{
	global $wpdb;

	$executionsTableName = $wpdb->prefix . 'wpab_executions';

	$lastRunDateQuery = <<<SQL
SELECT end_datetime FROM {$executionsTableName} WHERE test_id = {$post_id} ORDER BY end_datetime DESC LIMIT 1;
SQL;

	$lastRunDateResult = $wpdb->get_row($lastRunDateQuery);

	if($lastRunDateResult){
		return new DateTime($lastRunDateResult->end_datetime);
	}

	return false;
}