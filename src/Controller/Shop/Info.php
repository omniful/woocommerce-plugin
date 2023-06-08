<?php

namespace Omniful\Admin\Controller\Shop;

/**
 * The Info class provides REST API endpoints for managing WordPress plugin options.
 *
 * @class Info
 * @since 1.0.0
 */
if (!class_exists('Info')) {
    class Info
    {
        /**
         * Plugin shopInfo
         *
         * @var array
         */
        private $shopInfo;

        /**
         * Returns the stored plugin options
         *
         * @return \WP_REST_Response
         * 
         * @param \WP_REST_Request $request The REST request object
         */
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
                $user = get_user_by('email', get_bloginfo('admin_email'));

                $admin = [
                    'ID' => $user->ID,
                    'role' => $user->roles[0],
                    'email' => $user->user_email,
                    'name' => $user->display_name,
                    'user_url' => $user->user_url,
                    'username' => $user->user_login,
                    'nicename' => $user->user_nicename,
                    'user_registered' => $user->user_registered,
                ];
                $store = [
                    'name' => get_bloginfo('name'),
                    'url' => get_bloginfo('url'),
                    'charset' => get_bloginfo('charset'),
                    'language' => get_bloginfo('language'),
                    'wp_version' => get_bloginfo('version'),
                    'timezone' => get_option('timezone_string'),
                    'permalink_structure' => get_option('permalink_structure'),
                    'currency' => get_woocommerce_currency(),
                    'version' => defined('WC_VERSION') ? WC_VERSION : '',
                    'product_count' => wp_count_posts('product')->publish,
                    'order_count' => wp_count_posts('shop_order')->publish,
                    'active' => class_exists('woocommerce') ? true : false,
                    'store_url' => defined('WC_STORE_URL') ? WC_STORE_URL : '',
                    'db_version' => defined('WC_DB_VERSION') ? WC_DB_VERSION : '',
                    'api_enabled' => defined('WC_API_ENABLED') ? WC_API_ENABLED : '',
                ];

                $orderStatuses = $this->getOrderStatuses();

                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Success',
                    'data' => [
                        'admin' => $admin,
                        'store' => $store,
                        'orderStatuses' => $orderStatuses,
                    ],
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }


        /**
         * Retrieves all available WooCommerce order statuses
         *
         * @return array
         */
        private function getOrderStatuses()
        {
            $orderStatuses = wc_get_order_statuses();
            $formattedStatuses = [];

            foreach ($orderStatuses as $code => $title) {
                $formattedStatuses[] = [
                    'code' => $code,
                    'title' => $title,
                ];
            }

            return $formattedStatuses;
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