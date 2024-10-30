<?php

/**
 * WooCommerce InXpress Loomis Method
 *
 * @package WooCommerce/InXpress
 * @since   3.4.3
 */

defined('ABSPATH') || exit;

/**
 * InXpress Loomis shipping method
 *
 * @property contain all the needed method definitions for InXress Shipping Fee Calculation
 */
class WC_Inxpress_Loomis_Method extends WC_Inxpress_Method
{
	protected $inxpress_gateway;
	protected $inxpress_handling_type;
	protected $inxpress_handling_applied;
	protected $inxpress_handling_fee;

	/**
	 * Constructs Loomis shipping
	 *
	 * @param number $instance_id the shipping method instance id.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($instance_id = 0)
	{
		parent::__construct($instance_id);

		$this->id                 = 'inxpress_loomis';
		$this->method_title       = __('InXpress Loomis');
		$this->method_description = __('Using InXpress Loomis method');

		$this->services = array(
			array(
				'carrier' => 'Loomis',
			),
		);

		$this->init();

		$this->inxpress_gateway = '2';
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
				'title' => array(
					'title'       => __('Title', 'woocommerce'),
					'type'        => 'text',
					'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
					'default'     => __('Loomis', 'woocommerce'),
				),
			),
			$this->instance_form_fields
		);
	}
}
