<?php
/**
 * Summary of uninstall
 *
 * Used when uninstalling the plugin.
 *
 * @package Uninstall
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit();
}

// DELETING PLUGIN OPTIONS ON UNINSTALLING THE PLUGIN
delete_option('webhook_url');
delete_option('is_enabled');
delete_option('workspace_id');
delete_option('access_token');
delete_option('webhook_token');
delete_option('enable_debugging');