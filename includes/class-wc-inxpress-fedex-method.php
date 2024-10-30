<?php

/**
 * WooCommerce InXpress FedEx Method
 *
 * @package WooCommerce/InXpress
 * @since   3.5.0
 */

defined('ABSPATH') || exit;

/**
 * InXpress FedEx shipping method
 *
 * @property contain all the needed method definitions for InXpress Shipping Fee Calculation
 */
class WC_Inxpress_FedEx_Method extends WC_Inxpress_Method
{
	protected $inxpress_gateway;
	protected $inxpress_handling_type;
	protected $inxpress_handling_applied;
	protected $inxpress_handling_fee;

	/**
	 * Constructs FedEx shipping
	 *
	 * @param number $instance_id the shipping method instance id.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($instance_id = 0)
	{
		parent::__construct($instance_id);

		$this->id                 = 'inxpress_fedex';
		$this->method_title       = __('InXpress FedEx');
		$this->method_description = __('Using InXpress FedEx method');

		$this->services = array(
			array(
				'carrier' => 'FedEx',
			),
		);

		$this->init();

		$this->inxpress_gateway = '4';
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

		$this->title                     = $this->get_option('title');
		$this->inxpress_gateway          = $this->get_option('inxpress_gateway');
		$this->inxpress_handling_type    = $this->get_option('inxpress_handling_type');
		$this->inxpress_handling_applied = $this->get_option('inxpress_handling_applied');
		$this->inxpress_merchant_origin  = $this->get_option('inxpress_merchant_origin');
		$this->inxpress_handling_fee     = $this->get_option('inxpress_handling_fee');
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields()
	{
		parent::init_form_fields();
		$this->instance_form_fields = array_merge(
			array(
				'title'            => array(
					'title'       => __('Title', 'woocommerce'),
					'type'        => 'text',
					'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
					'default'     => __('FedEx', 'woocommerce'),
				),
			),
			$this->instance_form_fields
		);
	}
}
