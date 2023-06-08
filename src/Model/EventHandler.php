<?php

namespace Omniful\Admin\Model;

use Omniful\Admin\Model\ApiCaller;
use Omniful\Admin\Logging\Logger;
use Omniful\Admin\Controller\Sales\Order;
use Omniful\Admin\Controller\Catalog\Product;

/**
 * The EventHandler class handles various events that happen in the system
 * and performs required actions, such as posting to an API.
 *
 * @package Omniful\Admin\Model
 */
class EventHandler
{
    private $logger;

    private $apiCaller;

    private $orderManagement;

    private $productManagement;

    /**
     * Constructor.
     * Initializes the add_actions for admin pages, enqueue asset, register setting & REST API routes.
     */
    public function __construct(
        Logger $logger,
        ApiCaller $apiCaller
    ) {
        $this->logger = $logger;
        $this->apiCaller = $apiCaller;
    }

    /**
     * Handles a new order event.
     *
     * @param int $order_id The ID of the new order.
     *
     * @return void
     */
    public function handleNewOrder(int $order_id): void
    {
        $orderId = $order_id;
        $this->logger->info("handleNewOrder");

        $this->orderManagement = new Order();
        $order = $this->orderManagement->getOrderData($orderId);
        $this->apiCaller->post(['event' => 'order.created', 'data' => $order]);
    }

    /**
     * Handles an update order event.
     *
     * @param int $order_id The ID of the updated order.
     *
     * @return void
     */
    public function handleUpdateOrder(int $order_id)
    {
        $orderId = $order_id;
        $this->logger->info("handleUpdateOrder");

        $this->orderManagement = new Order();
        $order = $this->orderManagement->getOrderData($orderId);
        $this->apiCaller->post(['event' => 'order.updated', 'data' => $order]);
    }

    /**
     * Handles a delete order event.
     *
     * @param int $order_id The ID of the deleted order.
     *
     * @return void
     */
    public function handleDeleteOrder(int $order_id)
    {
        $orderId = $order_id;
        $this->logger->info("handleDeleteOrder");

        $order = get_post($orderId);
        if ($order && $order->post_type == 'shop_order') {
            $data = [
                'order_id' => $orderId,
            ];
            $this->apiCaller->post([
                'event' => 'order.deleted',
                'data' => $data,
            ]);
        }
    }

    /**
     * Handles a cancel order event.
     *
     * @param int $order_id The ID of the cancelled order.
     *
     * @return void
     */
    public function handleCancelOrder(int $order_id)
    {
        $orderId = $order_id;
        $this->logger->info("handleCancelOrder");

        $this->orderManagement = new Order();
        $order = $this->orderManagement->getOrderData($orderId);
        $this->apiCaller->post(['event' => 'order.canceled', 'data' => $order]);
    }

    /**
     * Handles a refund order event.
     *
     * @param int $order_id The ID of the refunded order.
     *
     * @return void
     */
    public function handleRefundOrder(int $order_id)
    {
        $orderId = $order_id;
        $this->logger->info("handleRefundOrder");

        $this->orderManagement = new Order();
        $order = $this->orderManagement->getOrderData($orderId);
        $this->apiCaller->post(['event' => 'order.refunded', 'data' => $order]);
    }

    /**
     * Handles an update order status event.
     *
     * @param int $order_id The ID of the order.
     * @param string $old_status The old status of the order.
     * @param string $new_status The new status of the order.
     *
     * @return void
     */
    public function handleUpdateOrderStatus(int $order_id, $old_status, $new_status)
    {
        $orderId = $order_id;
        $this->logger->info("handleUpdateOrderStatus");

        $this->orderManagement = new Order();
        $order = $this->orderManagement->getOrderData($orderId);
        $this->apiCaller->post(['event' => 'order.status.updated', 'data' => $order, 'old_status' => $old_status, 'new_status' => $new_status]);
    }

    /**
     * Handles a new product event.
     *
     * @param int $product_id The ID of the new product.
     *
     * @return void
     */
    public function handleNewProduct(int $product_id): void
    {
        $this->logger->info("handleNewProduct");

        $this->productManagement = new Product();
        $product = $this->productManagement->getProductData($product_id);
        $this->apiCaller->post(['event' => 'product.created', 'data' => $product]);
    }

    /**
     * Handles an update product event.
     *
     * @param int $product_id The ID of the updated product.
     *
     * @return void
     */
    public function handleUpdateProduct(int $product_id): void
    {
        $this->logger->info("handleUpdateProduct");

        $this->productManagement = new Product();
        $product = $this->productManagement->getProductData($product_id);
        $this->apiCaller->post(['event' => 'product.updated', 'data' => $product]);
    }

    /**
     * Handles a delete product event.
     *
     * @param int $product_id The ID of the deleted product.
     *
     * @return void
     */
    public function handleDeleteProduct(int $product_id): void
    {
        $this->logger->info("handleDeleteProduct");

        $data = [
            'product_id' => $product_id,
        ];
        $this->apiCaller->post(['event' => 'product.deleted', 'data' => $data]);
    }

    /**
     * Handles a new category event.
     *
     * @param int $category_id The ID of the new category.
     *
     * @return void
     */
    public function handleNewCategory(int $category_id): void
    {
        $this->logger->info("handleNewCategory");

        if (get_post_status($category_id) === 'draft' || get_post_status($category_id) === 'auto-draft') {
            return;
        }

        $category = $this->getCategoryDetails($category_id);
        $this->apiCaller->post(['event' => 'category.created', 'data' => $category]);
    }

    /**
     * Handles an update category event.
     *
     * @param int $category_id The ID of the updated category.
     *
     * @return void
     */
    public function handleUpdateCategory(int $category_id): void
    {
        $this->logger->info("handleUpdateCategory");

        $category = $this->getCategoryDetails($category_id);
        $this->apiCaller->post(['event' => 'category.updated', 'data' => $category, 'category_id' => $category_id]);
    }

    /**
     * Handles a delete category event.
     *
     * @param int $term_id The ID of the deleted category.
     *
     * @return void
     */
    public function handleDeleteCategory(int $term_id): void
    {
        $this->logger->info("handleDeleteCategory");

        $data = [
            'category_id' => $term_id,
        ];
        $this->apiCaller->post(['event' => 'category.deleted', 'data' => $data]);
    }

    /**
     * Gets product details.
     *
     * @param int $product_id The ID of the product.
     *
     * @return array An array of product data.
     *
     * @throws Exception If the product is not found.
     */
    private function getProductDetails(int $product_id): array
    {
        $product = wc_get_product($product_id);
        if (!$product) {
            throw new \Exception("Product not found for id: {$product_id}");
        }
        return $product->get_data();
    }

    /**
     * Gets category details.
     *
     * @param int $category_id The ID of the category.
     *
     * @return array An array of category data.
     *
     * @throws Exception If the category is not found.
     */
    private function getCategoryDetails(int $category_id): array
    {
        $category = get_term($category_id);
        if (!$category) {
            throw new \Exception("Category not found for id: {$category_id}");
        }
        return [$category];
    }
}