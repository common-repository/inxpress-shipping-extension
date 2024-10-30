<?php

/**
 * WooCommerce InXpress Actions
 *
 * Functions for handling InXpress actions
 *
 * @package WooCommerce/InXpress/Functions
 * @version 3.4.0
 */

defined('ABSPATH') || exit;

if (!function_exists('write_log')) {
	/**
	 * Logs errors correctly based on type
	 *
	 * @param array $log could be an array, object or string.
	 */
	function write_log($log)
	{
		// phpcs:disable WordPress.PHP.DevelopmentFunctions
		if (is_array($log) || is_object($log)) {
			error_log(print_r($log, true));
		} else {
			error_log($log);
		}
		// phpcs:enable WordPress.PHP.DevelopmentFunctions
	}
}

/**
 * Sets safe inxpress redirects
 *
 * @param string[] $content List of allowed redirect hosts.
 */
function inxpress_allowed_redirect_hosts($content)
{
	$content[] = 'api.inxpressapps.com';
	$content[] = 'auwebship.inxpress.com';
	$content[] = 'cawebship.inxpress.com';
	$content[] = 'ukwebship.inxpress.com';
	$content[] = 'uswebship.inxpress.com';

	return $content;
}

add_filter('allowed_redirect_hosts', 'inxpress_allowed_redirect_hosts');

/**
 * InXpress portal admin handler
 *
 * @property managing portal
 */
function inxpress_portal_function()
{
	global $wp;
	$inxpress_settings = (get_option('woocommerce_inxpress_shipping_settings'));

	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('You do not have sufficient permissions to access this page.'));
	}

	if (!isset($inxpress_settings['inxpress_gateway'])) {
		wp_die(esc_html__('You need to set the Gateway before you can register.'));
	}

	$store_id = get_site_option('woo_inxpress_store_id');

	$gateway =	strtolower($inxpress_settings['inxpress_gateway']);
	$webship_url = "https://" . $gateway . "webship.inxpress.com/imcs_" . $gateway . "/ecommercial/setting/live/rating/manage?type=WOOCOMMERCE&id=" . $store_id;

	if ($store_id) {
		wp_safe_redirect($webship_url);
		exit;
		// phpcs:disable WordPress.Security.NonceVerification
	} elseif (isset($_GET['registered']) && isset($_GET['store_id']) && 'true' === $_GET['registered']) {
		$store_id = sanitize_text_field(wp_unslash($_GET['store_id']));
		// phpcs:enable WordPress.Security.NonceVerification

		add_site_option('woo_inxpress_store_id', $store_id);

		wp_safe_redirect($webship_url . $store_id);
		exit;
	} else {
		$site_url     = get_home_url();
		$callback_url = get_admin_url() . 'admin.php?page=inxpress_portal';
		$plan         = 'WooCommerce%20' . WC()->version . '%20(WordPress%20v' . get_bloginfo('version') . ')';

		write_log('site_url ' . $site_url);
		write_log('callback_url ' . $callback_url);
		write_log('plan ' . $plan);

		$registeration_url = 'https://' . $gateway . 'webship.inxpress.com/imcs_' . $gateway . '/live/rating/link/account?gateway=' . $inxpress_settings['inxpress_gateway'] . '&platform=WooCommerce&plan=' . $plan . '&storeUrl=' . urlencode($site_url) . '&callbackUrl=' . urlencode($callback_url);

		write_log('site_url ' . $site_url);
		write_log('callback_url ' . $callback_url);
		write_log('plan ' . $plan);

		wp_safe_redirect($registeration_url);

		exit;
	}
	exit;
}

/**
 * Adds portal to admin
 */
function add_inxpress_portal()
{
	add_submenu_page('woocommerce', 'InXpress Portal', 'InXpress Portal', 'manage_options', 'inxpress_portal', 'inxpress_portal_function');
}

add_action('admin_menu', 'add_inxpress_portal');

/**
 * Inits InXpress shipping methods
 */
function inxpress_shipping_init()
{
	include_once WC_INXPRESS_ABSPATH . 'includes/class-wc-inxpress-method.php';

	include_once WC_INXPRESS_ABSPATH . 'includes/class-wc-inxpress-settings.php';
	include_once WC_INXPRESS_ABSPATH . 'includes/class-wc-inxpress-dhlexpress-method.php';
	include_once WC_INXPRESS_ABSPATH . 'includes/class-wc-inxpress-dhlparcel-method.php';
    include_once WC_INXPRESS_ABSPATH . 'includes/class-wc-inxpress-fedex-method.php';
	include_once WC_INXPRESS_ABSPATH . 'includes/class-wc-inxpress-canpar-method.php';
	include_once WC_INXPRESS_ABSPATH . 'includes/class-wc-inxpress-loomis-method.php';
	include_once WC_INXPRESS_ABSPATH . 'includes/class-wc-inxpress-purolator-method.php';
	include_once WC_INXPRESS_ABSPATH . 'includes/class-wc-inxpress-ups-method.php';
}

add_action('woocommerce_shipping_init', 'inxpress_shipping_init');

/**
 * Adds InXpress shipping methods
 *
 * @param string[] $methods An array of all registered shipping methods.
 */
function add_inxpress_methods($methods)
{
	$methods['inxpress_settings']    = 'WC_InXpress_Settings';
	$methods['inxpress_dhl_express'] = 'WC_Inxpress_DHLExpress_Method';
	$methods['inxpress_dhl_parcel']  = 'WC_Inxpress_DHLParcel_Method';
    $methods['inxpress_fedex']       = 'WC_Inxpress_FedEx_Method';
	$methods['inxpress_canpar']      = 'WC_Inxpress_Canpar_Method';
	$methods['inxpress_loomis']      = 'WC_Inxpress_Loomis_Method';
	$methods['inxpress_purolator']   = 'WC_Inxpress_Purolator_Method';
	$methods['inxpress_ups']         = 'WC_Inxpress_UPS_Method';

	return $methods;
}

add_filter('woocommerce_shipping_methods', 'add_inxpress_methods');
