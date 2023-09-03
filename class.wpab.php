<?php

/**
 * @package WpAbSplit
 */

class WpAbSplit {

    static $initialized = false;
    static $adminInitialized = false;

    public function init()
    {
        if(!self::$initialized){
            self::$initialized = true;

            WpAbSplit::add_custom_post_type();
        }
    }

    public function admin_init()
    {
        if(!self::$adminInitialized){
            self::$adminInitialized = true;
            WpAbSplit::add_custom_post_metaboxes();
        }
    }

    public function save_post($post_id)
    {
        if(WPAB_POST_TYPE != get_post_type($post_id)){
            return $post_id;
        }

        if(empty($_POST['wpabsplit_subject_nonce']) || !wp_verify_nonce($_POST['wpabsplit_subject_nonce'], WPAB_NONCE_KEY)){
            return $post_id;
        }

        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
            return $post_id;
        }

        if(!current_user_can('edit_post', $post_id)){
            return $post_id;
        }

        update_post_meta($post_id, 'wpab_test_subjects', array_map('intval', $_POST['selected_pages']));
        update_post_meta($post_id, 'wpab_trigger_type', $_POST['trigger_type']);
    }

    public static function add_custom_post_type()
    {
        // Register Custom Post Type
        register_post_type(WPAB_POST_TYPE, [
            'labels' => [
                'name' => __('A/B Tests'),
                'singular_name' => __('A/B Test'),
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => false,
            'menu_icon' => 'dashicons-forms',
            'supports' => ['title']
        ]);
    }

    public static function add_custom_post_metaboxes()
    {
        add_meta_box('ab_test_subjects', __('Pages to Test'), [WpAbSplit::class, 'create_test_subjects_box'], WPAB_POST_TYPE);
        add_meta_box('ab_test_triggers', __('Triggers'), [WpAbSplit::class, 'create_test_triggers_box'], WPAB_POST_TYPE);
    }

    public function create_test_subjects_box()
    {
        global $post;

        $selected_pages = WPAB_get_test_subjects($post->ID);
        $pages = get_pages();

        include WPAB_PLUGIN_PATH . 'templates/subject_metabox.php';
    }

    public function create_test_triggers_box()
    {
        global $post;

        $trigger_type = WPAB_get_trigger_type($post->ID);

        include WPAB_PLUGIN_PATH . 'templates/trigger_metabox.php';
    }

    public function admin_enqueue_scripts($hook)
    {
        global $post;

        if($hook == 'post.php' && $post->post_type == WPAB_POST_TYPE){
            wp_enqueue_style('wpab-admin-style', plugin_dir_url(__FILE__) . 'assets/admin-styles.css', [], WPAB_VERSION);
            wp_enqueue_script('wpab-admin-script', plugin_dir_url(__FILE__) . 'assets/admin-scripts.js', ['jquery'], WPAB_VERSION, true);
        }
    }

    public function wp()
    {
        if(is_singular()){
            global $post;

            if(get_post_type() == WPAB_POST_TYPE){
                $subjects = WPAB_get_test_subjects($post->ID);

                if(count($subjects)){
                    $query = new WP_Query([
                        'post_type' => 'any',
                        'post__in' => $subjects,
                        'orderby' => 'rand',
                        'posts_per_page' => 1,
                    ]);

                    if($query->have_posts()){
                        $testId = $post->ID;

                        $query->the_post();

                        wp_enqueue_script('wpab-job', plugin_dir_url(__FILE__) . 'assets/wpab-job.js', ['jquery'], WPAB_VERSION, true);

                        $pluginVars = ['id' => uniqid('wpab_', true), 'test_id' => $testId, 'subject_id' => get_the_ID(), 'probe_url' => admin_url('admin-ajax.php'), 'triggers' => []];

                        $triggerType = WPAB_get_trigger_type($testId);

                        if($triggerType == 'data_attribute'){
                            $pluginVars['triggers'][] = ['action' => 'click', 'js_event' => 'click', 'trigger_selector' => '[data-wpab-trigger="click"]', 'description_selector' => 'wpab-description'];
                            $pluginVars['triggers'][] = ['action' => 'submit', 'js_event' => 'submit', 'trigger_selector' => '[data-wpab-trigger="submit"]', 'description_selector' => 'wpab-description'];
                            $pluginVars['triggers'][] = ['action' => 'mousein', 'js_event' => 'mousein', 'trigger_selector' => '[data-wpab-trigger="mousein"]', 'description_selector' => 'wpab-description'];
                            $pluginVars['triggers'][] = ['action' => 'mouseout', 'js_event' => 'mouseout', 'trigger_selector' => '[data-wpab-trigger="mouseout"]', 'description_selector' => 'wpab-description'];
                            $pluginVars['triggers'][] = ['action' => 'visible', 'js_event' => 'visible', 'trigger_selector' => '[data-wpab-trigger="visible"]', 'description_selector' => 'wpab-description'];
                        } else {

                        }

                        wp_localize_script('wpab-job', 'wpab_vars', $pluginVars);
                    }
                }

                return;
            }

//            $query = new WP_Query([
//                'post_type' => WPAB_POST_TYPE,
//                'posts_per_page' => -1,
//                'meta_key' => 'wpab_test_subjects',
//                'meta_value' => sprintf('i:%d;', $post->ID),
//                'meta_compare' => 'LIKE'
//            ]);
//
//            if($query->have_posts()){
//                $query->the_post();
//
//                wp_redirect(get_permalink());
//                return;
//            }
        }
    }

    public function probe()
    {
        /** @todo Registrar em banco ou em outro local as informações recebidas via ajax */
        echo '<pre>';
        print_r($_POST);
        echo '</pre>';

        wp_die();
    }

}