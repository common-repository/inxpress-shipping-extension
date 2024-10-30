<?php

/**
 * WooCommerce InXpress Settings
 *
 * @package WooCommerce/InXpress
 * @since   3.5.0
 */

defined('ABSPATH') || exit;

/**
 * InXpress shipping settings
 *
 * @name WC_InXpress_Settings
 *
 * @property contain all the needed method definitions for InXpress Shipping Fee Calculation
 */
class WC_InXpress_Settings extends WC_Shipping_Method
{
	protected $inxpress_acc_num;
	protected $inxpress_gateway;
	/**
	 * The single instance of the class
	 *
	 * @var WC_Shipping_Method
	 */
	protected static $instance = null;

	/**
	 * Main WC_Shipping_Method Instance.
	 *
	 * Ensures only one instance of WC_Shipping_Method is loaded or can be loaded.
	 *
	 * @return WC_Shipping_Method Main instance
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
	 */
	public function __wakeup()
	{
		wc_doing_it_wrong(__FUNCTION__, __('Unserializing instances of this class is forbidden.', 'wcinxpress'), '3');
	}

	/**
	 * Constructs shipping settings
	 *
	 * @param number $instance_id the shipping method instance id.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($instance_id = 0)
	{
		$this->id                 = 'inxpress_shipping';
		$this->instance_id        = absint($instance_id);
		$this->method_title       = __('InXpress Shipping');
		$this->method_description = __('Using InXpress Shipping');
		$this->supports           = array(
			'settings',
		);

		$this->init();

		add_action(
			'woocommerce_update_options_shipping_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);
	}

	/**
	 * Init your settings
	 *
	 * @access public
	 * @return void
	 */
	public function init()
	{
		$this->init_form_fields();

		$this->inxpress_acc_num          = $this->get_option('inxpress_acc_num');
		$this->inxpress_gateway          = $this->get_option('inxpress_gateway');
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields()
	{
		$this->form_fields = array(
			'inxpress_acc_num' => array(
				'title'       => __('Account Number', 'woocommerce'),
				'type'        => 'text',
				'description' => __('This controls the account number of the admin user.', 'woocommerce'),
				'default'     => '',
			),
			'inxpress_gateway' => array(
				'title'       => __('Gateway', 'woocommerce'),
				'type'        => 'select',
				'options'     => array(
					'US' => __('United States', 'woocommerce'),
					'CA' => __('Canada', 'woocommerce'),
					'UK' => __('Great Britain', 'woocommerce'),
					'AU' => __('Australia', 'woocommerce'),
				),
				'description' => '',
				'default'     => __('US', 'woocommerce'),
			),
		);
	}
}
