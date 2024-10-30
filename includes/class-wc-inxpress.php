<?php

/**
 * WooCommerce InXpress Setup
 *
 * @package WooCommerce/InXpress
 * @since   3.5.0
 */

defined('ABSPATH') || exit;

/**
 * Main WooCommerce InXpress Class.
 *
 * @class WCInXpress
 */
final class WC_InXpress
{

	/**
	 * WooCommerce InXpress version.
	 *
	 * @var string
	 */
	public $version = '3.5.0';

	/**
	 * The single instance of the class.
	 *
	 * @var WCInXpress
	 */
	protected static $instance = null;

	/**
	 * Main WCInXpress Instance.
	 *
	 * Ensures only one instance of WCInXpress is loaded or can be loaded.
	 *
	 * @static
	 * @return WCInXpress - Main instance.
	 */
	public static function instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone()
	{
		wc_doing_it_wrong(__FUNCTION__, __('Cloning is forbidden.', 'wcinxpress'), '3');
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.1
	 */
	public function __wakeup()
	{
		wc_doing_it_wrong(__FUNCTION__, __('Unserializing instances of this class is forbidden.', 'wcinxpress'), '3');
	}

	/**
	 * WCInXpress Constructor.
	 */
	public function __construct()
	{
		if (!defined('WC_INXPRESS_ABSPATH')) {
			define('WC_INXPRESS_ABSPATH', dirname(WC_IXP_PLUGIN_FILE) . '/');
		}
		if (!defined('WC_INXPRESS_VERSION')) {
			define('WC_INXPRESS_VERSION', $this->version);
		}

		include_once WC_INXPRESS_ABSPATH . 'includes/wc-inxpress-action-functions.php';
	}
}
