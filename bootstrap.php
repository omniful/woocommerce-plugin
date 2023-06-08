<?php

namespace Omniful;

use Omniful\Admin\Logging\Logger;
use Omniful\Admin\Model\AdminPage;
use Omniful\Admin\Model\HeadlessActions;
use Omniful\Admin\Model\RestRoutes;
use Omniful\Admin\Model\Settings;
use Omniful\Admin\Model\ProductBarcode;
use Omniful\Admin\Model\EventHandler;
use Omniful\Admin\Model\EventListeners;
use Omniful\Admin\Model\ApiCaller;
use Omniful\Admin\Model\OrderMetaBox;
use Omniful\Admin\Model\WoocommerceOrderStatuses;
use \WebhookManagement;


/**
 * Plugin Name: Omniful Core
 * Plugin URI: https://github.com/dym5-official/omniful-core
 * Description: A plugin for Omniful - Retail Management & Quick-Commerce Software.
 * Version: 1.0.0
 * Author: @omniful
 * Author URI: https://dym5.com
 * License: GPLv2 or later
 */

if (!class_exists('OmnifulPlugin')) {

    define('OMNIFUL_ADMIN_URL', plugin_dir_url(__FILE__));
    define('OMNIFUL_ADMIN_DIR', plugin_dir_path(__FILE__));

    class OmnifulPlugin
    {
        public $logger;

        public $adminPage;

        public $headlessActions;
        public $omnifulOrderStatuses;

        public $restRoutes;

        public $settings;

        public $productBarcode;

        public $apiCaller;

        public $eventHandler;

        public $webhookManagement;
        public $orderMetaBox;

        public function __construct()
        {
            if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                // REQUIRE ALL REQUIRED CLASSES ONCE
                $this->registerAutoloads();

                // LOAD PLUGIN OPTIONS
                $this->options = get_option('omniful_plugin_options');

                // REGISTER ADMIN PAGE CLASS ONCE
                $this->admin();
                $this->registerAdminPage();

                // REGISTER ALL REQUIRED CLASSES ONCE
                $this->registerLogger();
                $this->registerSettings();
                $this->registerApiCaller();
                $this->registerOrderMetaBox();
                $this->registerEventHandler();
                $this->registerEventListeners();
                $this->registerHeadlessActions();
                $this->registerHeadlessActions();
                $this->registerOmnifulOrderStatuses();
                $this->registerWoocommerceProductBarcode();

                add_action('rest_api_init', [$this, 'registerRestRoutes']);

                // FLUSH PLUGIN OPTIONS ON DEACTIVATING THE PLUGIN
                register_deactivation_hook(__FILE__, [$this, 'deactivatePlugin']);

                // Add cronjob to delete debug logs.
                register_activation_hook(__FILE__, [$this, 'activatePlugin']);
                add_action('server_debug_delete_cron_job', [$this, 'deleteDebuggingLog']);
            } else {
                add_action('admin_notices', [$this, 'woocommerceMissingNotice']);
            }

            add_action('admin_enqueue_scripts', [$this, 'registerPolarisAssets']);
            add_filter('woocommerce_product_data_validation', [$this, 'make_sku_required'], 10, 2);
        }

        public function enable_revisions_for_woocommerce_product($args, $post_type)
        {
            if ($post_type === 'product') {
                $args['supports'][] = 'revisions';
            }

            return $args;
        }

        /**
         * Admin.
         *
         * @version 1.6.1
         * @since   1.2.1
         */
        public function admin()
        {
            // Action links.
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'action_links'));
        }

        /**
         * Show action links on the plugin screen.
         *
         * @version 1.1.0
         * @since   1.0.0
         * @param mixed $links Links array.
         * @return array
         */
        public function action_links($links)
        {
            $custom_links = array();
            $custom_links[] = '<a href="' . admin_url('admin.php?page=omniful-core') . '">' . __('Settings', 'woocommerce') . '</a>';

            return array_merge($custom_links, $links);
        }

        private function registerAutoloads()
        {
            $directories = [
                OMNIFUL_ADMIN_DIR . 'src' . DIRECTORY_SEPARATOR . 'Model',
                OMNIFUL_ADMIN_DIR . 'src' . DIRECTORY_SEPARATOR . 'Logging',
                OMNIFUL_ADMIN_DIR . 'src' . DIRECTORY_SEPARATOR . 'Controller',
            ];

            spl_autoload_register(function ($class) use ($directories) {
                foreach ($directories as $directory) {
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($directory)
                    );

                    foreach ($iterator as $file) {
                        if ($file->isFile() && $file->getExtension() === 'php') {
                            require_once $file->getPathname();
                        }
                    }
                }
            });
        }


        /**
         * Summary of mo_oauth_activate
         *
         * Handles all the events on plugin activation.
         *
         * @return void
         */
        public function activatePlugin()
        {
            // create a new cronjob to delete old debug logs.
            if (!wp_next_scheduled('server_debug_delete_cron_job')) {
                wp_schedule_event(time(), 'weekly', 'server_debug_delete_cron_job');
            }
        }

        public function woocommerceMissingNotice()
        {
            $title = __('Before you can activate the plugin', 'omniful-core');
            $message = __('The Omniful Core Plugin requires WooCommerce to be installed and active. Please install and activate WooCommerce to use <a target="_blank" href="https://wordpress.org/plugins/woocommerce/" >this plugin</a>.', 'omniful-core');
            echo '<div class="notice-wrapper">
            <div class="omniful_shell Polaris-Banner Polaris-Banner--statusWarning Polaris-Banner--withinPage" tabindex="0"
              role="alert" aria-live="polite">
              <div class="omniful_shell Polaris-Box" style="--pc-box-padding-inline-end-xs:var(--p-space-4)">
                <span class="omniful_shell Polaris-Icon Polaris-Icon--colorWarning Polaris-Icon--applyColor">
                  <span class="omniful_shell Polaris-Text--root Polaris-Text--visuallyHidden">
                  </span>
                  <svg viewBox="0 0 20 20" class="omniful_shell Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                    <path fill-rule="evenodd"
                      d="M10 0c-5.514 0-10 4.486-10 10s4.486 10 10 10 10-4.486 10-10-4.486-10-10-10zm-1 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0v-4zm1 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2z">
                    </path>
                  </svg>
                </span>
              </div>
              <div class="omniful_shell Polaris-Banner__ContentWrapper">
                <h2 class="omniful_shell Polaris-Text--root Polaris-Text--headingMd">' . $title . '</h2>
                <div class="omniful_shell Polaris-Box"
                  style="--pc-box-padding-block-end-xs:var(--p-space-05);--pc-box-padding-block-start-xs:var(--p-space-05)">
                  <ul class="omniful_shell Polaris-List Polaris-List--spacingLoose">
                    <li class="omniful_shell Polaris-List__Item">' . $message . '</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
            ';
        }

        /**
         * Summary of omniful_server_debug_delete_log
         *
         * Deletes the debug logs.
         *
         * @return void
         */
        public function deleteDebuggingLog()
        {
            // delete debug log file.
            $this->deleteDebuggingLogFile();
        }

        /**         * Summary of deleteDebuggingLogFile
         *
         * Deletes or empties the debug log file.
         *
         * @return void
         */
        public function deleteDebuggingLogFile()
        {
            $file_name = OMNIFUL_ADMIN_DIR . 'src' . DIRECTORY_SEPARATOR . 'Logging' . DIRECTORY_SEPARATOR . 'error.log';

            // Use the WP_Filesystem method to open the file.
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-src/includes/file.php';
                WP_Filesystem();
            }

            // Get the contents of the file.
            $file_contents = $wp_filesystem->get_contents($file_name);

            // Overwrite the file with the fixed message.
            $wp_filesystem->put_contents($file_name, 'This is Omniful Oauth server plugin debug log' . PHP_EOL . '------------------------------------------------' . PHP_EOL);
        }

        /**
         * Summary of deactivatePlugin
         *
         * Handles all the events on plugin deactivation.
         *
         * @return void
         */
        public function deactivatePlugin()
        {
            // DELETING PLUGIN OPTIONS ON UNINSTALLING THE PLUGIN
            delete_option('webhook_url');
            delete_option('is_enabled');
            delete_option('workspace_id');
            delete_option('access_token');
            delete_option('webhook_token');
            delete_option('enable_debugging');
        }

        public function registerAdminPage()
        {
            $this->adminPage = new AdminPage();
            return $this;
        }

        public function registerOmnifulOrderStatuses()
        {
            $this->omnifulOrderStatuses = new WoocommerceOrderStatuses();
            return $this;
        }

        public function registerWoocommerceProductBarcode()
        {
            $this->productBarcode = new ProductBarcode();
            $this->productBarcode->register();
            return $this;
        }

        public function registerOrderMetaBox()
        {
            $this->orderMetaBox = new OrderMetaBox();
            $this->orderMetaBox->register();
            return $this;
        }

        public function registerHeadlessActions()
        {
            $this->headlessActions = new HeadlessActions();
            return $this;
        }

        public function registerRestRoutes()
        {
            $this->restRoutes = new RestRoutes();
            $this->restRoutes->register();
            return $this;
        }

        public function registerSettings()
        {
            $this->settings = new Settings();
            return $this;
        }

        public function registerLogger()
        {
            $this->logger = new Logger();
            return $this;
        }

        public function registerWebhookManagement()
        {
            if ($this->options) {
                $this->webhookManagement = new WebhookManagement();
                return $this;
            }
        }

        public function registerApiCaller()
        {
            if ($this->options) {
                $this->apiCaller = new ApiCaller(
                    $this->logger
                );
                return $this;
            }

            return null;
        }

        public function registerEventHandler()
        {
            if ($this->options) {
                $this->eventHandler = new EventHandler($this->logger, $this->apiCaller);
                return $this;
            }

            return null;
        }

        public function registerEventListeners()
        {
            if ($this->options) {
                $this->eventListeners = new EventListeners($this->eventHandler);
                $this->eventListeners->register();
                return $this;
            }

            return null;
        }

        /**
         * Enqueue polars assets.
         * Enqueues the script and styles.
         */
        public function registerPolarisAssets()
        {
            wp_enqueue_style('polaris', plugins_url('/src/view/assets/css/polaris.css', __FILE__));
        }
    }

    new \Omniful\OmnifulPlugin();

}