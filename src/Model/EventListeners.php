<?php

/** This class is responsible for Initializing the plugin's admin page, event handlers and API caller.
 *
 * @package Omniful\Admin\Model;
 */

namespace Omniful\Admin\Model;

use Omniful\Admin\Model\EventHandler;

if (!class_exists('EventListeners')) {
    class EventListeners
    {
        public $eventHandler;

        /**
         * Plugin options
         *
         * @var array
         */
        private $options;

        /**
         * EventHandler constructor.
         * 
         * @param ApiCaller $eventHandler EventHandler instance.
         * 
         * @return void
         */
        public function __construct(EventHandler $eventHandler)
        {
            $this->eventHandler = $eventHandler;
            $this->options = get_option('omniful_plugin_options');
        }

        function handle_order_created($order_id)
        {
            // Check if the post status is auto-draft or auto-save
            if (get_post_status($order_id) === 'AUTO-DRAFT' || get_post_status($order_id) === 'auto-save') {
                return; // If it's auto-draft or auto-save, don't perform any actions
            }

            // Perform actions for order created
            $this->eventHandler->handleNewOrder((int) $order_id);
        }

        function handle_order_updated($order_id)
        {
            // Check if the post status is auto-draft or auto-save
            if (get_post_status($order_id) === 'AUTO-DRAFT' || get_post_status($order_id) === 'auto-save') {
                return; // If it's auto-draft or auto-save, don't perform any actions
            }

            // Get order created and updated datetime
            $created_at = get_post_field('post_date', $order_id, 'raw');
            $updated_at = get_post_field('post_modified', $order_id, 'raw');

            // If there's no difference between created and updated datetime, return
            if ($created_at === $updated_at) {
                return;
            }

            // If it's not auto-draft or auto-save, perform actions for order updated
            $this->eventHandler->handleUpdateOrder((int) $order_id);
        }

        function handle_order_status_updated($order_id, $old_status, $new_status, $order)
        {
            // Check if the post status is auto-draft or auto-save
            if (get_post_status($order_id) === 'auto-draft' || get_post_status($order_id) === 'auto-save' || get_post_status($order_id) === 'AUTO-DRAFT') {
                return; // If it's auto-draft or auto-save, don't perform any actions
            }

            // Perform actions for order status update
            $this->eventHandler->handleUpdateOrderStatus((int) $order_id, $old_status, $new_status);
        }

        public function handle_product_save($post_id, $post, $update)
        {
            // Check if the saved post is a product
            if ($post->post_type !== 'product') {
                return;
            }

            $product = wc_get_product($post_id);
            $product_name = $product->get_name();
            $product_sku = $product->get_sku() ? $product->get_sku() : "";
            $date_created = $product->get_date_created() ? $product->get_date_created()->format('Y-m-d H:i:s') : null;
            $date_modified = $product->get_date_modified() ? $product->get_date_modified()->format('Y-m-d H:i:s') : null;

            // Check if the product name is "AUTO-DRAFT"
            if ($product_name === 'AUTO-DRAFT') {
                return;
            }

            // Check if the product is new
            if (!empty($date_created) && !empty($date_modified) && !empty($product_sku) && $date_created === $date_modified) {
                $this->handle_new_product_created($post_id);
            }

            if ($update && !empty($date_created) && !empty($date_modified) && !empty($product_sku) && $date_created != $date_modified) {
                $this->handle_existing_product_updated($post_id);
            }
        }

        // Existing product updated
        public function handle_existing_product_updated($post_id)
        {
            // Perform actions for existing product update
            $this->eventHandler->handleUpdateProduct((int) $post_id);
        }

        // New product created
        public function handle_new_product_created($post_id)
        {
            // Perform actions for new product created
            $this->eventHandler->handleNewProduct((int) $post_id);
        }

        function handle_category_created($term_id, $term_taxonomy_id, $taxonomy)
        {
            if ($taxonomy === 'product_cat') {
                $term = get_term($term_id, $taxonomy, ARRAY_A);

                // Get order created and updated datetime
                $created_at = $term['date_created'];
                $updated_at = $term['post_modified'];

                // If there's no difference between created and updated datetime, return
                if ($created_at === $updated_at) {
                    ;
                    // Perform actions for category created
                    $this->eventHandler->handleNewCategory((int) $term_id);
                }
            }
        }

        function handle_category_updated($term_id, $term_taxonomy_id, $taxonomy)
        {
            if ($taxonomy === 'product_cat') {
                $term = get_term($term_id, $taxonomy, ARRAY_A);

                // Get order created and updated datetime
                $created_at = $term['date_created'];
                $updated_at = $term['post_modified'];

                // If there's no difference between created and updated datetime, return
                if ($created_at === $updated_at) {
                    return;
                }

                // Perform actions for category updated
                $this->eventHandler->handleUpdateCategory((int) $term_id);
            }
        }

        /**
         * Initialize event handlers.
         * Registers the category, product & order events.
         */
        public function register()
        {
            if (!$this->options['is_enabled']) {
                return;
            }

            // Update existing product
            add_action('save_post', [$this, 'handle_product_save'], 10, 3);

            // Order created
            add_action('woocommerce_new_order', array($this, 'handle_order_created'));

            // Order status updated or changed
            add_action('woocommerce_order_status_changed', array($this, 'handle_order_status_updated'), 10, 4);

            // Order products or data updated
            add_action('woocommerce_update_order', array($this, 'handle_order_updated'));

            // WooCommerce category created
            add_action('created_term', array($this, 'handle_category_created'), 10, 3);

            // WooCommerce category updated
            add_action('edited_term', array($this, 'handle_category_updated'), 10, 3);
        }

    }
}