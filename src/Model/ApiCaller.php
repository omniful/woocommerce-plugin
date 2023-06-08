<?php
/**
 * Define the ApiCaller class for making API requests.
 *
 * This class is used to make API requests by posting data to a specified URL using WP_REST_Request.
 *
 * @package     Omniful\Admin\Model
 */
namespace Omniful\Admin\Model;

use Automattic\Jetpack\Constants;
use Automattic\WooCommerce\Utilities\NumberUtil;

class ApiCaller
{
    /**
     * The API URL to post data to.
     *
     * @access  private
     * @var     string      $url            The API URL to post data to.
     */
    private $url;

    /**
     * The access token to use for authentication.
     *
     * @access  private
     * @var     string      $accessToken   The access token to use for authentication.
     */
    private $accessToken;

    /**
     * The webhook token for securing the request.
     *
     * @access  private
     * @var     string      $webhookToken  The webhook token for securing the request.
     */
    private $webhookToken;

    /**
     * The webhook token for securing the request.
     *
     * @access  private
     * @var     string      $workspaceId  The workspace for securing the request.
     */
    private $workspaceId;

    /**
     * Plugin options
     *
     * @var array
     */
    private $options;

    /**
     * Logger
     *
     * @var array
     */
    private $logger;

    /**
     * Constructor method.
     *
     * @param   string      $url            The API URL to post data to.
     * @param   string      $accessToken   The access token to use for authentication.
     * @param   string      $webhookToken  The webhook token for securing the request.
     * @param Logger $logger compatible logger instance.
     * @access  public
     */
    public function __construct($logger)
    {
        // INIT PLUGIN OPTIONS
        $this->options = get_option('omniful_plugin_options');

        $this->logger = $logger;
        $this->url = $this->options["webhook_url"];
        $this->accessToken = $this->options["access_token"];
        $this->workspaceId = $this->options["workspace_id"];
        $this->webhookToken = $this->options["webhook_token"];

    }

    /**
     * Method to post data to the API URL.
     *
     * @param   array       $payload        The payload data to post to the API URL.
     * @param   int         $retry_count    The number of times to retry in case of rate limiting.
     * @return  mixed                       The \WP_REST_Response object if the request is successful, otherwise an error status object or null.
     * @access  public
     */
    public function post($payload, $retry_count = 3)
    {
        if (!$this->options['is_enabled']) {
            return;
        }

        $delivery_id = $this->get_new_delivery_id();
        $start_time = microtime(true);

        $body = json_encode([
            'event' => $payload["event"],
            'merchant' => $this->workspaceId,
            'created_at' => date("Y-m-d H:i:s"),
            'data' => $payload["data"],
        ]);

        // Setup request args.
        $http_args = [
            'method' => 'POST',
            'timeout' => MINUTE_IN_SECONDS,
            'redirection' => 0,
            'httpversion' => '1.0',
            'blocking' => true,
            'user-agent' => sprintf('WooCommerce/%s Hookshot (WordPress/%s)', Constants::get_constant('WC_VERSION'), $GLOBALS['wp_version']),
            'body' => trim($body),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Webhook-Source' => home_url('/'),
                'X-Event-Name' => $payload["event"],
                'X-Timestamp' => date("Y-m-d H:i:s"),
                'X-Webhook-Token' => $this->webhookToken,
                'X-Omniful-Merchant' => $this->workspaceId,
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
            'cookies' => [],
        ];

        $http_args = apply_filters('woocommerce_webhook_http_args', $http_args, [], $this->workspaceId);

        $response = $this->send_request($this->url, $http_args, $retry_count);

        $duration = NumberUtil::round(microtime(true) - $start_time, 5);
        do_action('woocommerce_webhook_delivery', $http_args, $response, $duration, [], $this->workspaceId);

        return $response;
    }

    private function send_request($url, $http_args, $retry_count)
    {
        $rate_limiting_status_codes = [429];
        $retry_after_header = 'Retry-After';

        while ($retry_count > 0) {
            $response = wp_safe_remote_request($url, $http_args);

            if (!is_wp_error($response) && !in_array(wp_remote_retrieve_response_code($response), $rate_limiting_status_codes)) {
                return $response;
            }

            $headers = wp_remote_retrieve_headers($response);

            if (isset($headers[$retry_after_header])) {
                $retry_after = (int) $headers[$retry_after_header];
                sleep($retry_after);
            }

            $retry_count--;
        }

        if ($retry_count === 0) {
            $this->logger->warning('Rate limit hits %s %s', [$url, $http_args]);
        }

        return null;
    }


    /**
     * Generate a new unique hash as a delivery id based on current time and wehbook id.
     * Return the hash for inclusion in the webhook request.
     *
     * @since  2.2.0
     * @return string
     */
    public function get_new_delivery_id()
    {
        // Since we no longer use comments to store delivery logs, we generate a unique hash instead based on current time and webhook ID.
        return wp_hash($this->workspaceId . strtotime('now'));
    }
}