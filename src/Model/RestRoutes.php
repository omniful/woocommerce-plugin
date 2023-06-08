<?php
/**
 * Omniful Admin Model RestRoutes class.
 *
 * Responsible for the registration of the plugin's REST API routes.
 *
 * @package Omniful\Admin\Model
 */
namespace Omniful\Admin\Model;

use Omniful\Admin\Controller\Sales\PluginOptions;
use Omniful\Admin\Controller\Catalog\Category;
use Omniful\Admin\Controller\Catalog\Product;
use Omniful\Admin\Controller\Sales\Order;
use \WebhookManagement;
use Omniful\Admin\Controller\Shop\Info as ShopInfo;

if (!class_exists('RestRoutes')) {
    class RestRoutes
    {
        private $options;
        private $isEnabled;

        public function __construct()
        {
            // LOAD PLUGIN OPTIONS
            $this->options = get_option('omniful_plugin_options');
            $this->isEnabled = $this->options ? $this->options["is_enabled"] : false;
        }

        /**
         * Method to registers the plugin's API endpoints.
         *
         * @return void
         */
        public function register()
        {
            $endpoints = array(
                // REGISTER OPTIONS API
                array(
                    'methods' => 'GET',
                    'route' => '/store/info',
                    'callback' => function ($request) {
                        $ShopManagement = new ShopInfo();
                        return $ShopManagement->get($request);
                    },
                    'permission_callback' => '__return_true'
                ),

                // REGISTER CREATE WEBHOOK API
                array(
                    'methods' => 'GET',
                    'route' => '/webhooks',
                    'callback' => function ($request) {
                        $WebhookManagement = new WebhookManagement();
                        return $WebhookManagement->get($request);
                    },
                    'args' => array(
                        'page_info' => array(
                            'required' => false,
                            'type' => 'string',
                            'description' => 'Page information.',
                            'default' => ''
                        )
                    ),
                    'permission_callback' => '__return_true'
                ),

                array(
                    'methods' => 'POST',
                    'route' => '/webhooks',
                    'callback' => function ($request) {
                        $WebhookManagement = new WebhookManagement();
                        return $WebhookManagement->save($request);
                    },
                    // 'args' => array(
                    //     'status' => array(
                    //         'required' => true,
                    //         'type' => 'boolean',
                    //         'description' => 'Webhook Status.',
                    //         'default' => ''
                    //     ),
                    //     'topic' => array(
                    //         'required' => true,
                    //         'type' => 'string',
                    //         'description' => 'Topic Name.',
                    //         'default' => ''
                    //     ),
                    //     'delivery_url' => array(
                    //         'required' => true,
                    //         'type' => 'string',
                    //         'description' => 'Callback Url.',
                    //         'default' => ''
                    //     ),
                    //     'secret' => array(
                    //         'required' => true,
                    //         'type' => 'string',
                    //         'description' => 'Webhook Token.',
                    //         'default' => ''
                    //     ),
                    //     'api_version' => array(
                    //         'required' => false,
                    //         'type' => 'string',
                    //         'description' => 'Api Version.',
                    //         'default' => ''
                    //     ),
                    // ),
                    'permission_callback' => '__return_true'
                ),

                // REGISTER OPTIONS API
                array(
                    'methods' => 'GET',
                    'route' => '/options',
                    'callback' => function ($request) {
                        $pluginOptionsManagement = new PluginOptions();
                        return $pluginOptionsManagement->get($request);
                    },
                    'permission_callback' => '__return_true'
                ),
                array(
                    'methods' => 'POST',
                    'route' => '/options',
                    'callback' => function ($request) {
                        $pluginOptionsManagement = new PluginOptions();
                        return $pluginOptionsManagement->save($request);
                    },
                    'permission_callback' => '__return_true'
                ),

                // REGISTER PRODUCTS API
                array(
                    'methods' => 'GET',
                    'route' => '/products',
                    'callback' => function ($request) {
                        if (!$this->isEnabled) {
                            return;
                        }

                        $productManagement = new Product();
                        return $productManagement->getProducts($request);
                    },
                    'args' => array(
                        'page_info' => array(
                            'required' => false,
                            'type' => 'string',
                            'description' => 'Page information.',
                            'default' => ''
                        )
                    ),
                    'permission_callback' => function ($request) {
                        return $this->checkApiPermission($request);
                    }
                ),
                array(
                    'methods' => 'GET',
                    'route' => '/product/(?P<identifier>[a-zA-Z0-9-_]+)',
                    'callback' => function ($request) {
                        if (!$this->isEnabled) {
                            return;
                        }

                        $identifier = $request->get_param('identifier');

                        $productManagement = new Product();
                        return $productManagement->getProductByIdentifier($identifier);
                    },
                    'args' => array(
                        'identifier' => array(
                            'required' => true,
                            'description' => 'Product ID or SKU.',
                            'type' => 'string',
                        ),
                    ),
                    'permission_callback' => function ($request) {
                        return $this->checkApiPermission($request);
                    }
                ),
                array(
                    'methods' => 'PUT',
                    'route' => '/product/(?P<id>\d+)/stock',
                    'callback' => function ($request) {
                        if (!$this->isEnabled) {
                            return;
                        }

                        $productManagement = new Product();
                        return $productManagement->updateProductStockById($request);
                    },
                    'permission_callback' => function ($request) {
                        return $this->checkApiPermission($request);
                    }
                ),
                array(
                    'methods' => 'PUT',
                    'route' => '/product/(?P<sku>[a-zA-Z0-9-_]+)/stock',
                    'callback' => function ($request) {
                        if (!$this->isEnabled) {
                            return;
                        }

                        $productManagement = new Product();
                        return $productManagement->updateProductStockBySku($request);
                    },
                    'permission_callback' => function ($request) {
                        return $this->checkApiPermission($request);
                    }
                ),
                array(
                    'methods' => 'PUT',
                    'route' => '/products/bulk/stock',
                    'callback' => function ($request) {
                        if (!$this->isEnabled) {
                            return;
                        }

                        $productManagement = new Product();
                        return $productManagement->bulkUpdateProductStockBySku($request);
                    },
                    'permission_callback' => function ($request) {
                        return $this->checkApiPermission($request);
                    }
                ),

                // REGISTER CATEGORY API
                array(
                    'methods' => 'GET',
                    'route' => '/categories',
                    'callback' => function ($request) {
                        if (!$this->isEnabled) {
                            return;
                        }

                        $categoryManagement = new Category();
                        return $categoryManagement->getCategories($request);
                    },
                    'args' => array(
                        'page_info' => array(
                            'required' => false,
                            'type' => 'string',
                            'description' => 'Page information.',
                            'default' => ''
                        )
                    ),
                    'permission_callback' => function ($request) {
                        return $this->checkApiPermission($request);
                    }
                ),
                array(
                    'methods' => 'GET',
                    'route' => '/category/(?P<id>\d+)',
                    'callback' => function ($request) {
                        if (!$this->isEnabled) {
                            return;
                        }

                        $categoryManagement = new Category();
                        return $categoryManagement->getCategoryById($request);
                    },
                    'args' => array(
                        'id' => array(
                            'required' => true,
                            'description' => 'Category ID.',
                            'type' => 'integer',
                        ),
                    ),
                    'permission_callback' => function ($request) {
                        return $this->checkApiPermission($request);
                    }
                ),

                // REGISTER SALES API
                array(
                    'methods' => 'GET',
                    'route' => '/orders',
                    'callback' => function ($request) {
                        if (!$this->isEnabled) {
                            return;
                        }

                        $orderManagement = new Order();
                        return $orderManagement->getOrders($request);
                    },
                    'args' => array(
                        'status' => array(
                            'required' => false,
                            'type' => 'string',
                            'description' => 'Order status.',
                            'default' => ''
                        ),
                        'page_info' => array(
                            'required' => false,
                            'type' => 'string',
                            'description' => 'Page information.',
                            'default' => ''
                        )
                    ),
                    'permission_callback' => function ($request) {
                        return $this->checkApiPermission($request);
                    }
                ),
                array(
                    'methods' => 'GET',
                    'route' => '/order/(?P<id>\d+)',
                    'callback' => function ($request) {
                        if (!$this->isEnabled) {
                            return;
                        }

                        $orderManagement = new Order();
                        return $orderManagement->getOrderById($request);
                    },
                    'args' => array(
                        'id' => array(
                            'required' => true,
                            'description' => 'Order ID.',
                            'type' => 'integer',
                        ),
                    ),
                    'permission_callback' => function ($request) {
                        return $this->checkApiPermission($request);
                    }
                ),
                array(
                    'methods' => 'PUT',
                    'route' => '/order/(?P<id>\d+)/status',
                    'callback' => function ($request) {
                        if (!$this->isEnabled) {
                            return;
                        }

                        $orderManagement = new Order();
                        return $orderManagement->updateOrderStatus($request);
                    },
                    'args' => array(
                        'id' => array(
                            'required' => true,
                        ),
                        'status' => array(
                            'required' => true,
                        )
                    ),
                    'permission_callback' => function ($request) {
                        return $this->checkApiPermission($request);
                    }
                ),
                array(
                    'methods' => 'POST',
                    'route' => '/order/(?P<id>\d+)/tracking/info',
                    'callback' => function ($request) {
                        if (!$this->isEnabled) {
                            return;
                        }

                        $orderManagement = new Order();
                        return $orderManagement->addTrackingInfo($request);
                    },
                    'args' => array(
                        'id' => array(
                            'required' => true,
                        ),
                    ),
                    'permission_callback' => function ($request) {
                        return $this->checkApiPermission($request);
                    }
                ),
            );

            foreach ($endpoints as $endpoint) {
                register_rest_route(
                    'V2', $endpoint['route'],
                    array(
                        'methods' => $endpoint['methods'],
                        'callback' => $endpoint['callback'],
                        'args' => isset($endpoint['args']) ? $endpoint['args'] : [],
                        'permission_callback' => isset($endpoint['permission_callback']) ? $endpoint['permission_callback'] : '__return_false',
                    )
                );
            }
        }

        /**
         * Retrieves the list of categories.
         *
         * @method checkApiPermission
         * @param \WP_REST_Request $request The REST API request object.
         */
        public function checkApiPermission($request)
        {
            $authorization_header = $request->get_header('Authorization');
            $consumer_secret = '';

            if ($authorization_header) {
                if (is_array($authorization_header)) {
                    $consumer_secret = str_replace("Bearer ", "", $authorization_header[0]);
                } else {
                    $consumer_secret = str_replace("Bearer ", "", $authorization_header);
                }
            }

            if ($this->is_consumer_secret_exist($consumer_secret)) {
                return true;
            }
        }

        function is_consumer_secret_exist($consumer_secret)
        {
            global $wpdb;

            $result = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}woocommerce_api_keys WHERE consumer_secret = %s",
                    $consumer_secret
                )
            );

            return $result !== null;
        }


    }
}