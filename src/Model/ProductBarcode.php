<?php

/**
 * This file contains the ProductBarcode class, which handles the registration and sanitization 
 * of WooCommerce Omniful Core Plugin's ProductBarcode page options.
 *
 * @package Omniful\Admin\Model
 */

namespace Omniful\Admin\Model;

if (!class_exists('ProductBarcode')) {
    class ProductBarcode
    {
        public function __construct()
        {
            // Register the plugin options
            add_action('admin_init', array($this, 'register'));
        }

        /**
         * Registers the Omniful Core Plugin options and sections.
         *
         * @return void
         */
        public function register()
        {
            // Add a barcode field to the product inventory tab
            add_action('woocommerce_product_options_sku', array($this, 'add_omniful_barcode_attribute_field'));
            add_action('woocommerce_process_product_meta', array($this, 'save_omniful_barcode_attribute_field'));
        }

        /**
         * Adds the barcode field to the product inventory tab.
         *
         * @param WP_Post $product
         * @return void
         */
        public function add_omniful_barcode_attribute_field($product)
        {
            woocommerce_wp_text_input(
                array(
                    'id' => 'omniful_barcode_attribute',
                    'label' => __('Barcode', 'textdomain'),
                    'placeholder' => '',
                    'description' => __('Enter the product barcode.', 'textdomain'),
                    'required' => true, // Include this attribute to make it a required field
                )
            );
        }

        /**
         * Saves the barcode field value.
         *
         * @param int $product_id
         * @return void
         */
        public function save_omniful_barcode_attribute_field($product_id)
        {
            $barcode = isset($_POST['omniful_barcode_attribute']) ? $_POST['omniful_barcode_attribute'] : '';
            update_post_meta($product_id, 'omniful_barcode_attribute', sanitize_text_field($barcode));
        }
    }
}