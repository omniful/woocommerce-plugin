<?php

/**
 * This file contains the Settings class, which handles the registration and sanitization 
 * of WooCommerce Omniful Core Plugin's settings page options.
 *
 * @package Omniful\Admin\Model
 */

namespace Omniful\Admin\Model;

if (!class_exists('Settings')) {
    class Settings
    {

        public function __construct()
        {
            // REGISTER THE PLUGIN OPTIONS
            add_action('admin_init', array($this, 'register'));
        }

        /**
         * Registers the Omniful Core Plugin options and sections.
         *
         * @return void
         */
        public function register()
        {
            register_setting(
                'omniful_plugin_options_group',
                'omniful_plugin_options',
                [$this, 'sanitizeOptions']
            );

            add_settings_section(
                'omniful_plugin_options_section',
                'General Settings',
                null,
                'omniful-core-settings'
            );

            // Add omniful_is_enabled option
            $this->addSettingsField(
                'is_enabled',
                'Enable Plugin',
                [$this, 'is_enabled_option_callback'],
                'omniful-core-settings',
                'omniful_plugin_options_section'
            );
            // Add omniful_webhook_url option
            $this->addSettingsField(
                'webhook_url',
                'Webhook Url',
                [$this, 'webhook_url_option_callback'],
                'omniful-core-settings',
                'omniful_plugin_options_section'
            );

            // Add omniful_access_token option
            $this->addSettingsField(
                'access_token',
                'Access Token',
                [$this, 'access_token_option_callback'],
                'omniful-core-settings',
                'omniful_plugin_options_section'
            );

            // Add omniful_workspace_id option
            $this->addSettingsField(
                'workspace_id',
                'Workspace Id',
                [$this, 'workspace_id_option_callback'],
                'omniful-core-settings',
                'omniful_plugin_options_section'
            );

            // Add omniful_webhook_token option
            $this->addSettingsField(
                'webhook_token',
                'Webhook Token',
                [$this, 'webhook_token_option_callback'],
                'omniful-core-settings',
                'omniful_plugin_options_section'
            );

            // Add omniful_enable_debugging option
            $this->addSettingsField(
                'enable_debugging',
                'Enable Debugging',
                [$this, 'enable_debugging_option_callback'],
                'omniful-core-settings',
                'omniful_plugin_options_section'
            );
        }

        /**
         * Sanitizes each option field.
         *
         * @param array $options The options fields to be sanitized.
         *
         * @return array $clean_options The sanitized options fields.
         */
        public function sanitizeOptions($options)
        {
            $clean_options = [];

            // Sanitize each option field
            foreach ($options as $key => $value) {
                $clean_options[$key] = sanitize_text_field($value);
            }

            return $clean_options;
        }

        /**
         * Adds a settings field to the Omniful Core Plugin options page.
         *
         * @param string $id The ID of the settings field.
         * @param string $title The title of the settings field.
         * @param callable $callback The callback function for rendering the settings field.
         * @param string $page The ID of the settings page where the settings field will be displayed.
         * @param string $section The ID of the settings section where the settings field will be displayed.
         *
         * @return void
         */
        public function addSettingsField($id, $title, $callback, $page, $section)
        {
            add_settings_field(
                $id,
                $title,
                $callback,
                $page,
                $section
            );
        }
    }
}