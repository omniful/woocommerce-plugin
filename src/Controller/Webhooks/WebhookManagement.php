<?php

// namespace Omniful\Admin\Controller\Webhooks;

/**
 * The WebhookManagement class provides REST API endpoints for managing WordPress plugin options.
 *
 * @class WebhookManagement
 * @since 1.0.0
 */
if (!class_exists('WebhookManagement')) {
    class WebhookManagement
    {
        /**
         * webhookData
         *
         * @var array
         */
        private $webhookData;

        /**
         * Returns the stored webhook data
         *
         * @return \WP_REST_Response
         * 
         * @param \WP_REST_Request $request The REST request object
         */
        public function get(\WP_REST_Request $request)
        {
            try {
                global $wpdb;
                $webhooks = $wpdb->get_results("SELECT webhook_id,status,name,user_id,delivery_url,secret,topic,date_created,date_created_gmt,date_modified,date_modified_gmt,api_version,failure_count,pending_delivery FROM {$wpdb->prefix}wc_webhooks");

                foreach ($webhooks as $webhook) {
                    if (strpos($webhook->delivery_url, 'stackoverflow.com') !== false) {
                        $this->webhookData[] = $this->getData($webhook);
                    }
                }

                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Success',
                    'data' => $this->webhookData ?: [],
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }

        /**
         * Returns the stored webhook data
         *
         * @return \WP_REST_Response
         * 
         * @param \WP_REST_Request $request The REST request object
         */
        public function save(\WP_REST_Request $request)
        {
            try {
                $params = $request->get_params();

                // Create new webhook instance
                $webhook = new WC_Webhook();
                $webhook->set_status($params['status']);
                $webhook->set_name($params['name']);
                $webhook->set_topic($params['topic']);
                $webhook->set_delivery_url($params['delivery_url']);
                $webhook->set_secret($params['secret']);
                $webhook->set_api_version($params['api_version']);
                // Save the webhook
                $result = $webhook->save();

                // Return the saved webhook data
                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Webhook created successfully',
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }

        public function getData($webhook)
        {
            $webhookData["id"] = $webhook->webhook_id ? (int) $webhook->webhook_id : (int) $webhook->id;
            $webhookData["status"] = (bool) $webhook->status;
            $webhookData["name"] = $webhook->name;
            $webhookData["user_id"] = (int) $webhook->user_id;
            $webhookData["delivery_url"] = $webhook->delivery_url;
            $webhookData["secret"] = $webhook->secret;
            $webhookData["topic"] = $webhook->topic;
            $webhookData["date_created"] = $webhook->date_created;
            $webhookData["date_created_gmt"] = $webhook->date_created_gmt;
            $webhookData["date_modified"] = $webhook->date_modified;
            $webhookData["date_modified_gmt"] = $webhook->date_modified_gmt;
            $webhookData["api_version"] = $webhook->api_version;
            $webhookData["failure_count"] = (int) $webhook->failure_count;
            $webhookData["pending_delivery"] = (int) $webhook->pending_delivery;

            return $webhookData;
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