<?php

/** This class is responsible for Initializing the plugin's admin page, event handlers and API caller.
 *
 * @package Omniful\Admin\Model;
 */

namespace Omniful\Admin\Model;

if (!class_exists('OrderMetaBox')) {
    class OrderMetaBox
    {
        public function register()
        {
            // Add the metabox to the WooCommerce order page
            add_action('add_meta_boxes', array($this, 'add_custom_order_metabox'));

            // Save the values when the order is updated
            add_action('save_post_shop_order', array($this, 'save_custom_order_metabox'), 10, 1);
        }

        public function add_custom_order_metabox()
        {
            add_meta_box('custom_order_metabox', 'Tracking Info', array($this, 'render_custom_order_metabox'), 'shop_order', 'side', 'core');
        }

        public function render_custom_order_metabox($post)
        {
            // Get the saved values
            $tracking_number = get_post_meta($post->ID, '_tracking_number', true);
            $tracing_link = get_post_meta($post->ID, '_tracing_link', true);
            $shipping_label_pdf = get_post_meta($post->ID, '_shipping_label_pdf', true);

            // GET ORDER
            $order = wc_get_order($post->ID);
            $status = $order->get_status();
            ?>
            <style>
                .order-meta-box {
                    margin: 10px 0;
                }

                .order-meta-box label {
                    font-weight: bold;
                }

                .order-meta-box input[type="text"] {
                    width: 100%;
                    padding: 6px 10px;
                    margin: 5px 0;
                }

                .save-button {
                    margin: 0 auto;
                    text-align: center;
                    margin-top: 10px;
                }

                .save-button button {
                    width: 100%;
                    padding: 5px 0px !important;
                }
            </style>

            <div class="order-meta-box">
                <p>
                    <label for="tracking_number">
                        <?php _e('Tracking Number:', 'textdomain'); ?>
                    </label>
                    <br>
                    <input type="text" id="tracking_number" name="tracking_number"
                        value="<?php echo esc_attr($tracking_number); ?>">
                </p>

                <p>
                    <label for="tracing_link">
                        <?php _e('Tracing Link:', 'textdomain'); ?>
                    </label>
                    <br>
                    <input type="text" id="tracing_link" name="tracing_link" value="<?php echo esc_attr($tracing_link); ?>">
                </p>

                <p>
                    <label for="shipping_label_pdf">
                        <?php _e('Shipping Label PDF:', 'textdomain'); ?>
                    </label>
                    <br>
                    <input type="text" id="shipping_label_pdf" name="shipping_label_pdf"
                        value="<?php echo esc_attr($shipping_label_pdf); ?>">
                </p>

                <p class="save-button">
                    <button id="save_custom_order_info" class="button button-primary" disabled>
                        <?php _e('Save Info', 'textdomain'); ?>
                    </button>
                </p>
            </div>

            <script>
                jQuery(document).ready(function ($) {
                    // Enable/disable button based on field values and order status
                    function toggleSaveButton() {
                        var $saveButton = $('#save_custom_order_info');
                        var trackingNumber = $('#tracking_number').val().trim();
                        var tracingLink = $('#tracing_link').val().trim();
                        var shippingLabelPDF = $('#shipping_label_pdf').val().trim();
                        var orderStatus = '<?php echo $status; ?>';

                        if (
                            (trackingNumber !== '' || tracingLink !== '' || shippingLabelPDF !== '') &&
                            !['refunded', 'cancelled', 'failed', 'delivered', 'completed'].includes(orderStatus)
                        ) {
                            $saveButton.prop('disabled', false);
                        } else {
                            $saveButton.prop('disabled', true);
                        }
                    }

                    // Enable/disable button on page load
                    toggleSaveButton();

                    // Enable/disable button on field value change
                    $('#tracking_number, #tracing_link, #shipping_label_pdf').on('input', toggleSaveButton);

                    // Validate tracking link
                    $('#tracing_link').on('input', function () {
                        var tracingLink = $('#tracing_link').val().trim();
                        var $saveButton = $('#save_custom_order_info');

                        if (tracingLink !== '') {
                            var urlPattern = /^((http|https):\/\/)?([a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5})?(:[0-9]{1,5})?(\/.*)?$/;
                            if (!urlPattern.test(tracingLink)) {
                                $saveButton.prop('disabled', true);
                            } else {
                                $saveButton.prop('disabled', false);
                            }
                        } else {
                            $saveButton.prop('disabled', false);
                        }
                    });

                    // Validate shipping label PDF
                    $('#shipping_label_pdf').on('input', function () {
                        var shippingLabelPDF = $('#shipping_label_pdf').val().trim();
                        var $saveButton = $('#save_custom_order_info');

                        if (shippingLabelPDF !== '') {
                            var pdfPattern = /\.pdf$/;
                            if (!pdfPattern.test(shippingLabelPDF)) {
                                $saveButton.prop('disabled', true);
                            } else {
                                $saveButton.prop('disabled', false);
                            }
                        } else {
                            $saveButton.prop('disabled', false);
                        }
                    });
                });

            </script>

            <?php
        }

        public function save_custom_order_metabox($post_id)
        {
            if (isset($_POST['tracking_number'])) {
                update_post_meta($post_id, '_tracking_number', sanitize_text_field($_POST['tracking_number']));
            }
            if (isset($_POST['tracing_link'])) {
                update_post_meta($post_id, '_tracing_link', sanitize_text_field($_POST['tracing_link']));
            }
            if (isset($_POST['shipping_label_pdf'])) {
                update_post_meta($post_id, '_shipping_label_pdf', sanitize_text_field($_POST['shipping_label_pdf']));
            }
        }
    }
}