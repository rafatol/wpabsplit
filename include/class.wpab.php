<?php

namespace WpAbSplit;

/**
 * @package WpAbSplit
 */

class WpAbSplit {

    static $initialized = false;
    static $adminInitialized = false;

    static $splitInjected = false;

    public static function init()
    {
        if(!self::$initialized){
	        if(!session_id()){
		        session_start();
	        }

            self::$initialized = true;

            if(!License::isActivated()){
                return;
            }

            WpAbSplit::add_custom_post_type();
            WpAbSplit::add_custom_post_status();
        }
    }

    public static function plugin_activation()
    {
        global $wpdb;

	    self::executeOrder66();

        $charset_collate = $wpdb->get_charset_collate();

        /**
         * Tabela que armazena as execuções de teste
         */
        $executionsTableName = $wpdb->prefix . 'wpab_executions';

        $executionsSql = <<<SQL
CREATE TABLE {$executionsTableName} (
  `id` CHAR(28) NOT NULL,
  `client_platform` VARCHAR(6) NULL,
  `client_user_agent` VARCHAR(255) NULL,
  `start_datetime` DATETIME NULL,
  `conversion_datetime` DATETIME NULL,
  `end_datetime` DATETIME NULL,
  `test_id` BIGINT UNSIGNED NULL,
  `subject_id` BIGINT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_execution_test_idx` (`test_id`),
  INDEX `fk_execution_subject_idx` (`subject_id`),
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
  `execution_id` CHAR(28) NULL,
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
        self::executeOrder66();
        delete_option('wpab_plugin_db_version');
    }

	public static function executeOrder66()
	{
		global $wpdb;

		$executionsTableName = $wpdb->prefix . 'wpab_executions';
		$stepsTableName = $wpdb->prefix . 'wpab_steps';

		$wpdb->query(sprintf('DROP TABLE IF EXISTS %s', $stepsTableName));
		$wpdb->query(sprintf('DROP TABLE IF EXISTS %s', $executionsTableName));

		$postType = WPAB_POST_TYPE;

		$postDeleteQuery = <<<SQL
DELETE a,b,c
    FROM wp_posts a
    LEFT JOIN wp_term_relationships b
        ON (a.ID = b.object_id)
    LEFT JOIN wp_postmeta c
        ON (a.ID = c.post_id)
    WHERE a.post_type = '{$postType}';
SQL;

		$wpdb->query($postDeleteQuery);
	}

    public static function admin_init()
    {
        if(!self::$adminInitialized){
            self::$adminInitialized = true;
            WpAbSplit::add_custom_post_metaboxes();
        }
    }

    public static function admin_notices()
    {
        if(!License::isActivated()){
            include WPAB_PLUGIN_PATH . 'templates/admin_page/license_notice.php';
        }
    }

    public static function save_post($post_id)
    {
        if(WPAB_POST_TYPE != get_post_type($post_id)){
            global $wpdb;

            // carrega todos os testes que possuem o post salvo como control ou hypothesis
            $query = <<<SQL
SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('wpab_control_page', 'wpab_hypothesis_page') AND meta_value = {$post_id};
SQL;

            $testPostIds = $wpdb->get_col($query);

            if($testPostIds){
                foreach($testPostIds as $testPostId){
                    $controlPage = get_post(WPAB_get_control($testPostId));
                    $hypothesisPage = get_post(WPAB_get_hypothesis($testPostId));

                    if($controlPage){
                        update_post_meta($testPostId, 'wpab_control_page_title', $controlPage->post_title);
                        update_post_meta($testPostId, 'wpab_control_page_url', get_permalink($controlPage));
                    }

                    if($hypothesisPage){
                        update_post_meta($testPostId, 'wpab_hypothesis_page_title', $hypothesisPage->post_title);
                        update_post_meta($testPostId, 'wpab_hypothesis_page_url', get_permalink($hypothesisPage));
                    }
                }
            }

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

        $can_edit = !WPAB_test_started($post_id);

        if(isset($_POST['wpab_control_page']) && $can_edit){
            update_post_meta($post_id, 'wpab_control_page', $_POST['wpab_control_page']);

            $controlPage = get_post($_POST['wpab_control_page']);

            update_post_meta($post_id, 'wpab_control_page_title', $controlPage->post_title);
            update_post_meta($post_id, 'wpab_control_page_url', get_permalink($controlPage));
        }

	    if(isset($_POST['wpab_control_page_color']) && $can_edit){
		    update_post_meta($post_id, 'wpab_control_page_color', $_POST['wpab_control_page_color']);
	    }

        if(isset($_POST['wpab_hypothesis_page']) && $can_edit){
            update_post_meta($post_id, 'wpab_hypothesis_page', $_POST['wpab_hypothesis_page']);

            $hypothesisPage = get_post($_POST['wpab_hypothesis_page']);

            update_post_meta($post_id, 'wpab_hypothesis_page_title', $hypothesisPage->post_title);
            update_post_meta($post_id, 'wpab_hypothesis_page_url', get_permalink($hypothesisPage));
        }

        if(isset($_POST['wpab_hypothesis_page_color']) && $can_edit){
            update_post_meta($post_id, 'wpab_hypothesis_page_color', $_POST['wpab_hypothesis_page_color']);
        }

        if(isset($_POST['test_quantity'])){
			if($_POST['test_quantity'] % 2 != 0){
				$_POST['test_quantity'] = $_POST['test_quantity'] + 1;
			}

            update_post_meta($post_id, 'wpab_test_quantity', $_POST['test_quantity']);

			delete_post_meta($post_id, 'wpab_runs');
			delete_post_meta($post_id, 'wpab_progress');

			/** Flag indicando situação final */
	        $testQuantity = WPAB_get_test_quantity($post_id);
			$testRuns = WPAB_get_total_runs($post_id);

			if($testRuns < $testQuantity){
				update_post_meta($post_id, 'wpab_completed', 0);
			}
        }

        if(isset($_POST['trigger_selector']) && $can_edit){
            update_post_meta($post_id, 'wpab_trigger_selector', $_POST['trigger_selector']);
        }

        if(isset($_POST['trigger_event']) && $can_edit){
            update_post_meta($post_id, 'wpab_trigger_event', $_POST['trigger_event']);
        }

        return $post_id;
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

	public static function add_custom_post_status()
	{
		register_post_status('paused', [
			'label' => __('Paused'),
			'label_count' => _n_noop('Paused <span class="count">(%s)</span>', 'Paused <span class="count">(%s)</span>'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
		]);
	}

    public static function add_custom_post_metaboxes()
    {
        add_meta_box('ad_test_control', __('Control Page'), [WpAbSplit::class, 'create_test_control'], WPAB_POST_TYPE, 'advanced', 'high');
        add_meta_box('ad_test_hypothesis', __('Challenger Page'), [WpAbSplit::class, 'create_test_hypothesis'], WPAB_POST_TYPE, 'advanced', 'high');
        add_meta_box('ab_test_triggers', __('Event Trigger'), [WpAbSplit::class, 'create_test_triggers_box'], WPAB_POST_TYPE);

        /** Sidebar */
	    if(isset($_GET['post'])){
            add_meta_box('ab_test_about', __('About'), [WpAbSplit::class, 'create_about_box'], WPAB_POST_TYPE, 'side');
	    }

        add_meta_box('ab_test_options', __('Test Settings'), [WpAbSplit::class, 'create_options_box'], WPAB_POST_TYPE, 'side');
    }

    public static function create_test_control()
    {
        global $post;
        global $wpdb;

        $pagesToExcludeQuery = <<<SQL
SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id <> {$post->ID} AND ((meta_key = 'wpab_control_page') OR (meta_key = 'wpab_hypothesis_page')) AND post_id NOT IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'wpab_completed' AND meta_value = 1);
SQL;

        $pagesToExclude = array();
        $pagesToExcludeResult = $wpdb->get_results($pagesToExcludeQuery, ARRAY_A);

        if(count($pagesToExcludeResult)){
            foreach($pagesToExcludeResult as $pageInfo){
                $pagesToExclude[] = $pageInfo['meta_value'];
            }
        }

        $pagesToExclude = array_unique($pagesToExclude);

        $pages = get_pages(array(
            'exclude' => $pagesToExclude
        ));

        $selected_page = WPAB_get_control($post->ID);

        $can_edit = !WPAB_test_started($post->ID);
		$color = WPAB_get_control_color($post->ID);

        include WPAB_PLUGIN_PATH . 'templates/metabox/control.php';
    }

    public static function create_test_hypothesis()
    {
        global $post;

        $can_edit = !WPAB_test_started($post->ID);
	    $color = WPAB_get_hypothesis_color($post->ID);

        include WPAB_PLUGIN_PATH . 'templates/metabox/hypothesis.php';
    }

    public static function create_test_triggers_box()
    {
        global $post;

        $trigger_selector = WPAB_get_selector($post->ID);
        $trigger_event = WPAB_get_event($post->ID);

        $can_edit = !WPAB_test_started($post->ID);

        include WPAB_PLUGIN_PATH . 'templates/metabox/trigger.php';
    }

	public static function create_about_box()
	{
		global $post;

		$progress = WPAB_get_progress($post->ID);
		$totalQuantity = WPAB_get_test_quantity($post->ID);
		$totalRuns = WPAB_get_total_runs($post->ID);

		$reportUrl = admin_url('post.php?page=wpab_report&post=' . $post->ID);
		$isCompleted = get_post_meta($post->ID, 'wpab_completed', true);

		include WPAB_PLUGIN_PATH . 'templates/metabox/about.php';
	}

    public static function create_options_box()
    {
        global $post;

        $test_quantity = WPAB_get_test_quantity($post->ID);

        include WPAB_PLUGIN_PATH .'templates/metabox/options.php';
    }

    public static function admin_enqueue_scripts($hook)
    {
        global $post;

        if(in_array($hook, ['post-new.php', 'post.php']) && $post && $post->post_type == WPAB_POST_TYPE){
            wp_enqueue_style('wpab-admin-toast-css', WPAB_PLUGIN_URL . 'assets/plugins/jquery-toast-plugin/jquery.toast.min.css', [], '1.3.2');
            wp_enqueue_script('wpab-admin-toast-js', WPAB_PLUGIN_URL . 'assets/plugins/jquery-toast-plugin/jquery.toast.min.js', ['jquery'], '1.3.2', true);

            wp_enqueue_style('wpab-admin-select2-css', WPAB_PLUGIN_URL . 'assets/plugins/jquery-select2/css/select2.min.css', [], '4.1.0');
            wp_enqueue_script('wpab-admin-select2-js', WPAB_PLUGIN_URL . 'assets/plugins/jquery-select2/js/select2.full.min.js', ['jquery'], '4.1.0', true);

            $currentLocale = get_locale();
            $currentLocale = str_replace('_', '-', $currentLocale);

            if(file_exists(WPAB_PLUGIN_PATH . "assets/plugins/jquery-select2/js/i18n/{$currentLocale}.js")){
                wp_enqueue_script('wpab-admin-select2-i18n-js', WPAB_PLUGIN_URL . "assets/plugins/jquery-select2/js/i18n/{$currentLocale}.js", ['wpab-admin-select2-js'], '4.1.0', true);
            }

            wp_enqueue_style('wpab-admin-style', WPAB_PLUGIN_URL . 'assets/admin-stylesheet.css', [], WPAB_VERSION);
            wp_enqueue_script('wpab-admin-script', WPAB_PLUGIN_URL . 'assets/admin-scripts.js', ['jquery'], WPAB_VERSION, true);

			if(isset($_GET['post'])){
				$sidebarVars = [
					'custom_menu' => [
						[
							'label' => __('Test Report'),
							'url' => admin_url('post.php?page=wpab_report&post=' . $post->ID),
							'add_after' => '#menu-posts-wpab_test ul.wp-submenu li.wp-first-item'
						]
					]
				];

				wp_localize_script('wpab-admin-script', 'wpab_sidebar', $sidebarVars);
			}
        }

		if($hook == 'edit.php' && $post && $post->post_type == WPAB_POST_TYPE){
            wp_enqueue_style('wpab-admin-posts-style', WPAB_PLUGIN_URL . 'assets/admin-posts-stylesheet.css', [], WPAB_VERSION);
            wp_enqueue_script('wpab-admin-posts-script', WPAB_PLUGIN_URL . 'assets/admin-posts-scripts.js', ['jquery'], WPAB_VERSION, true);
		}

        if($hook == 'settings_page_wpab_settings'){
            wp_enqueue_style('wpab-admin-settings-style', WPAB_PLUGIN_URL . 'assets/admin-settings.css', [], WPAB_VERSION);
        }

		if($hook == 'admin_page_wpab_report'){
			wp_enqueue_style('wpab-admin-report-style', WPAB_PLUGIN_URL . 'assets/admin-report-stylesheet.css', [], WPAB_VERSION);
			wp_enqueue_script('wpab-admin-report-script', WPAB_PLUGIN_URL . 'assets/admin-report-scripts.js', ['jquery'], WPAB_VERSION, true);
			wp_enqueue_script('canvasjs-chart', WPAB_PLUGIN_URL . 'assets/plugins/canvasjs-chart/canvasjs.min.js', ['wpab-admin-report-script'], WPAB_VERSION, true);

			$sidebarVars = [
				'custom_menu' => [
					[
						'label' => __('Test Report'),
						'url' => admin_url('post.php?page=wpab_report&post=' . $_GET['post']),
						'add_after' => '#menu-posts-wpab_test ul.wp-submenu li.wp-first-item',
						'current' => true,
						'expand' => '#menu-posts-wpab_test '
					],
					[
						'label' => __('Edit Test'),
						'url' => admin_url('post.php?post=' . $_GET['post'] . '&action=edit'),
						'add_after' => '#menu-posts-wpab_test ul.wp-submenu li.wp-first-item',
					]
				],
				'custom_title' => sprintf(__('Test Report "%s"'), get_the_title($_GET['post']))
			];

			wp_localize_script('wpab-admin-report-script', 'wpab_sidebar', $sidebarVars);
		}
    }

    public static function pre_get_posts($wp)
    {
        if(!License::isActivated()){
            return $wp;
        }

        if(is_singular() && !is_admin() && !isset($wp->query_vars['tempered_query'])){
			if(isset($wp->query_vars['post_type']) && $wp->query_vars['post_type'] == WPAB_POST_TYPE && !self::ignoreThisRequest()){
				$queryArgs = [
					'name' => $wp->query_vars['name'],
					'post_type' => WPAB_POST_TYPE,
					'post_status' => 'publish',
					'numberposts' => 1,
					'tempered_query' => true
				];

				$testPostResult = get_posts($queryArgs);

				if(count($testPostResult)){
					$controlPage = WPAB_get_control($testPostResult[0]->ID);

					wp_redirect(get_permalink($controlPage));
					die();
				}
			}

            if(is_front_page() && $wp->query_vars['post_type'] == 'page' && is_numeric($wp->query_vars['post_parent']) && !self::ignoreThisRequest()){
                $currentPageId = $wp->query_vars['post_parent'];

                $showOnFront = get_option('show_on_front');
                $pageOnFront = get_option('page_on_front');

                if($showOnFront == 'page' && $pageOnFront == $currentPageId){
					delete_option('page_on_front_style');
                    $queryTest = self::queryTestPost($currentPageId);

                    if($queryTest->have_posts()){
                        $testPost = $queryTest->next_post();

                        $sortedPostId = self::prepareTestSubject($testPost, true);

                        if(false === $sortedPostId){
							update_option('page_on_front', WPAB_get_control($testPost->ID));

                            return $wp;
                        }

                        self::testPostLoad($testPost->ID, $currentPageId);
						update_option('page_on_front_style', $currentPageId);
                    }
                }

                return $wp;
            }

			if((!isset($wp->query_vars['post_type']) || (isset($wp->query_vars['post_type']) && $wp->query_vars['post_type'] === NULL)) && $wp->queried_object instanceof \WP_Post && $wp->queried_object->post_type == 'page'){
				$currentPageId = $wp->queried_object->ID;

				$queryTest = self::queryTestPost($currentPageId);

				if($queryTest->have_posts()){
					$testPost = $queryTest->next_post();

					if(self::ignoreThisRequest()){
						$postStyleId = get_post_meta($testPost->ID, 'wpab_post_style', true);

						$wp->queried_object = get_post($postStyleId);
						$wp->queried_object_id = $postStyleId;

						return $wp;
					}

					delete_post_meta($testPost->ID, 'wpab_post_style');

					$controlPage = WPAB_get_control($testPost->ID);

					$sortedPostId = self::prepareTestSubject($testPost);

					if(false === $sortedPostId){
						$wp->queried_object = get_post($controlPage);
						$wp->queried_object_id = $controlPage;

						return $wp;
					}

					update_post_meta($testPost->ID, 'wpab_post_style', $sortedPostId);
					self::testPostLoad($testPost->ID, $sortedPostId);

					$wp->queried_object = get_post($sortedPostId);
					$wp->queried_object_id = $sortedPostId;

					return $wp;
				}
			}
        }

        return $wp;
    }

	public static function post_row_actions($actions, $id)
	{
		global $post;

		if($post->post_type == WPAB_POST_TYPE){
            $controlPage = WPAB_get_control($post->ID);
            $hypotesisPage = WPAB_get_hypothesis($post->ID);

            if($controlPage && $hypotesisPage){
                $isCompleted = get_post_meta($post->ID, 'wpab_completed', true);

                if(!$isCompleted){
                    if(in_array($post->post_status, ['paused', 'draft', 'pending', 'future', 'private'])){
                        $actions = ['wpab_resume' => sprintf('<a href="%s" title="%s">%s</a>', admin_url('post.php?post=' . $post->ID . '&action=toggle_test_status'), esc_attr(__('Resume')), __('Resume'))] + $actions;
                    } else {
                        $actions = ['wpab_pause' => sprintf('<a href="%s" title="%s">%s</a>', admin_url('post.php?post=' . $post->ID . '&action=toggle_test_status'), esc_attr(__('Pause')), __('Pause'))] + $actions;
                    }

	                $actions = ['wpab_report' => __('Test Report')] + $actions;
                } else {
                    $actions = ['wpab_report' => sprintf('<a href="%s" title="%s">%s</a>', admin_url('post.php?page=wpab_report&post=' . $post->ID), esc_attr(__('Test Report')), __('Test Report'))] + $actions;
                }
            }

            if(isset($actions['inline hide-if-no-js'])){
                unset($actions['inline hide-if-no-js']);
            }

            if(isset($actions['view'])){
                unset($actions['view']);
            }
		}

		return $actions;
	}

    public static function probe()
    {
        global $wpdb;

        if(isset($_GET['method']) && $_GET['method'] == 'hypothesis'){
            $pagesToExcludeQuery = <<<SQL
SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id <> {$_GET['current_id']} AND ((meta_key = 'wpab_control_page') OR (meta_key = 'wpab_hypothesis_page')) AND post_id NOT IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'wpab_completed' AND meta_value = 1);
SQL;

            $pagesToExclude = array();
            $pagesToExcludeResult = $wpdb->get_results($pagesToExcludeQuery, ARRAY_A);

            if(count($pagesToExcludeResult)){
                foreach($pagesToExcludeResult as $pageInfo){
                    $pagesToExclude[] = $pageInfo['meta_value'];
                }
            }

            $pagesToExclude[] = $_GET['page_id'];
            $pagesToExclude = array_unique($pagesToExclude);

            $pagesArgs = [
                'exclude' => $pagesToExclude
            ];

            $pages = get_pages($pagesArgs);
            $returnPages = [];

            $selectedHypothesis = WPAB_get_hypothesis($_GET['current_id']);

            $returnPages[] = ['id' => null, 'label' => __('Select a hypothesis page'), 'selected' => false];

            foreach($pages as $p){
                $returnPages[] = ['id' => $p->ID, 'label' => $p->post_title, 'selected' => ($selectedHypothesis == $p->ID)];
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($returnPages);
            wp_die();
        }

        if(isset($_POST['user_id']) && isset($_POST['userAction'])){
            $stepTableName = $wpdb->prefix . 'wpab_steps';

            $stepName = $_POST['userAction'];
            $stepDescription = ((isset($_POST['description']))?$_POST['description']:'');
            $elementId = ((isset($_POST['id']))?$_POST['id']:'');

            $stepQuery = <<<SQL
INSERT INTO {$stepTableName} (execution_id, step_name, step_description, element_id, time) VALUES ('{$_POST['user_id']}', '{$stepName}', '{$stepDescription}', '{$elementId}', NOW());
SQL;

            $wpdb->query($stepQuery);

			if(!in_array($stepName, ['handshake', 'bye'])){
				$conversionUpdate = <<<SQL
UPDATE {$wpdb->prefix}wpab_executions SET conversion_datetime = NOW() WHERE id = '{$_POST['user_id']}' AND conversion_datetime IS NULL;
SQL;

				$wpdb->query($conversionUpdate);
			}

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

	public static function report()
	{
		if(isset($_GET['page']) && $_GET['page'] == 'completed'){
			$tests = new \WP_Query([
				'post_type' => WPAB_POST_TYPE,
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'order' => 'DESC',
				'orderby' => 'date',
				'meta_query' => [
					[
						'key' => 'wpab_completed',
						'value' => 1,
						'compare' => '='
					]
				]
			]);

			require WPAB_PLUGIN_PATH . 'templates/admin_page/report_list.php';

			return;
		}

		$postId = $_GET['post'];

		if(is_numeric($postId)){
			$post = get_post($postId);

			if($post && $post->post_type == WPAB_POST_TYPE){
				global $wpdb;

				$executionsTableName = $wpdb->prefix . 'wpab_executions';
				$conversionTableName = $wpdb->prefix . 'wpab_steps';

				$controlPageId = WPAB_get_control($post->ID);
				$challengerPageId = WPAB_get_hypothesis($post->ID);

				$query = <<<SQL
SELECT
    (SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.subject_id = {$controlPageId}) AS control_all_users_count,
    (SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.subject_id = {$challengerPageId}) AS challenge_all_users_count,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.subject_id = {$controlPageId} AND e.conversion_datetime IS NOT NULL) AS control_all_users_conversion,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.subject_id = {$challengerPageId} AND e.conversion_datetime IS NOT NULL) AS challenge_all_users_conversion,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.client_platform = 'small' AND e.subject_id = {$controlPageId}) AS control_mobile_users_count,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.client_platform = 'small' AND e.subject_id = {$challengerPageId}) AS challenge_mobile_users_count,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.client_platform = 'small' AND e.subject_id = {$controlPageId} AND e.conversion_datetime IS NOT NULL) AS control_mobile_users_conversion,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.client_platform = 'small' AND e.subject_id = {$challengerPageId} AND e.conversion_datetime IS NOT NULL) AS challenge_mobile_users_conversion,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.client_platform = 'medium' AND e.subject_id = {$controlPageId}) AS control_tablet_users_count,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.client_platform = 'medium' AND e.subject_id = {$challengerPageId}) AS challenge_tablet_users_count,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.client_platform = 'medium' AND e.subject_id = {$controlPageId} AND e.conversion_datetime IS NOT NULL) AS control_tablet_users_conversion,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.client_platform = 'medium' AND e.subject_id = {$challengerPageId} AND e.conversion_datetime IS NOT NULL) AS challenge_tablet_users_conversion,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.client_platform = 'large' AND e.subject_id = {$controlPageId}) AS control_desktop_users_count,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.client_platform = 'large' AND e.subject_id = {$challengerPageId}) AS challenge_desktop_users_count,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.client_platform = 'large' AND e.subject_id = {$controlPageId} AND e.conversion_datetime IS NOT NULL) AS control_desktop_users_conversion,
	(SELECT COALESCE(COUNT(e.id), 0) FROM {$executionsTableName} AS e WHERE e.test_id = {$post->ID} AND e.client_platform = 'large' AND e.subject_id = {$challengerPageId} AND e.conversion_datetime IS NOT NULL) AS challenge_desktop_users_conversion
SQL;

				$result = $wpdb->get_row($query);

				$resultArray = [
					'all' => [
						'control' => [
							'sample' => $result->control_all_users_count,
							'conversion' => $result->control_all_users_conversion
						],
						'challenge' => [
							'sample' => $result->challenge_all_users_count,
							'conversion' => $result->challenge_all_users_conversion
						]
					],
					'small' => [
						'control' => [
							'sample' => $result->control_mobile_users_count,
							'conversion' => $result->control_mobile_users_conversion
						],
						'challenge' => [
							'sample' => $result->challenge_mobile_users_count,
							'conversion' => $result->challenge_mobile_users_conversion
						]
					],
					'medium' => [
						'control' => [
							'sample' => $result->control_tablet_users_count,
							'conversion' => $result->control_tablet_users_conversion
						],
						'challenge' => [
							'sample' => $result->challenge_tablet_users_count,
							'conversion' => $result->challenge_tablet_users_conversion
						]
					],
					'large' => [
						'control' => [
							'sample' => $result->control_desktop_users_count,
							'conversion' => $result->control_desktop_users_conversion
						],
						'challenge' => [
							'sample' => $result->challenge_desktop_users_count,
							'conversion' => $result->challenge_desktop_users_conversion
						]
					]
				];

				$chartData = [];

				/** Conversion Rate */
				foreach($resultArray as $platform => $platformData){
					$chartData[$platform] = ['dataPoints' => []];
					$emptyData = 0;

					foreach($platformData as $subject => $subjectData){
						$dataPoint = ['indexLabel' => (($subject == 'control')?__('Control'):__('Challenger')), 'y' => 0];

						if(!$subjectData['sample']){
							$resultArray[$platform][$subject]['conversion_rate'] = 0;
							$chartData[$platform]['dataPoints'][] = $dataPoint;
						}

                        if(!$subjectData['conversion']){
	                        $resultArray[$platform][$subject]['conversion_rate'] = 0;

                            $emptyData++;
                            continue;
                        }

						$resultArray[$platform][$subject]['conversion_rate'] = $dataPoint['y'] = (($subjectData['conversion'] / $subjectData['sample']) * 100);
						$chartData[$platform]['dataPoints'][] = $dataPoint;
					}

					if($emptyData == 2){
						unset($chartData[$platform]);
					}
				}

				/** Uplift */
				foreach($resultArray as $platform => $platformData){
					foreach($platformData as $subject => $subjectData){
						if($subject == 'control'){
							$resultArray[$platform][$subject]['uplift'] = __('Baseline');
							continue;
						}

						$controlConversionRate = $resultArray[$platform]['control']['conversion_rate'];
						$challengeConversionRate = $resultArray[$platform]['challenge']['conversion_rate'];

						if($controlConversionRate == 0){
							$resultArray[$platform][$subject]['uplift'] = __('N/A');
							continue;
						}

						$resultArray[$platform][$subject]['uplift'] = (($challengeConversionRate - $controlConversionRate) / $controlConversionRate) * 100;

						if($resultArray[$platform][$subject]['uplift'] > 0){
							$resultArray[$platform][$subject]['uplift'] = sprintf('+%s%%', number_format_i18n($resultArray[$platform][$subject]['uplift'], 2));
						} else {
							$resultArray[$platform][$subject]['uplift'] = sprintf('%s%%', number_format_i18n($resultArray[$platform][$subject]['uplift'], 2));
						}
					}
				}

				require WPAB_PLUGIN_PATH . 'vendor/autoload.php';

				foreach($resultArray as $platform => $platformData){
					$variations = [
						new \BenTools\SplitTestAnalyzer\Variation('control', $platformData['control']['sample'], $platformData['control']['conversion']),
						new \BenTools\SplitTestAnalyzer\Variation('challenge', $platformData['challenge']['sample'], $platformData['challenge']['conversion'])
					];

					$predictor = \BenTools\SplitTestAnalyzer\SplitTestAnalyzer::create()->withVariations(...$variations);

					foreach($predictor->getResult() as $key => $value){
						$resultArray[$platform][$key]['p2bb'] = $value;
						$resultArray[$platform][$key]['winner'] = false;
					}

					if($predictor->getBestVariation()){
						$resultArray[$platform][$predictor->getBestVariation()->getKey()]['winner'] = true;
					}

					if(!isset($chartData[$platform])){
						$resultArray[$platform]['empty'] = true;
					}
				}

				$controlColor = WPAB_get_control_color($post->ID);
				$hypotesisColor = WPAB_get_hypothesis_color($post->ID);

				wp_localize_script('wpab-admin-report-script', 'wpab_chart_colors', ['control_color' => $controlColor, 'hypotesis_color' => $hypotesisColor]);
				wp_localize_script('wpab-admin-report-script', 'wpab_chart_data', $chartData);

                $controlPage = get_post(WPAB_get_control($post->ID));

                if($controlPage){
                    $controlPageTitle = $controlPage->post_title;
                    $controlPageUrl = get_permalink($controlPage);
                } else {
                    $controlPageTitle = get_post_meta($postId, 'wpab_control_page_title', true);
                    $controlPageUrl = get_post_meta($postId, 'wpab_control_page_url', true);
                }


                $hypotesisPage = get_post(WPAB_get_hypothesis($post->ID));

                if($hypotesisPage){
                    $challengerPageTitle = $hypotesisPage->post_title;
                    $challengerPageUrl = get_permalink($hypotesisPage);
                } else {
                    $challengerPageTitle = get_post_meta($postId, 'wpab_hypothesis_page_title', true);
                    $challengerPageUrl = get_post_meta($postId, 'wpab_hypothesis_page_url', true);
                }

				$totalRuns = WPAB_get_total_runs($post->ID);

				if($totalRuns){
					$lastRunDateQuery = <<<SQL
SELECT end_datetime FROM {$executionsTableName} WHERE test_id = {$post->ID} ORDER BY end_datetime DESC LIMIT 1;
SQL;

					$lastRunDateResult = $wpdb->get_row($lastRunDateQuery);
					$lastRunDateResult = new \DateTime($lastRunDateResult->end_datetime);
				}

				include WPAB_PLUGIN_PATH . 'templates/admin_page/report.php';
				return;
			}
		}

		wp_redirect(admin_url('edit.php?post_type=' . WPAB_POST_TYPE));
		exit;
	}

	public static function manage_wpab_test_posts_columns($columns)
	{
		$removedColumns = [];
		$removedColumns['cb'] = $columns['cb'];
		$removedColumns['title'] = $columns['title'];
		$removedColumns['status'] = __('Status');
		$removedColumns['progress'] = __('Progress');

		unset($columns['cb']);
		unset($columns['title']);

		return array_merge($removedColumns, $columns);
	}

	public static function manage_wpab_test_posts_custom_column($column, $post_id)
	{
		if($column == 'progress'){
			$progress = WPAB_get_progress($post_id);
			$totalRuns = WPAB_get_total_runs($post_id);
			$totalQuantity = WPAB_get_test_quantity($post_id);

			include WPAB_PLUGIN_PATH . 'templates/column/progress.php';
			return;
		}

		if($column == 'status'){
			$completedStatus = get_post_meta($post_id, 'wpab_completed', true);

			if($completedStatus){
				echo __('Completed');
				return;
			}

			$post = get_post($post_id);

			if(in_array($post->post_status, ['paused', 'draft', 'pending', 'future', 'private'])){
                $controlPage = WPAB_get_control($post_id);
                $hypotesisPage = WPAB_get_hypothesis($post_id);

                if(!$controlPage || !$hypotesisPage){
                    echo __('Draft');
                    return;
                }

				echo __('Paused');
				return;
			}

			echo __('Running');
			return;
		}
	}

	public static function manage_edit_wpab_test_sortable_columns($columns)
	{
		$columns['progress'] = 'progress';
		return $columns;
	}

	public static function admin_menu()
	{
		add_submenu_page('edit.php?post_type=' . WPAB_POST_TYPE, __('All Reports'), __('All Reports'), 'manage_options', 'completed', [WpAbSplit::class, 'report'], 1);
		add_submenu_page(null, __('Test Report'), __('Test Report'), 'manage_options', 'wpab_report', [WpAbSplit::class, 'report']);

        add_submenu_page('options-general.php', __('WP A/B Split Settings'), __('WP A/B Split Settings'), 'manage_options', 'wpab_settings', [WpAbSplit::class, 'settings']);
	}

	public static function restrict_manage_posts()
	{
		$screen = get_current_screen();

		if($screen->id == sprintf('edit-%s', WPAB_POST_TYPE)){
			include WPAB_PLUGIN_PATH . 'templates/admin_page/status_filter.php';
		}
	}

	public static function parse_query(\WP_Query $query)
	{
		global $pagenow;

		$post_type = (isset($_GET['post_type'])) ? $_GET['post_type'] : 'post';

		if($post_type == WPAB_POST_TYPE && $pagenow == 'edit.php' && isset($_GET['test_status']) && !empty($_GET['test_status'])){
			switch($_GET['test_status']){
				case 'running':
						$query->set('post_status', 'publish');
						$query->set('meta_query', [
							'relation' => 'OR',
							[
								'key' => 'wpab_completed',
								'value' => 1,
								'compare' => '!='
							],
							[
								'key' => 'wpab_completed',
								'compare' => 'NOT EXISTS'
							]
						]);
					break;
				case 'paused':
						$query->set('post_status', ['paused', 'draft', 'pending', 'future', 'private']);
						$query->set('meta_query', [
							'relation' => 'OR',
							[
								'key' => 'wpab_completed',
								'value' => 1,
								'compare' => '!='
							],
							[
								'key' => 'wpab_completed',
								'compare' => 'NOT EXISTS'
							]
						]);
					break;
				case 'completed':
						$query->set('meta_query', [
							[
								'key' => 'wpab_completed',
								'value' => 1,
								'compare' => '='
							]
						]);
					break;
			}
		}
	}

	public static function views_edit_wpab_test($views)
	{
		if(isset($views['publish'])){
			unset($views['publish']);
		}

		return $views;
	}

    public static function toggle_test_status($post_id)
    {
        $post = get_post($post_id);

        if($post->post_status == 'publish'){
            wp_update_post(['ID' => $post_id, 'post_status' => 'pending']);
        } else {
            $controlPage = WPAB_get_control($post_id);
            $hypothesisPage = WPAB_get_hypothesis($post_id);

            if($controlPage && $hypothesisPage){
                wp_update_post(['ID' => $post_id, 'post_status' => 'publish']);
            }
        }

        wp_redirect(admin_url('edit.php?post_type=' . WPAB_POST_TYPE));
        die();
    }

    public static function display_post_states($states, $post)
    {
        if($post->post_type == WPAB_POST_TYPE && isset($states['pending'])){
            $controlPage = WPAB_get_control($post->ID);
            $hypothesisPage = WPAB_get_hypothesis($post->ID);

            if($controlPage && $hypothesisPage){
                unset($states['pending']);
            }
        }

        return $states;
    }

    public static function settings()
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(isset($_POST[License::LICENSE_KEY])){
                License::updateLicenseKey($_POST[License::LICENSE_KEY]);
                wp_redirect(admin_url('options-general.php?page=wpab_settings'));
            }
        }

        include WPAB_PLUGIN_PATH . 'templates/admin_page/settings.php';
    }

	private static function queryTestPost($postId)
	{
		return new \WP_Query([
			'post_type' => WPAB_POST_TYPE,
			'posts_per_page' => 1,
			'post_status' => 'publish',
			'order' => 'DESC',
			'orderby' => 'date',
			'meta_query' => [
				'relation' => 'AND',
				[
					'relation' => 'OR',
					[
						'key' => 'wpab_control_page',
						'value' => $postId,
						'compare' => '='
					],
					[
						'key' => 'wpab_hypothesis_page',
						'value' => $postId,
						'compare' => '='
					]
				],
				[
					'relation' => 'OR',
					[
						'key' => 'wpab_completed',
						'value' => 1,
						'compare' => '!='
					],
					[
						'key' => 'wpab_completed',
						'compare' => 'NOT EXISTS'
					]
				]
			],
			'tempered_query' => true
		]);
	}

	private static function prepareTestSubject(\WP_Post $post, $fromStaticPage = false)
	{
		global $wpdb;

		$postsTableName = $wpdb->prefix . 'posts';
		$executionsTableName = $wpdb->prefix . 'wpab_executions';

		$testQuantity = WPAB_get_test_quantity($post->ID);
		$testRuns = WPAB_get_total_runs($post->ID);

		$controlPage = WPAB_get_control($post->ID);
		$hypotesisPage = WPAB_get_hypothesis($post->ID);

		if($testRuns >= $testQuantity){
			self::updateStaticPage($controlPage, $hypotesisPage);
			return false;
		}

		$test_subjects = [$controlPage, $hypotesisPage];

		$subjectsCondition = implode(',', $test_subjects);

		$sortQuery = <<<SQL
SELECT p.ID, COUNT(e.subject_id) AS occurrences FROM {$postsTableName} AS p LEFT JOIN {$executionsTableName} AS e ON (p.ID = e.subject_id) WHERE p.ID IN ({$subjectsCondition}) GROUP BY e.subject_id ORDER BY occurrences ASC LIMIT 1;
SQL;

		$sortResult = $wpdb->get_col($sortQuery);
		$sortedPostId = array_shift($sortResult);

		/** Salvando o progresso do teste em meta-data */
		update_post_meta($post->ID, 'wpab_runs', ($testRuns + 1));
		update_post_meta($post->ID, 'wpab_progress', ceil((($testRuns + 1) / $testQuantity) * 100));

		/** Flag indicando situação final */
		update_post_meta($post->ID, 'wpab_completed', (($testRuns + 1 >= $testQuantity)?1:0));

        if(!$fromStaticPage){
		    self::updateStaticPage($controlPage, $hypotesisPage, $sortedPostId);
        } else {
		    self::updateStaticPage($controlPage, $hypotesisPage, get_option('page_on_front'));
        }

		return $sortedPostId;
	}

	private static function testPostLoad($testId, $subjectId)
	{
		global $wpdb;

		$executionsTableName = $wpdb->prefix . 'wpab_executions';

		/** MobileDetect */
		require_once WPAB_PLUGIN_PATH . 'trd_party/Mobile_Detect.php';

		$mobileDetect = new \Mobile_Detect();

		$clientPlatform = WPAB_PLATFORM_LARGE;
		$clientUserAgent = $mobileDetect->getUserAgent();

		if($mobileDetect->isMobile()){
			$clientPlatform = WPAB_PLATFORM_SMALL;
		}

		if($mobileDetect->isTablet()){
			$clientPlatform = WPAB_PLATFORM_MEDIUM;
		}

		wp_enqueue_script('wpab-job', WPAB_PLUGIN_URL . 'assets/wpab-job.js', ['jquery'], WPAB_VERSION, true);

		$pluginVars = ['id' => uniqid('wpab_', true), 'test_id' => $testId, 'subject_id' => $subjectId, 'probe_url' => admin_url('admin-ajax.php'), 'triggers' => []];

		/** Registrando no banco o início dos testes */
		$startQuery = <<<SQL
INSERT INTO {$executionsTableName} (id, client_platform, client_user_agent, start_datetime, test_id, subject_id) VALUES ('{$pluginVars['id']}', '{$clientPlatform}', '{$clientUserAgent}', NOW(), {$pluginVars['test_id']}, {$pluginVars['subject_id']});
SQL;

		$wpdb->query($startQuery);

		$pluginVars['triggers'][] = ['action' => WPAB_get_event($testId), 'js_event' => WPAB_get_event($testId), 'trigger_selector' => WPAB_get_selector($testId)];

		wp_localize_script('wpab-job', 'wpab_vars', $pluginVars);
	}

	private static function updateStaticPage($controlPageId, $hypotesisPageId, $sortedValue = null)
	{
		$showOnFront = get_option('show_on_front');
		$pageOnFront = get_option('page_on_front');

		if($showOnFront != 'page'){
			return;
		}

		if(!in_array($pageOnFront, [$controlPageId, $hypotesisPageId])){
			return;
		}

		if(null !== $sortedValue){
			if($sortedValue == $controlPageId){
				update_option('page_on_front', $hypotesisPageId);
				update_option('page_on_front_style', $controlPageId);

				return;
			}

			update_option('page_on_front', $controlPageId);
			update_option('page_on_front_style', $hypotesisPageId);
			return;
		}

		update_option('page_on_front', $controlPageId);
		delete_option('page_on_front_style');
	}

    private static function ignoreThisRequest()
    {
        return isset($_GET['xlink']);
    }

	public static function pre_option_page_on_front()
	{
		if(self::ignoreThisRequest()){
			$optionValue = get_option('page_on_front_style');

			if(is_numeric($optionValue)){
				return $optionValue;
			}
		}

		global $wpdb;

		$optionQuery = <<<SQL
SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'page_on_front';
SQL;

		$optionValue = $wpdb->get_var($optionQuery);

		if(is_numeric($optionValue)){
			return $optionValue;
		}

		return false;
	}

}