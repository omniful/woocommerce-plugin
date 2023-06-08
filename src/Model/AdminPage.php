<?php

/** This class is responsible for Initializing the plugin's admin page, event handlers and API caller.
 *
 * @package Omniful\Admin\Model;
 */

namespace Omniful\Admin\Model;

if (!class_exists('AdminPage')) {
    class AdminPage
    {
        /**
         * Constructor.
         * Initializes the add_actions for admin pages, enqueue asset, register setting & REST API routes.
         */
        public function __construct()
        {
            add_action('admin_menu', [$this, 'add_menus']);
            add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        }

        /**
         * Add admin menus.
         * to hook the omniful menus and renders the access_keys.
         */
        public function add_menus()
        {
            add_menu_page(
                'Omniful Core Plugin Options',
                'Omniful',
                'edit_posts',
                'omniful-core',
                [$this, 'admin_page'],
                'https://www.omniful.com/favicon-6.0.ico',
                null
            );
        }

        /**
         * Render admin page.
         * Renders the admin page content.
         */
        public function admin_page()
        {
            echo '<div id="wp-admin-plugin-page-root"></div>';
        }

        /**
         * Enqueue admin assets.
         * Enqueues the script and styles.
         */
        public function enqueueAssets($hook)
        {
            if ('toplevel_page_omniful-core' !== $hook) {
                return;
            }



            $script = 'src' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'bundle.js';
            $scriptFile = OMNIFUL_ADMIN_DIR . '/' . $script;

            if (file_exists($scriptFile)) {
                wp_enqueue_script('react-wp-admin', OMNIFUL_ADMIN_URL . $script, array(), filemtime($scriptFile), true);
            }

            $style = 'src' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'bundle.css';
            $styleFile = OMNIFUL_ADMIN_DIR . '/' . $style;

            if (file_exists($styleFile)) {
                wp_enqueue_style('react-wp-admin', OMNIFUL_ADMIN_URL . $style, array(), filemtime($styleFile));
            }
        }
    }
}