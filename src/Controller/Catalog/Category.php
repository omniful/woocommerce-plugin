<?php

namespace Omniful\Admin\Controller\Catalog;

/** * The Category class provides REST API endpoints for managing WordPress categories.
 *
 * @class Category
 * @since 1.0.0
 */
if (!class_exists('Category')) {
    class Category
    {
        /**
         * Retrieves the list of categories.
         *
         * @method getCategories
         * @param \WP_REST_Request $request The REST API request object.
         * @return \WP_REST_Response The REST API response object.         */
        public function getCategories(\WP_REST_Request $request): \WP_REST_Response
        {
            try {
                $page = (int) $request->get_param('page') ?: 1;
                $pageLimit = (int) $request->get_param('limit') ?: 200;

                $args = array(
                    'taxonomy' => 'product_cat',
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'hide_empty' => false,
                    'parent' => 0,
                    'number' => $pageLimit,
                    'offset' => ($page - 1) * $pageLimit,
                );

                $categories = get_terms($args);
                $categoryData = [];
                foreach ($categories as $category) {
                    $categoryData[] = $this->getCategoryData($category->term_id);
                }

                $totalCategories = (int) wp_count_terms('product_cat');

                $pageInfo = array(
                    'current_page' => $page,
                    'per_page' => $pageLimit,
                    'total_count' => $totalCategories,
                    'total_pages' => ceil($totalCategories / $pageLimit),
                );

                try {
                    $responseData = [
                        'httpCode' => 200,
                        'status' => true,
                        'message' => 'Success',
                        'data' => $categoryData,
                        'page_info' => $pageInfo
                    ];
                } catch (\Exception $e) {
                    $responseData = [
                        'httpCode' => 500,
                        'status' => false,
                        'message' => $e->getMessage(),
                    ];
                }

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }


        /**
         * Retrieves a category by ID.
         *
         * @method getCategoryById
         * @param \WP_REST_Request $request The REST API request object.         
         * @return \WP_REST_Response The REST API response object.
         */
        public function getCategoryById(\WP_REST_Request $request): \WP_REST_Response
        {
            try {
                $categoryId = $request->get_param('id');

                $args = array(
                    'taxonomy' => 'product_cat',
                    'include' => array($categoryId)
                );

                $categoryData = $this->getCategoryData($categoryId);

                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Success',
                    'data' => $categoryData,
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }

        /**
         * Creates or updates a category.
         *
         * @method createUpdateCategory
         * @param \WP_REST_Request $request The REST API request object.
         * @return \WP_REST_Response The REST API response object.
         */
        public function createUpdateCategory(\WP_REST_Request $request): \WP_REST_Response
        {
            $params = $request->get_params();
            $categoryId = isset($params['category_id']) ? $params['category_id'] : '';
            $name = isset($params['name']) ? $params['name'] : '';
            if ($categoryId == '') {
                //create new category
                $parent_id = empty($params['parent_id']) ? 0 : (int) $params['parent_id'];
                $cat_args = array(
                    'cat_name' => $name,
                    'category_parent' => $parent_id
                );
                $result = wp_insert_category($cat_args);
                if (is_wp_error($result)) {
                    $responseData = array(
                        'httpCode' => 400,
                        'status' => false,
                        'message' => 'Error: ' . $result->get_error_message(),
                        'data' => []
                    );
                } else {
                    $responseData = array(
                        'httpCode' => 200,
                        'status' => true,
                        'message' => 'Category Created Successfully',
                        'data' => [
                            'category_id' => $result,
                            'name' => $name
                        ]
                    );
                }

            } else {
                //update existing category
                $cat_args = array(
                    'cat_ID' => $categoryId,
                    'cat_name' => $name
                );
                $result = wp_update_category($cat_args);
                if (is_wp_error($result)) {
                    $responseData = array(
                        'httpCode' => 400,
                        'status' => false,
                        'message' => 'Error: ' . $result->get_error_message(),
                        'data' => []
                    );
                } else {
                    $responseData = array(
                        'httpCode' => 200,
                        'status' => true,
                        'message' => 'Category Updated Successfully',
                        'data' => [
                            'category_id' => $categoryId,
                            'name' => $name
                        ]
                    );
                }
            }
            return new \WP_REST_Response($responseData, 200);
        }

        /**
         * Deletes a category.
         *
         * @method deleteCategory
         * @param \WP_REST_Request $request The REST API request object.
         * @return \WP_REST_Response The REST API response object.
         */
        public function deleteCategory(\WP_REST_Request $request): \WP_REST_Response
        {
            try {
                $categoryId = $request->get_param('id');

                if (!$categoryId) {
                    throw new \Exception('Category ID is required');
                }

                $response = wp_delete_term($categoryId, 'product_cat');

                if (is_wp_error($response)) {
                    throw new \Exception($response->get_error_message());
                }

                $responseData = array(
                    'httpCode' => 200,
                    'status' => true,
                    'message' => 'Category deleted successfully',
                    'data' => [],
                );

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage());
            }
        }

        /**
         * Creates or updates multiple categories.
         *
         * @method bulkUpdateCategories
         * @param \WP_REST_Request $request The REST API request object.
         * @return \WP_REST_Response The REST API response object.
         */
        public function bulkUpdateCategories(\WP_REST_Request $request): \WP_REST_Response
        {
            try {
                $params = $request->get_params();
                $categories = isset($params['categories']) ? $params['categories'] : [];

                if (empty($categories)) {
                    throw new \Exception('No categories to update');
                }

                $responseData = [];
                foreach ($categories as $category) {
                    $categoryId = isset($category['category_id']) ? $category['category_id'] : '';
                    $name = isset($category['name']) ? $category['name'] : '';

                    if ($categoryId == '') {
                        //create new category
                        $parent_id = empty($category['parent_id']) ? 0 : (int) $category['parent_id'];
                        $cat_args = array(
                            'cat_name' => $name,
                            'category_parent' => $parent_id
                        );
                        $result = wp_insert_category($cat_args);
                        if (is_wp_error($result)) {
                            $responseData[] = array(
                                'httpCode' => 400,
                                'status' => false,
                                'message' => 'Error: ' . $result->get_error_message(),
                                'data' => []
                            );
                        } else {
                            $responseData[] = array(
                                'httpCode' => 200,
                                'status' => true,
                                'message' => 'Category Created Successfully',
                                'data' => [
                                    'category_id' => $result,
                                    'name' => $name
                                ]
                            );
                        }

                    } else {
                        //update existing category
                        $cat_args = array(
                            'cat_ID' => $categoryId,
                            'cat_name' => $name
                        );
                        $result = wp_update_category($cat_args);
                        if (is_wp_error($result)) {
                            $responseData[] = array(
                                'httpCode' => 400,
                                'status' => false,
                                'message' => 'Error: ' . $result->get_error_message(),
                                'data' => []
                            );
                        } else {
                            $responseData[] = array(
                                'httpCode' => 200,
                                'status' => true,
                                'message' => 'Category Updated Successfully',
                                'data' => [
                                    'category_id' => $categoryId,
                                    'name' => $name
                                ]
                            );
                        }
                    }
                }

                return new \WP_REST_Response($responseData, 200);
            } catch (\Exception $e) {
                return $this->handle_error($e->getMessage()); //handle error method already exists in the original code
            }
        }

        public function getCategoryData($category_id)
        {
            $category = get_term($category_id, 'product_cat');

            if (!$category) {
                throw new \Exception('Category not found.');
            }

            $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
            if ($thumbnail_id) {
                $thumbnail_url = wp_get_attachment_image_src($thumbnail_id, 'thumbnail')[0];
            } else {
                $thumbnail_url = "";
            }

            $categoryData = [
                'id' => (int) $category->term_id,
                'name' => (string) $category->name,
                'slug' => (string) $category->slug,
                'description' => (string) $category->description,
                'thumbnail_url' => (string) $thumbnail_url,
                'seo_title' => (string) get_term_meta($category->term_id, 'wpseo_title', true),
                'seo_description' => (string) get_term_meta($category->term_id, 'wpseo_metadesc', true),
                'seo_keywords' => (string) get_term_meta($category->term_id, 'wpseo_focuskw', true),
            ];

            return $categoryData;
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
    }
}