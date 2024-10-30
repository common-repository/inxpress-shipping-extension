<?php

/**
 * WooCommerce InXpress Method
 *
 * @package WooCommerce/InXpress
 * @since   3.5.0
 */

defined('ABSPATH') || exit;

/**
 * Base class for all InXpress Shipping methods regardless of carrier
 */
abstract class WC_Inxpress_Method extends WC_Shipping_Method
{
	/**
	 * The single instance of the class
	 *
	 * @var WC_Inxpress_Method
	 */
	protected static $instance = null;

	protected $inxpress_merchant_origin;
	protected $services;
	protected $gateway;

	/**
	 * Main WC_Inxpress_Method Instance.
	 *
	 * Ensures only one instance of WC_Inxpress_Method is loaded or can be loaded.
	 *
	 * @return WC_Inxpress_Method Main instance
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

	const FIXED       = '1';
	const PERCENTAGE  = '2';
	const PER_ORDER   = '1';
	const PER_PACKAGE = '2';

	/**
	 * Constructs shipping method
	 *
	 * @param number $instance_id the shipping method instance id.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($instance_id = 0)
	{
		$this->instance_id              = absint($instance_id);
		$this->supports                 = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		$this->inxpress_merchant_origin = 1;
		$this->services                 = array(array());

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
		$this->instance_form_fields = array(
			'inxpress_handling_type'    => array(
				'title'       => __('Calculate Handling Fee', 'woocommerce'),
				'type'        => 'select',
				'options'     => array(
					'1' => __('Fixed', 'woocommerce'),
					'2' => __('Percent', 'woocommerce'),
				),
				'description' => '',
				'default'     => '',
			),
			'inxpress_handling_applied' => array(
				'title'       => __('Handling Applied', 'woocommerce'),
				'type'        => 'select',
				'options'     => array(
					'1' => __('Per Order', 'woocommerce'),
					'2' => __('Per Package', 'woocommerce'),
				),
				'description' => '',
				'default'     => '',
			),
			'inxpress_handling_fee'     => array(
				'title'       => __('Handling Fee', 'woocommerce'),
				'type'        => 'text',
				'description' => '',
				'default'     => '',
			),
		);
	}

	/**
	 * Calculate shipping rates
	 *
	 * @access public
	 *
	 * @param mixed $package the package to calculate shipping rates for.
	 *
	 * @return void
	 */
	public function calculate_shipping($package = array())
	{
		$inxpress_settings = (get_option('woocommerce_inxpress_shipping_settings'));

		$products = array();
		foreach ($package['contents'] as $item) {
			$product = $item['data'];

			array_push(
				$products,
				array(
					'id'          => $item['product_id'] . '-' . $item['variation_id'],
					'sku'         => $product->get_sku(),
					'name'        => $product->get_name(),
					'weight'      => $this->calculate_weight_in_grams($product),
					'height'      => $this->calculate_height_in_mm($product),
					'width'       => $this->calculate_width_in_mm($product),
					'depth'       => $this->calculate_depth_in_mm($product),
					'price'       => $product->get_price() * 100,
					'weight_unit' => 'g',
					'dim_unit'    => 'mm',
					'quantity'    => $item['quantity'],
				)
			);
		}
		$split_country = explode(':', get_option('woocommerce_default_country'));

		$origin = array(
			'name'        => '',
			'address1'    => get_option('woocommerce_store_address'),
			'address2'    => get_option('woocommerce_store_address2'),
			'city'        => get_option('woocommerce_store_city'),
			'province'    => $split_country[1] ?? '',
			'phone'       => '',
			'country'     => $split_country[0] ?? '',
			'postal_code' => get_option('woocommerce_store_postcode'),
		);

		$destination = array(
			'name'        => '',
			'address1'    => $package['destination']['address_1'],
			'address2'    => $package['destination']['address_2'],
			'city'        => $package['destination']['city'],
			'province'    => $package['destination']['state'],
			'phone'       => '',
			'country'     => $package['destination']['country'],
			'postal_code' => $package['destination']['postcode'],
		);

		$rates = $this->fetch_rates($inxpress_settings['inxpress_acc_num'], $origin, $destination, $products);

		if ($rates) {
			foreach ($rates as $rate) {
				$shipping_price = $rate['total_price'];
				$id             = $rate['service_type'];

				$shipping_price = $this->add_handling($package['contents'], $shipping_price);

				$total_cost = $shipping_price / 100;

				$label = $rate['display_text'];

				if (mb_strlen($rate['display_sub_text']) > 0) {
					$label .= ' - ' . $rate['display_sub_text'];
				}

				if (0 !== $shipping_price) {
					$this->add_rate(
						array(
							'id'        => $id,
							'label'     => $label,
							'cost'      => $total_cost,
							'meta_data' => array(),
							'calc_tax'  => 'per_order',
							'package'   => $package,
						)
					);
				}
			}
		}
	}

	/**
	 * Convert the weight from a unit of measure to grams for the API
	 *
	 * @param mixed $product a WooCommerce product object.
	 *
	 * @property used convert the weight to grams if necessary for rating in the API
	 */
	public function calculate_weight_in_grams($product)
	{
		$weight_in_uom = floatval($product->get_weight());
		$weight_unit   = get_option('woocommerce_weight_unit');

		switch ($weight_unit) {
			case 'oz':
				$weight = $weight_in_uom * 28.3495;
				break;
			case 'lbs':
				$weight = $weight_in_uom * 453.5920;
				break;
			case 'kg':
				$weight = $weight_in_uom * 1000;
				break;
			default:
				$weight = $weight_in_uom;
		}

		return $weight;
	}

	/**
	 * Convert the height from a unit of measure to mm for the API
	 *
	 * @param mixed $product a WooCommerce product object.
	 *
	 * @property used convert the height to mm if necessary for rating in the API
	 */
	public function calculate_height_in_mm($product)
	{
		$height_in_uom = floatval($product->get_height());

		return $this->dim_converter($height_in_uom);
	}

	/**
	 * Convert the width from a unit of measure to mm for the API
	 *
	 * @param mixed $product a WooCommerce product object.
	 *
	 * @property used convert the width to mm if necessary for rating in the API
	 */
	public function calculate_width_in_mm($product)
	{
		$width_in_uom = floatval($product->get_width());

		return $this->dim_converter($width_in_uom);
	}

	/**
	 * Convert the length from a unit of measure to mm for the API
	 *
	 * @param mixed $product a WooCommerce product object.
	 *
	 * @property used convert the length to mm if necessary for rating in the API
	 */
	public function calculate_depth_in_mm($product)
	{
		$depth_in_uom = floatval($product->get_length());

		return $this->dim_converter($depth_in_uom);
	}

	/**
	 * Convert the dim from a unit of measure to mm for the API
	 *
	 * @param mixed $dim a WooCommerce product object.
	 *
	 * @property used convert the dim to mm if necessary for rating in the API
	 */
	public function dim_converter($dim)
	{
		$dim_unit = get_option('woocommerce_dimension_unit');

		switch ($dim_unit) {
			case 'in':
				$con_dim = $dim * 25.4;
				break;
			case 'cm':
				$con_dim = $dim * 10;
				break;
			case 'm':
				$con_dim = $dim * 1000;
				break;
			case 'yd':
				$con_dim = $dim * 914.4;
				break;
			default:
				$con_dim = $dim;
		}

		return round($con_dim, 1);
	}

	/**
	 * Fetch the rates from the InXpress api
	 *
	 * @param string $account the customer number.
	 * @param mixed  $origin the origin address.
	 * @param mixed  $destination the destination address.
	 * @param mixed  $products the products to ship.
	 *
	 * @property used to calculate the exact rate for any shipping method according to product weight and zip code with country code
	 */
	public function fetch_rates($account, $origin, $destination, $products)
	{
		$inxpress_settings = (get_option('woocommerce_inxpress_shipping_settings'));

		// if the inxpress_settings is blank, use the default for this
		if (isset($inxpress_settings['inxpress_gateway'])) {
			$this->gateway = $inxpress_settings['inxpress_gateway'];
		} else {
			switch ($this->inxpress_gateway) {
				case '1':
					$this->gateway = 'US';
					break;
				case '2':
					$this->gateway = 'CA';
					break;
				case '3':
					$this->gateway = 'UK';
					break;
				case '4':
					$this->gateway = 'AU';
					break;
				default:
					$this->gateway = 'US';
			}
		}

		$request = wp_json_encode(
			array(
				'account'     => $account,
				'gateway'     => $this->gateway,
				'services'    => $this->services,
				'origin'      => $origin,
				'destination' => $destination,
				'items'       => $products,
			)
		);

		$store_id = get_site_option('woo_inxpress_store_id');
		if (!isset($store_id)) {
			$store_id = 'default';
		}

		$url = 'https://api.inxpressapps.com/carrier/v1/stores/' . $store_id . '/rates';

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => $request,
				'timeout' => 8,
			)
		);
		$data     = wp_remote_retrieve_body($response);

		$response = json_decode($data, true);

		if (isset($response['rates'])) {
			return $response['rates'];
		} else {
			return false;
		}
	}

	/**
	 * Add handling to price
	 *
	 * @param mixed  $products the products for shipping.
	 * @param number $shipping_price cost of shipping.
	 *
	 * @property calculate the final rate including the handling fee if configured
	 */
	public function add_handling($products, $shipping_price)
	{
		$handling_fee     = $this->get_handling_fee();
		$handling_type    = $this->inxpress_handling_type;
		$handling_applied = $this->inxpress_handling_applied;

		if ((isset($handling_fee)) && ('' !== $handling_fee) && (self::FIXED === $handling_type)) {
			if ((isset($handling_applied)) && ('' !== $handling_applied) && (self::PER_ORDER === $handling_applied)) {
				$final_price = $shipping_price + $handling_fee;

				return $final_price;
			} elseif ((isset($handling_applied)) && ('' !== $handling_applied) && (self::PER_PACKAGE === $handling_applied)) {
				$final_price = $shipping_price + ((count($products)) * $handling_fee);

				return $final_price;
			} else {
				return $shipping_price;
			}
		} elseif ((isset($handling_fee)) && ('' !== $handling_fee) && (self::PERCENTAGE === $handling_type)) {
			if ((isset($handling_applied)) && ('' !== $handling_applied) && (self::PER_ORDER === $handling_applied)) {
				$final_price = $shipping_price + (($shipping_price / 100) * $handling_fee);

				return $final_price;
			} elseif ((isset($handling_applied)) && ('' !== $handling_applied) && (self::PER_PACKAGE === $handling_applied)) {
				$final_price = $shipping_price + ((count($products)) * (($shipping_price / 100) * $handling_fee));

				return $final_price;
			} else {
				return $shipping_price;
			}
		} else {
			return $shipping_price;
		}
	}

	/**
	 * Convert handling to cents if fixed
	 *
	 * @property calculate the handling fee and convert if fixed
	 */
	public function get_handling_fee()
	{
		$handling_fee = ($this->inxpress_handling_fee === '') ? 0 : $this->inxpress_handling_fee;

		if (self::FIXED === $this->inxpress_handling_type && $handling_fee < 100) {
			return $handling_fee * 100;
		}

		return $handling_fee;
	}
}
