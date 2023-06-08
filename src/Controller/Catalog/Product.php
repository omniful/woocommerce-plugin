<?php

namespace Omniful\Admin\Controller\Catalog;

/** Product class provides REST API endpoints to handle products.
 *
 * @class Product
 * @since 1.0.0
 */
if (!class_exists('Product')) {
    class Product
    {
        /**
         * Retrieves products using WP_Query and return \WP_REST_Response.
         *
         * @since 1.0.0
         * @param \WP_REST_Request $request Object representing the request.
         * @return \WP_REST_Response Response object with product data.
         */
        public function getProducts(\WP_REST_Request $request): \WP_REST_Response
        {
            try {
                $statuses = $request->get_param('status');
                $statuses = $statuses ? explode(',', $statuses) : array('publish');

                $page = (int) $request->get_param('page') ?: 1;
                $pageLimit = (int) $request->get_param('limit') ?: 200;

                $args = array(
                    'post_type' => 'product',
                    'post_status' => $statuses,
                    'posts_per_page' => $pageLimit,
                    'paged' => $page,
                );

                $query = new \WP_Query($args);
                $products = $query->posts;

                $productData = array_map(function ($product) {
                    return $this->getProductData($product->ID);
                }, $products);

                $totalProducts = $query->found_posts;

                $pageInfo = array(
                    'current_page' => $page,
                    'per_page' => $pageLimit,
                    'total_count' => $totalProducts,
                    'total_pages' => ceil($totalProducts / $pageLimit),
                );

                $responseData = [
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Success',
                    'data' => $productData,
                    'page_info' => $pageInfo,
                ];

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }


        /**
         * Retrieves product by ID and return \WP_REST_Response.
         *
         * @since 1.0.0
         * @param \WP_REST_Request $request Object representing the request.
         * @return \WP_REST_Response Response object with product data.
         */
        public function getProductByIdentifier(\WP_REST_Request $request): \WP_REST_Response
        {
            try {
                $productId = $request->get_param('id');
                $productSku = $request->get_param('sku');

                if (!$productId && !$productSku) {
                    throw new \Exception('Product ID or SKU not provided.');
                }

                if ($productId) {
                    $productData = $this->getProductData($productId);
                } elseif ($productSku) {
                    $productId = wc_get_product_id_by_sku($productSku);
                    if (!$productId) {
                        throw new \Exception('Product not found.');
                    }
                    $productData = $this->getProductData($productId);
                }

                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Success',
                    'data' => $productData,
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }


        /**
         * Updates products in bulk and return \WP_REST_Response.
         *
         * @since 1.0.0
         * @param \WP_REST_Request $request Object representing the request.
         * @return \WP_REST_Response Response object with updated product IDs.
         */
        public function bulkUpdateProducts(\WP_REST_Request $request): \WP_REST_Response
        {
            try {
                $product_data = $request->get_params();
                $productIds = [];

                foreach ($product_data as $update_product) {
                    $id = $update_product['id'];

                    if (!$id) {
                        throw new \Exception('Product ID not provided.');
                    }

                    $productIds[] = $id;

                    $product = wc_get_product($id);

                    if (!$product) {
                        throw new \Exception('Product not found.');
                    }

                    if (isset($update_product['name'])) {
                        $product->set_name($update_product['name']);
                    }

                    if (isset($update_product['description'])) {
                        $product->set_description($update_product['description']);
                    }

                    if (isset($update_product['price'])) {
                        $product->set_price($update_product['price']);
                    }

                    if (isset($update_product['categories'])) {
                        $category_ids = [];

                        foreach ($update_product['categories'] as $category) {
                            $category_ids[] = $category['id'];
                        }

                        wp_set_object_terms($product->get_id(), $category_ids, 'product_cat');
                    }

                    if (isset($update_product['stock_status'])) {
                        $product->set_stock_status($update_product['stock_status']);
                    }

                    if (isset($update_product['qty'])) {
                        $product->set_stock_quantity($update_product['qty']);
                    }

                    $product->save();
                }

                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Products updated successfully.',
                    'data' => $productIds
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }

        public function bulkUpdateProductStockBySku(\WP_REST_Request $request): \WP_REST_Response
        {
            try {
                $product_data = $request->get_params();
                $products = $product_data["products"];

                foreach ($products as $product) {
                    $product_sku = $product['sku'];
                    $quantity = $product['qty'];
                    $product_id = wc_get_product_id_by_sku($product_sku);

                    if (!$product_id) {
                        throw new \Exception('Product ID not provided.');
                    }

                    $product = wc_get_product($product_id);

                    if ($product) {
                        if (isset($quantity)) {
                            $product->set_stock_quantity($quantity);
                            $product->save();
                        }
                    }
                }

                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Products updated successfully.',
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }

        /**
         * Updates products stock and return \WP_REST_Response.
         *
         * @since 1.0.0
         * @param \WP_REST_Request $request Object representing the request.
         * @return \WP_REST_Response Response object with updated product IDs.
         */
        public function updateProductStockBySku(\WP_REST_Request $request): \WP_REST_Response
        {
            try {
                $product_data = $request->get_params();
                $quantity = $product_data['qty'];

                $product_sku = $product_data['sku'];
                $product_id = wc_get_product_id_by_sku($product_sku);

                if (!$product_id) {
                    throw new \Exception('Product ID not provided.');
                }

                $product = wc_get_product($product_id);

                if ($product) {
                    if (isset($quantity)) {
                        $product->set_stock_quantity($quantity);
                        $product->save();
                    }
                }

                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Products updated successfully.',
                    'data' => [
                        'new_stock' => $product->get_stock_quantity(),
                    ]
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }

        /**
         * Deletes a product and return \WP_REST_Response.
         *
         * @since 1.0.0
         * @param \WP_REST_Request $request Object representing the request.
         * @return \WP_REST_Response Response object with success message.
         */
        public function deleteProduct(\WP_REST_Request $request): \WP_REST_Response
        {
            try {
                $id = $request->get_param('id');

                if (!$id) {
                    throw new \Exception('Product ID not provided.');
                }

                if ('product' !== get_post_type($id)) {
                    throw new \Exception('Post ID is not a WooCommerce product.');
                }

                $product = wc_get_product($id);

                if (!$product) {
                    throw new \Exception('Product not found.');
                }

                $product->delete();

                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Product deleted successfully.'
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }

        function getProductData($productId)
        {
            $gallery_urls = [];
            $variationDetails = [];
            $product = wc_get_product($productId);
            $weight_unit = get_option('woocommerce_weight_unit');

            if (!$product) {
                throw new \Exception('Product not found.');
            }

            $categories = [];
            $product_categories = $product->get_category_ids();

            foreach ($product_categories as $category_id) {
                $category = get_term_by('id', $category_id, 'product_cat');
                if ($category) {
                    $categories[] = [
                        'id' => (int) $category->term_id,
                        'name' => (string) $category->name,
                    ];
                }
            }

            // Get prices and sales
            $regular_price = (float) $product->get_regular_price();
            $sale_price = (float) $product->get_sale_price();
            $price = $sale_price ? $sale_price : $regular_price;
            $prices = [
                'regular_price' => $regular_price,
                'sale_price' => $sale_price,
                'price' => $price
            ];

            $variations = $product->get_children();

            foreach ($variations as $variation_id) {
                $variation = wc_get_product($variation_id);

                if ($variation && $variation->is_type('variation')) {
                    // Get the product image
                    $image = $variation->get_image_id();
                    $thumbnail_url = (string) wp_get_attachment_image_src($image, 'woocommerce_thumbnail')[0];

                    // Get variation details
                    $variation_detail = [
                        'id' => (int) $variation->get_id(),
                        'sku' => (string) $variation->get_sku(),
                        'regular_price' => (float) $variation->get_regular_price(),
                        'sale_price' => (float) $variation->get_sale_price(),
                        'price' => (float) $variation->get_price(),
                        'attributes' => $this->getChildAttributes($variation->get_attributes()),
                        'thumbnail' => $thumbnail_url,
                        'stock_quantity' => (int) $variation->get_stock_quantity(),
                    ];

                    // Add the variation details to the array
                    $variationDetails[] = $variation_detail;
                }
            }

            // Get the product images
            $gallery_ids = $product->get_gallery_image_ids();

            // Get the product image
            $image = $product->get_image_id();

            // Get the URL of the full size image
            $image_url = wp_get_attachment_image_src($image, 'full') ? (string) wp_get_attachment_image_src($image, 'full')[0] : "";

            // Get the URL of the thumbnail
            $thumbnail_url = wp_get_attachment_image_src($image, 'woocommerce_thumbnail') ? (string) wp_get_attachment_image_src($image, 'woocommerce_thumbnail')[0] : "";

            foreach ($gallery_ids as $gallery_id) {
                $gallery_url = (string) wp_get_attachment_image_url($gallery_id, 'full');
                $gallery_alt = get_post_meta($gallery_id, '_wp_attachment_image_alt', true);
                if (empty($gallery_alt)) {
                    $gallery_alt = pathinfo(get_attached_file($gallery_id), PATHINFO_FILENAME);
                }
                $gallery_urls[] = [
                    'url' => $gallery_url,
                    'alt' => $gallery_alt,
                ];
            }

            $productData = [
                'id' => (int) $product->get_id(),
                'sku' => (string) $product->get_sku(),
                'barcode' => (string) $product->get_meta('omniful_barcode_attribute'),
                'stock_quantity' => (int) $product->get_stock_quantity(),
                'name' => (string) $product->get_name(),
                'description' => (string) $product->get_description(),
                'short_description' => (string) $product->get_short_description(),
                'date_created' => $product->get_date_created(),
                'date_modified' => $product->get_date_modified(),
                'categories' => $categories,
                'tags' => $product->get_tag_ids(),
                'attributes' => $this->getAttributes($product->get_attributes()),
                'variations' => $variationDetails,
                'prices' => $prices,
                'gallery_images' => [
                    "full" => $image_url,
                    'thumbnail' => $thumbnail_url,
                    'images' => $gallery_urls,
                ],
                'tax_class' => (string) $product->get_tax_class(),
                'manage_stock' => (bool) $product->get_manage_stock(),
                'in_stock' => (bool) $product->is_in_stock(),
                'backorders_allowed' => (bool) $product->backorders_allowed(),
                'weight' => (float) $product->get_weight(),
                'weight_unit' => (string) $weight_unit,
                'shipping_class_id' => (int) $product->get_shipping_class_id(),
            ];

            return $productData;
        }


        public function getAttributes($attributesData)
        {
            $attributesDetails = [];

            foreach ($attributesData as $attributeData) {
                $attribute = array(
                    'name' => $attributeData["name"] ?: '',
                    'options' => $attributeData["options"] ?: [],
                );

                $attributesDetails[] = $attribute;
            }

            return $attributesDetails;
        }

        public function getChildAttributes($attributesData)
        {
            $attributesDetails = [];

            foreach ($attributesData as $attribute_name => $attribute_value) {
                $attribute = array(
                    'name' => $attribute_name,
                    'options' => $attribute_value,
                );

                $attributesDetails[] = $attribute;
            }

            return $attributesDetails;
        }

        /**
         * Handles error and return \WP_REST_Response.
         *
         * @since 1.0.0
         * @param \Exception $e Object representing the exception thrown.
         * @return \WP_REST_Response Response object with error message.
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