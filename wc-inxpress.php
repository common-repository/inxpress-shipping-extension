<?php

/**
 * Plugin Name: InXpress Shipping Extension
 * Plugin URI: https://www.inxpressapps.com/
 * Description: InXpress Shipping Extension
 * Version: 3.5.1
 * Author: InXpress
 * Author URI: https://inxpress.com/
 * WC requires at least: 3.5.0
 * WC tested up to: 8.2
 *
 * @package WooCommerce/InXpress
 */

defined('ABSPATH') || die();

// Define WC_IXP_PLUGIN_FILE.
if (!defined('WC_IXP_PLUGIN_FILE')) {
	define('WC_IXP_PLUGIN_FILE', __FILE__);
}

/**
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true)) {
	if (!class_exists('WC_InXpress')) {
		include_once dirname(__FILE__) . '/includes/class-wc-inxpress.php';
	}

	WC_InXpress::instance();
}
