<?php

namespace Omniful\Admin\Controller\Sales;

/**
 * The PluginOptions class provides REST API endpoints for managing WordPress plugin options.
 *
 * @class PluginOptions
 * @since 1.0.0
 */
if (!class_exists('PluginOptions')) {
    class PluginOptions
    {
        /**
         * Plugin options
         *
         * @var array
         */
        private $options;

        /**
         * Constructor function that initializes plugin options
         *
         * @return void
         */
        public function __construct()
        {
            // INIT PLUGIN OPTIONS
            $this->options = get_option('omniful_plugin_options');
        }

        /**
         * Returns the stored plugin options
         *
         * @return \WP_REST_Response
         * 
         * @param \WP_REST_Request $request The REST request object
         */
        public function get(\WP_REST_Request $request)
        {
            try {

                $this->options = [
                    "isEnabled" => isset($this->options["is_enabled"]) ? (bool) $this->options["is_enabled"] : false,
                    "webhookUrl" => isset($this->options["webhook_url"]) ? $this->options["webhook_url"] : "",
                    "accessToken" => isset($this->options["access_token"]) ? $this->options["access_token"] : "",
                    "workspaceId" => isset($this->options["workspace_id"]) ? $this->options["workspace_id"] : "",
                    "webhookToken" => isset($this->options["webhook_token"]) ? $this->options["webhook_token"] : "",
                    "enableDebugging" => isset($this->options["enable_debugging"]) ? (bool) $this->options["enable_debugging"] : false,
                ];

                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Success',
                    'data' => $this->options ?: [],
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }

        /**
         * Saves the submitted plugin options
         *
         * @param \WP_REST_Request $request The REST request object
         *
         * @return \WP_REST_Response
         */
        public function save(\WP_REST_Request $request)
        {
            try {
                $data = $request->get_params();

                if (!empty($this->options)) {
                    $this->options['is_enabled'] = isset($data['isEnabled']) ? (bool) $data['isEnabled'] : $this->options['is_enabled'];
                    $this->options['webhook_url'] = isset($data['webhookUrl']) ? sanitize_text_field($data['webhookUrl']) : $this->options['webhook_url'];
                    $this->options['access_token'] = isset($data['accessToken']) ? sanitize_text_field($data['accessToken']) : $this->options['access_token'];
                    $this->options['enable_debugging'] = isset($data['enableDebugging']) ? (bool) $data['enableDebugging'] : $this->options['enable_debugging'];
                    $this->options['workspace_id'] = isset($data['workspaceId']) ? sanitize_text_field($data['workspaceId']) : $this->options['workspace_id'];
                    $this->options['webhook_token'] = isset($data['webhookToken']) ? sanitize_text_field($data['webhookToken']) : $this->options['webhook_token'];
                } else {
                    $this->options = array(
                        'is_enabled' => isset($data['isEnabled']) ? (bool) $data['isEnabled'] : '',
                        'enable_debugging' => isset($data['accessToken']) ? (bool) $data['accessToken'] : '',
                        'webhook_url' => isset($data['webhookUrl']) ? sanitize_text_field($data['webhookUrl']) : '',
                        'workspace_id' => isset($data['workspaceId']) ? sanitize_text_field($data['workspaceId']) : '',
                        'webhook_token' => isset($data['webhookToken']) ? sanitize_text_field($data['webhookToken']) : '',
                        'access_token' => isset($data['enableDebugging']) ? sanitize_text_field($data['enableDebugging']) : '',
                    );
                }

                update_option('omniful_plugin_options', $this->options);

                $responseData = array(
                    'httpCode' => 200,
                    'status' => 'success',
                    'message' => 'Options saved successfully.',
                    'data' => $this->options
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }


        /**
         * Handles any errors that occur during request processing
         *
         * @param \Exception $e The exception object
         *
         * @return \WP_Error
         */
        public function handle_error($e)
        {
            if ($e instanceof Exception) {
                $message = $e->getMessage();
            } else {
                $message = $e;
            }

            $responseData[] = array(
                'httpCode' => 500,
                'status' => 'error',
                'message' => $message,
            );

            return $responseData;
        }
    }
}