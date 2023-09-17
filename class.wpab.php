<?php

/**
 * @package WpAbSplit
 */

class WpAbSplit {

    static $initialized = false;
    static $adminInitialized = false;

    public static function init()
    {
        if(!self::$initialized){
            self::$initialized = true;

            WpAbSplit::add_custom_post_type();
        }
    }

    public static function plugin_activation()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        /**
         * Tabela que armazena as execuções de teste
         */
        $executionsTableName = $wpdb->prefix . 'wpab_executions';

        $executionsSql = <<<SQL
CREATE TABLE {$executionsTableName} (
  `id` CHAR(28) NOT NULL,
  `start_datetime` DATETIME NULL,
  `end_datetime` DATETIME NULL,
  `test_id` BIGINT UNSIGNED NULL,
  `subject_id` BIGINT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_execution_test_idx` (`test_id` ASC) VISIBLE,
  INDEX `fk_execution_subject_idx` (`subject_id` ASC) VISIBLE,
  CONSTRAINT `fk_execution_test`
    FOREIGN KEY (`test_id`)
    REFERENCES `{$wpdb->prefix}posts` (`ID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_execution_subject`
    FOREIGN KEY (`subject_id`)
    REFERENCES `{$wpdb->prefix}posts` (`ID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE) {$charset_collate};
SQL;

        /**
         * Tabela que armazena os passos executados pelo usuário em cada execução de teste
         */
        $stepsTableName = $wpdb->prefix . 'wpab_steps';

        $stepsSql = <<<SQL
CREATE TABLE {$stepsTableName} (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `execution_id` CHAR(23) NULL,
  `step_name` VARCHAR(100) NULL,
  `step_description` VARCHAR(255) NULL,
  `element_id` VARCHAR(100) NULL,
  `time` DATETIME NULL,
  PRIMARY KEY (`id`)) {$charset_collate};
SQL;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($executionsSql);
        dbDelta($stepsSql);

        add_option('wpab_plugin_db_version', WPAB_VERSION);
    }

    public static function plugin_deactivation()
    {
        global $wpdb;

        $executionsTableName = $wpdb->prefix . 'wpab_executions';
        $stepsTableName = $wpdb->prefix . 'wpab_steps';

        $wpdb->query(sprintf('DROP TABLE IF EXISTS %s', $stepsTableName));
        $wpdb->query(sprintf('DROP TABLE IF EXISTS %s', $executionsTableName));

        delete_option('wpab_plugin_db_version');
    }

    public static function admin_init()
    {
        if(!self::$adminInitialized){
            self::$adminInitialized = true;
            WpAbSplit::add_custom_post_metaboxes();
        }
    }

    public static function save_post($post_id)
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
        update_post_meta($post_id, 'wpab_triggers', $_POST['trigger']);
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
            'supports' => ['title', 'editor']
        ]);
    }

    public static function add_custom_post_metaboxes()
    {
        add_meta_box('ab_test_subjects', __('Pages to Test'), [WpAbSplit::class, 'create_test_subjects_box'], WPAB_POST_TYPE);
        add_meta_box('ab_test_triggers', __('Triggers'), [WpAbSplit::class, 'create_test_triggers_box'], WPAB_POST_TYPE);
    }

    public static function create_test_subjects_box()
    {
        global $post;

        $selected_pages = WPAB_get_test_subjects($post->ID);
        $pages = get_pages();

        include WPAB_PLUGIN_PATH . 'templates/subject_metabox.php';
    }

    public static function create_test_triggers_box()
    {
        global $post;

        $trigger_type = WPAB_get_trigger_type($post->ID);
        $triggers = WPAB_get_triggers($post->ID);

        include WPAB_PLUGIN_PATH . 'templates/trigger_metabox.php';
    }

    public static function admin_enqueue_scripts($hook)
    {
        global $post;

        if($hook == 'post.php' && $post->post_type == WPAB_POST_TYPE){
            wp_enqueue_style('wpab-admin-toast-css', plugin_dir_url(__FILE__) . 'assets/plugins/jquery-toast-plugin/jquery.toast.min.css', [], '1.3.2');
            wp_enqueue_script('wpab-admin-toast-js', plugin_dir_url(__FILE__) . 'assets/plugins/jquery-toast-plugin/jquery.toast.min.js', ['jquery'], '1.3.2', true);

            wp_enqueue_style('wpab-admin-style', plugin_dir_url(__FILE__) . 'assets/admin-stylesheet.css', [], WPAB_VERSION);
            wp_enqueue_script('wpab-admin-script', plugin_dir_url(__FILE__) . 'assets/admin-scripts.js', ['jquery'], WPAB_VERSION, true);
        }
    }

    public static function pre_get_posts($wp)
    {
        if($wp->query_vars['post_type'] == WPAB_POST_TYPE && !isset($wp->query_vars['tempered_query'])){
            if(is_singular()){
                $query = new WP_Query([
                    'post_type' => WPAB_POST_TYPE,
                    'name' => $wp->query_vars['name'],
                    'posts_per_page' => 1,
                    'tempered_query' => true
                ]);

                if($query->have_posts()){
                    global $wpdb;

                    $postsTableName = $wpdb->prefix . 'posts';
                    $executionsTableName = $wpdb->prefix . 'wpab_executions';

                    $post = $query->next_post();

                    /** @todo Exibir o conteúdo original da página ao concluir o teste (passar o prazo ou atingir a quantidade necessária de amostras) */

                    $test_subjects = WPAB_get_test_subjects($post->ID);

                    $subjectsCondition = implode(',', $test_subjects);

                    $sortQuery = <<<SQL
SELECT p.ID, COUNT(e.subject_id) AS occurrences FROM {$postsTableName} AS p LEFT JOIN {$executionsTableName} AS e ON (p.ID = e.subject_id) WHERE p.ID IN ({$subjectsCondition}) GROUP BY e.subject_id ORDER BY occurrences ASC LIMIT 1;
SQL;

                    $sortResult = $wpdb->get_col($sortQuery);

                    unset($wp->query_vars['wpab_test']);
                    unset($wp->query_vars['name']);

                    $wp->query_vars['post_type'] = 'page';
                    $wp->query_vars['p'] = array_shift($sortResult);

                    wp_enqueue_script('wpab-job', plugin_dir_url(__FILE__) . 'assets/wpab-job.js', ['jquery'], WPAB_VERSION, true);

                    $pluginVars = ['id' => uniqid('wpab_', true), 'test_id' => $post->ID, 'subject_id' => $wp->query_vars['p'], 'probe_url' => admin_url('admin-ajax.php'), 'triggers' => []];

                    /** Registrando no banco o início dos testes */
                    $startQuery = <<<SQL
INSERT INTO {$executionsTableName} (id, start_datetime, test_id, subject_id) VALUES ('{$pluginVars['id']}', NOW(), {$pluginVars['test_id']}, {$pluginVars['subject_id']});
SQL;

                    $wpdb->query($startQuery);

                    $triggerType = WPAB_get_trigger_type($post->ID);

                    if($triggerType == 'data_attribute'){
                        $pluginVars['triggers'][] = ['action' => 'click', 'js_event' => 'click', 'trigger_selector' => '[data-wpab-trigger="click"]', 'description_selector' => 'wpab-description'];
                        $pluginVars['triggers'][] = ['action' => 'submit', 'js_event' => 'submit', 'trigger_selector' => '[data-wpab-trigger="submit"]', 'description_selector' => 'wpab-description'];
                        $pluginVars['triggers'][] = ['action' => 'mousein', 'js_event' => 'mousein', 'trigger_selector' => '[data-wpab-trigger="mousein"]', 'description_selector' => 'wpab-description'];
                        $pluginVars['triggers'][] = ['action' => 'mouseout', 'js_event' => 'mouseout', 'trigger_selector' => '[data-wpab-trigger="mouseout"]', 'description_selector' => 'wpab-description'];
                        $pluginVars['triggers'][] = ['action' => 'visible', 'js_event' => 'visible', 'trigger_selector' => '[data-wpab-trigger="visible"]', 'description_selector' => 'wpab-description'];
                    } else {
                        $custromTriggers = WPAB_get_triggers($post->ID);

                        foreach($custromTriggers as $trigger){
                            $triggerRow = ['action' => $trigger['action'], 'js_event' => $trigger['action'], 'trigger_selector' => $trigger['selector'], 'description_selector' => $trigger['description']];

                            if(!empty($trigger['description'])){
                                $triggerRow['description'] = $trigger['description'];
                            } else {
                                $triggerRow['description_selector'] = 'wpab-description';
                            }

                            $pluginVars['triggers'][] = $triggerRow;
                        }
                    }

                    wp_localize_script('wpab-job', 'wpab_vars', $pluginVars);
                }
            }
        }

        return $wp;
    }

    public static function wp()
    {
        if(is_singular()){
            global $post, $wp;

            if($post->post_type == WPAB_POST_TYPE){
                return;
            }

            $originalPermalink = rtrim(get_permalink(), '/');

            $query = new WP_Query([
                'post_type' => WPAB_POST_TYPE,
                'posts_per_page' => -1,
                'meta_key' => 'wpab_test_subjects',
                'meta_value' => sprintf('i:%d;', $post->ID),
                'meta_compare' => 'LIKE',
                'tempered_query' => true
            ]);

            if($query->have_posts()){
                $currentPermalink = rtrim(home_url($wp->request), '/');

                if($originalPermalink == $currentPermalink){
                    wp_redirect(get_permalink($query->posts[0]->ID));
                    return;
                }
            }
        }
    }

    public static function probe()
    {
        if(isset($_POST['user_id']) && isset($_POST['userAction'])){
            global $wpdb;

            $stepTableName = $wpdb->prefix . 'wpab_steps';

            $stepName = $_POST['userAction'];
            $stepDescription = ((isset($_POST['description']))?$_POST['description']:'');
            $elementId = ((isset($_POST['id']))?$_POST['id']:'');

            $stepQuery = <<<SQL
INSERT INTO {$stepTableName} (execution_id, step_name, step_description, element_id, time) VALUES ('{$_POST['user_id']}', '{$stepName}', '{$stepDescription}', '{$elementId}', NOW());
SQL;

            $wpdb->query($stepQuery);

            /** Fecha a execução do teste */
            if($stepName == 'bye'){
                $closeQuery = <<<SQL
UPDATE {$wpdb->prefix}wpab_executions SET end_datetime = NOW() WHERE id = '{$_POST['user_id']}';
SQL;

                $wpdb->query($closeQuery);
            }
        }

        wp_die();
    }

}