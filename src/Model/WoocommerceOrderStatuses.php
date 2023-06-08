<?php

namespace Omniful\Admin\Model;

class WoocommerceOrderStatuses
{
    public function __construct()
    {
        add_action('wc_order_statuses', array($this, 'registerOrderStatuses'), 9);
        add_action('wc_order_statuses', array($this, 'registerOrderStatusesToStatusDropdown'), 9);

        add_filter('wc_order_statuses', array($this, 'renameCompletedOrderStatus'), 9);
        add_filter('woocommerce_register_shop_order_post_statuses', array($this, 'renameCompletedOrderStatusCounter'), 9);
    }

    public static function registerOrderStatusesToStatusDropdown()
    {
        $order_statuses = array(
            'wc-pending' => _x('Pending payment', 'Order status', 'woocommerce'),
            'wc-processing' => _x('Processing', 'Order status', 'woocommerce'),
            'wc-on-hold' => _x('On hold', 'Order status', 'woocommerce'),
            'wc-packed' => __('Packed', 'woocommerce'),
            'wc-ready_to_ship' => __('Ready to Ship', 'woocommerce'),
            'wc-shipped' => __('Shipped', 'woocommerce'),
            'wc-completed' => _x('Completed', 'Order status', 'woocommerce'),
            'wc-cancelled' => _x('Cancelled', 'Order status', 'woocommerce'),
            'wc-refunded' => _x('Refunded', 'Order status', 'woocommerce'),
            'wc-failed' => _x('Failed', 'Order status', 'woocommerce'),
        );

        return $order_statuses;
    }

    public static function registerOrderStatuses()
    {
        $order_statuses = apply_filters(
            'woocommerce_register_shop_order_post_statuses',
            array(
                'packed' => array(
                    'label' => _x('Packed', 'Order status', 'woocommerce'),
                    'public' => false,
                    'exclude_from_search' => false,
                    'show_in_admin_all_list' => true,
                    'show_in_admin_status_list' => true,
                    /* translators: %s: number of orders */
                    'label_count' => _n_noop('Packed <span class="count">(%s)</span>', 'Packed <span class="count">(%s)</span>', 'woocommerce'),
                ),
                'shipped' => array(
                    'label' => _x('Shipped', 'Order status', 'woocommerce'),
                    'public' => false,
                    'exclude_from_search' => false,
                    'show_in_admin_all_list' => true,
                    'show_in_admin_status_list' => true,
                    /* translators: %s: number of orders */
                    'label_count' => _n_noop('Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>', 'woocommerce'),
                ),
                'ready_to_ship' => array(
                    'label' => _x('Ready to Ship', 'Order status', 'woocommerce'),
                    'public' => false,
                    'exclude_from_search' => false,
                    'show_in_admin_all_list' => true,
                    'show_in_admin_status_list' => true,
                    /* translators: %s: number of orders */
                    'label_count' => _n_noop('Ready to Ship <span class="count">(%s)</span>', 'Ready to Ship <span class="count">(%s)</span>', 'woocommerce'),
                ),
            )
        );

        foreach ($order_statuses as $order_status => $values) {
            register_post_status($order_status, $values);
        }
    }

    function renameCompletedOrderStatus($statuses)
    {
        $statuses['wc-completed'] = 'Delivered';
        return $statuses;
    }


    function renameCompletedOrderStatusCounter($statuses)
    {
        $statuses['wc-completed']['label_count'] = _n_noop('Delivered <span class="count">(%s)</span>', 'Delivered <span class="count">(%s)</span>', 'woocommerce');
        return $statuses;
    }

}