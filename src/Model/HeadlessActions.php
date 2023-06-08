<?php

namespace Omniful\Admin\Model;

/** * The HeadlessActions class provides REST API endpoints for managing WordPress actions.
 *
 * @class HeadlessActions
 * @since 1.0.0
 */
if (!class_exists('HeadlessActions')) {
    class HeadlessActions
    {
        /**
         * Plugin options
         *
         * @var array
         */
        private $options;
        
        public function __construct()
        {
            add_action('wp_ajax_omniful-core-ajax', [$this, 'handle_ajax']);

            // INIT PLUGIN OPTIONS
            $this->options = get_option('omniful_plugin_options');
        }

        public function handle_ajax()
        {
            try {
                if (!$this->options['is_enabled']) {
                    return;
                }

                header('Content-Type: application/json');
                $user = wp_get_current_user();
                echo json_encode([
                    'httpCode' => 200,
                    "status" => true,
                    'name' => $user ? $user->data->display_name : 'there',
                    'message' => 'API call works!'
                ]);
            } catch (\Exception $e) {
                echo json_encode([
                    'httpCode' => 500,
                    "status" => true,
                    'message' => $e->getMessage()
                ]);
            }
            exit;
        }
    }
}