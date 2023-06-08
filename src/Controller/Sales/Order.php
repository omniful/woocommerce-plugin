<?php

namespace Omniful\Admin\Controller\Sales;

/** * The Order class provides REST API endpoints for managing WordPress orders.
 *
 * @class Order
 * @since 1.0.0
 */
if (!class_exists('Order')) {
    class Order
    {
        const IGNORED_STATUSES = [
            "refunded",
            "cancelled",
            "failed",
            "delivered",
            "completed",
            "pending",
            "shipped",
        ];

        public function getOrders(\WP_REST_Request $request)
        {
            try {
                $orderData = [];
                $params = $request->get_params();

                $page = (int) $request->get_param('page') ?: 1;
                $pageLimit = (int) $request->get_param('limit') ?: 200;
                $status = $params['status'] ? $this->processStatusArray(array($params['status'])) : $this->processStatusArray(array('pending', 'processing', 'completed', 'on-hold'));


                $args = array(
                    'post_type' => 'shop_order',
                    'post_status' => $status,
                    'numberposts' => -1,
                    'paged' => $page,
                    'posts_per_page' => $pageLimit,
                );

                // Get paginated orders
                $orders = get_posts($args);

                foreach ($orders as $order) {
                    // Get the order status
                    $orderStatus = $order->post_status;
                    // Check if the order status matches the given status
                    if (in_array($orderStatus, $status, true) || in_array('wc-' . $orderStatus, $status, true)) {
                        $orderData[] = $this->getOrderData($order->ID);
                    }
                }

                // Get total count of orders (without pagination)
                $totalOrders = count($orderData);

                $pageInfo = array(
                    'current_page' => $page,
                    'per_page' => $pageLimit,
                    'total_count' => $totalOrders,
                    'total_pages' => ceil($totalOrders / $pageLimit),
                );

                try {
                    $responseData = [
                        'httpCode' => 200,
                        'status' => true,
                        'message' => 'Success',
                        'data' => $orderData,
                        'page_info' => $pageInfo,
                    ];

                    return new \WP_REST_Response($responseData, 200);
                } catch (\Exception $e) {
                    $responseData = [
                        'httpCode' => 500,
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];

                    return $responseData;
                }
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }

            exit;
        }

        // @todo add hubId and fulfillment status same as Magento
        public function updateOrderStatus(\WP_REST_Request $request)
        {
            try {
                $orderId = $request->get_param('id');
                $new_status = $request->get_param('status');

                // Check if status is "shipped" or "delivered"
                if ($new_status === 'shipped' || $new_status === 'delivered') {
                    $order = wc_get_order($orderId);

                    // Check if the order has shipments
                    $hasShipment = $this->isOrderHasShipment($orderId);

                    if (!$hasShipment) {
                        throw new \Exception('Cannot update status. Order does not have any shipments.');
                    }
                }

                // Update order status
                $order = wc_get_order($orderId);
                $order->update_status($new_status);

                $responseData = array(
                    'httpCode' => 200,
                    'status' => 'success',
                    'message' => 'Order status updated successfully.',
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }

        public function getOrderById(\WP_REST_Request $request)
        {
            try {
                $orderId = $request->get_param('id');

                if (!$orderId) {
                    throw new \Exception('Order ID not provided.');
                }

                $orderData = $this->getOrderData($orderId);

                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Success',
                    'data' => $orderData,
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }

        public function addTrackingInfo(\WP_REST_Request $request)
        {
            try {
                $orderId = $request->get_param('id');
                $tracingLink = $request->get_param('tracking_link');
                $trackingNumber = $request->get_param('tracking_number');
                $shippingLabelPdf = $request->get_param('shipping_label_pdf');
                $overrideExistingData = $request->get_param('override_exist_data');

                // Validate input data
                if (empty($orderId) || empty($trackingNumber) || empty($tracingLink) || empty($shippingLabelPdf)) {
                    return new \WP_Error('invalid_data', __('Invalid/Missing data provided.', 'omniful_core'), array('status' => 400));
                }

                // Check order status
                $order = wc_get_order($orderId);
                $status = $order->get_status();

                // Check if the order status allows adding tracking information
                if (in_array($status, self::IGNORED_STATUSES)) {
                    return new \WP_Error('invalid_status', __('Cannot add tracking information to an order with the %s status. Please update the status to Processing First.', 'omniful_core'), $status, array('status' => 400));
                }

                // Validate tracking link
                $urlPattern = '/^(http|https):\/\/[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,6})+(\/[^\s]*)?$/';
                if (!preg_match($urlPattern, $tracingLink)) {
                    return new \WP_Error('invalid_tracking_link', __('Invalid tracking link provided. Please provide a valid website link.', 'omniful_core'), array('status' => 400));
                }

                // Check if existing tracking information should be overridden
                $hasShipment = $this->isOrderHasShipment($orderId);

                if ($hasShipment) {
                    if ($overrideExistingData === true) {
                        // Update order meta
                        update_post_meta($orderId, '_tracing_link', sanitize_text_field($tracingLink));
                        update_post_meta($orderId, '_tracking_number', sanitize_text_field($trackingNumber));
                        update_post_meta($orderId, '_shipping_label_pdf', sanitize_text_field($shippingLabelPdf));
                        $message = __('Order tracking information updated successfully.', 'omniful_core');
                    } else {
                        return new \WP_Error('existing_data', __('The order already has tracking information. If you wish to override it, please set "override_exist_data" to true.', 'omniful_core'), array('status' => 400));
                    }
                } else {
                    // Add tracking information only if it doesn't already exist
                    if (isset($trackingNumber)) {
                        update_post_meta($orderId, '_tracking_number', sanitize_text_field($trackingNumber));
                    }
                    if (isset($tracingLink)) {
                        update_post_meta($orderId, '_tracing_link', sanitize_text_field($tracingLink));
                    }
                    if (isset($shippingLabelPdf)) {
                        update_post_meta($orderId, '_shipping_label_pdf', sanitize_text_field($shippingLabelPdf));
                    }

                    $message = __('Order tracking information added successfully.', 'omniful_core');
                }

                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => $message,
                );

                return new \WP_REST_Response($responseData, 200);

            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }

        public function getOrderData($orderId)
        {
            $order = wc_get_order($orderId);

            if (!$order) {
                throw new \Exception('Order not found.');
            }

            $orderItems = [];

            // Get the shipping method ID
            $shipping_method_id = $order->get_shipping_method();

            // Get all available shipping methods
            $shipping_methods = WC()->shipping()->get_shipping_methods();

            // Get the shipping method label for the shipping method ID
            if (isset($shipping_methods[$shipping_method_id])) {
                $shipping_method_label = $shipping_methods[$shipping_method_id]->get_label();
            } else {
                $shipping_method_label = '';
            }

            $existingTrackingLink = get_post_meta($orderId, '_tracing_link', true);
            $existingTrackingNumber = get_post_meta($orderId, '_tracking_number', true);
            $existingShippingLabelPdf = get_post_meta($orderId, '_shipping_label_pdf', true);

            $shipmentTracking = [
                'tracing_link' => (string) $existingTrackingLink,
                'tracking_number' => (string) $existingTrackingNumber,
                'shipping_label_pdf' => (string) $existingShippingLabelPdf,
            ];

            $customerData = [
                'first_name' => (string) $order->get_billing_first_name(),
                'last_name' => (string) $order->get_billing_last_name(),
                'email' => (string) $order->get_billing_email(),
                'phone' => (string) $order->get_billing_phone(),
                'company' => (string) $order->get_billing_company(),
                'address_1' => (string) $order->get_billing_address_1(),
                'address_2' => (string) $order->get_billing_address_2(),
                'city' => (string) $order->get_billing_city(),
                'state' => (string) $order->get_billing_state(),
                'postcode' => (string) $order->get_billing_postcode(),
                'country' => (string) $order->get_billing_country(),
            ];

            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                $orderItems[] = [
                    'sku' => (string) $product->get_sku(),
                    'product_id' => (int) $product->get_id(),
                    'name' => (string) $product->get_name(),
                    'quantity' => (float) $item->get_quantity(),
                    // 'price' => $item->get_price(),
                    'subtotal' => (float) $item->get_subtotal(),
                    'total' => (float) $item->get_total(),
                    'tax' => (float) $item->get_total_tax(),
                ];
            }

            $invoiceData = [
                'currency' => (string) $order->get_currency(),
                'subtotal' => (float) $order->get_subtotal(),
                'shipping_price' => (float) $order->get_shipping_total(),
                'tax' => (float) $order->get_total_tax(),
                'discount' => (float) $order->get_discount_total(),
                'total' => (float) $order->get_total(),
            ];

            $payment_method = [
                'code' => (string) $order->get_payment_method(),
                'title' => (string) $order->get_payment_method_title(),
                'is_cash_on_delivery' => $this->isCashOnDelivery($order),
            ];


            $shipping_address = [
                'first_name' => (string) $order->get_shipping_first_name(),
                'last_name' => (string) $order->get_shipping_last_name(),
                'company' => (string) $order->get_shipping_company(),
                'address_1' => (string) $order->get_shipping_address_1(),
                'address_2' => (string) $order->get_shipping_address_2(),
                'city' => (string) $order->get_shipping_city(),
                'state' => (string) $order->get_shipping_state(),
                'postcode' => (string) $order->get_shipping_postcode(),
                'country' => (string) $order->get_shipping_country(),
                'phone' => (string) $order->get_shipping_phone(),
            ];

            // Create totals array
            $totals = [
                'subtotal' => [
                    'title' => __('Subtotal'),
                    'value' => (float) $order->get_subtotal(),
                    'formatted_value' => $order->get_currency() . ' ' . $order->get_subtotal(),
                ],
                'shipping_total' => [
                    'title' => __('Shipping'),
                    'value' => (float) $order->get_shipping_total(),
                    'formatted_value' => $order->get_currency() . ' ' . $order->get_shipping_total(),
                ],
                'tax_total' => [
                    'title' => __('Tax'),
                    'value' => (float) $order->get_total_tax(),
                    'formatted_value' => $order->get_currency() . ' ' . $order->get_total_tax(),
                ],
                'discount_total' => [
                    'title' => __('Discount'),
                    'value' => (float) $order->get_discount_total(),
                    'formatted_value' => $order->get_currency() . ' ' . $order->get_discount_total(),
                ],
                'total' => [
                    'title' => __('Total'),
                    'value' => (float) $order->get_total(),
                    'formatted_value' => $order->get_currency() . ' ' . $order->get_total(),
                ],
            ];

            $status_code = (string) 'wc-' . $order->get_status();

            $statuses = wc_get_order_statuses();

            if (isset($statuses[$status_code])) {
                $status_label = $statuses[$status_code];
            } else {
                $status_label = __('Unknown');
            }

            $orderData = [
                'id' => (int) $order->get_id(),
                'status' => array(
                    'status_code' => str_replace('wc-', '', $status_code),
                    'status_label' => $status_label,
                ),
                'currency' => (string) $order->get_currency(),
                'total' => (float) $order->get_total(),
                'subtotal' => (float) $order->get_subtotal(),
                'shipping_total' => (float) $order->get_shipping_total(),
                'tax_total' => (float) $order->get_total_tax(),
                'discount_total' => (float) $order->get_discount_total(),
                'created_at' => $order->get_date_created() ? $order->get_date_created()->format('Y-m-d H:i:s') : '',
                'invoice' => $invoiceData,
                'customer' => $customerData,
                'order_items' => $orderItems,
                'shipment' => $shipmentTracking,
                'payment_method' => $payment_method,
                'shipping_address' => $shipping_address,
                'cancel_reason' => $this->getCancelReason($order),
                'totals' => $totals,
            ];

            return $orderData;
        }

        private function format_price($amount)
        {
            $formatted_amount = number_format($amount, 2, ',', '.');
            return html_entity_decode($formatted_amount);
        }

        // Function to check if the payment method is Cash on Delivery (COD)
        private function isCashOnDelivery($order)
        {
            $payment_method = $order->get_payment_method();
            return $payment_method === 'cod';
        }

        // Function to get the cancel reason if the order is cancelled
        private function getCancelReason($order)
        {
            $order_status = $order->get_status();
            if ($order_status === 'cancelled') {
                // Replace 'cancel_reason' with the actual source of the cancel reason,
                // such as $order->get_cancel_reason()
                return "Omniful Requested"; //$order->get_cancel_reason();
            }
            return null;
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
        public function isOrderHasShipment($orderId)
        {
            $existingTrackingLink = get_post_meta($orderId, '_tracing_link', true);
            $existingTrackingNumber = get_post_meta($orderId, '_tracking_number', true);
            $existingShippingLabelPdf = get_post_meta($orderId, '_shipping_label_pdf', true);

            if (!empty($existingTrackingLink) && !empty($existingTrackingNumber) && !empty($existingShippingLabelPdf)) {
                return true;
            } else {
                return false;
            }
        }

        function processStatusArray(array $statusArray): array
        {
            $hasWcPrefix = false;
            foreach ($statusArray as $status) {
                if (strpos($status, 'wc-') === 0) {
                    $hasWcPrefix = true;
                    break;
                }
            }

            $processedArray = array();
            foreach ($statusArray as $status) {
                if ($hasWcPrefix) {
                    $processedArray[] = $status;
                    $processedArray[] = substr($status, 3);
                } else {
                    $processedArray[] = 'wc-' . $status;
                    $processedArray[] = $status;
                }
            }

            return $processedArray;
        }

    }

}