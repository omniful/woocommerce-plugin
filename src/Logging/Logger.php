<?php

namespace Omniful\Admin\Logging;

/**
 * Summary of class-server-debug
 *
 * @package Debug
 */

/**
 * Summary of Logger
 */
class Logger
{
	/**
	 * Summary of error
	 *
	 * Logs an error message.
	 *
	 * @param mixed $message Error message.
	 * @return void
	 */
	public static function error($message)
	{
		self::log($message, 'error');
	}

	/**
	 * Summary of warning
	 *
	 * Logs a warning message.
	 *
	 * @param mixed $message Warning message.
	 * @return void
	 */
	public static function warning($message)
	{
		self::log($message, 'warning');
	}

	/**
	 * Summary of info
	 *
	 * Logs an informational message.
	 *
	 * @param mixed $message Informational message.
	 * @return void
	 */
	public static function info($message)
	{
		self::log($message, 'info');
	}

	/**
	 * Summary of debug
	 *
	 * Logs a debug message.
	 *
	 * @param mixed $message Debug message.
	 * @return void
	 */
	public static function debug($message)
	{
		self::log($message, 'debug');
	}

	/**
	 * Summary of log
	 *
	 * Logs a message with the specified log level.
	 *
	 * @param mixed $message Log message.
	 * @param string $level Log level (error, warning, info, or debug).
	 * @return void
	 */
	private static function log($message, $level = "info")
	{
		// INIT PLUGIN OPTIONS
		$options = get_option('omniful_plugin_options');

		if (!$options['enable_debugging']) {
			return;
		}

		$file_location = OMNIFUL_ADMIN_DIR . 'src' . DIRECTORY_SEPARATOR . 'Logging' . DIRECTORY_SEPARATOR . $level . '.log';

		$time = gmdate('d-M-Y H:i:s');
		$message = '[ ' . $time . ' UTC]: ' . print_r($message, true) . PHP_EOL;

		error_log($message, 3, $file_location);
	}

}