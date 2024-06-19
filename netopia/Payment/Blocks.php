<?php
/**
 * Class Netopia_Blocks_Support
 * This class used for supporting wooCommerce Block
 * @copyright NETOPIA Payments
 * @author Dev Team
 * @version 1.0
 **/
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class netopiapaymentsBlocks extends AbstractPaymentMethodType {
	private $gateway;
	protected $name = 'netopiapayments';


	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_netopiapayments_settings', [] );
		$this->gateway = new netopiapaymentsBlocks();
		// die(print_r($this->settings));
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return filter_var( $this->get_setting( 'enabled', false ), FILTER_VALIDATE_BOOLEAN );
		// return $this->gateway->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
        	wp_register_script(
				'netopiapayments-block-integration',
				plugin_dir_url(__FILE__) . '../../blocks/index.js',
				array(
					'wc-blocks-registry',
					'wc-settings',
					'wp-element',
					'wp-html-entities',
					'wp-i18n'
				),
				null,
				true
			);
		return [ 'netopiapayments-block-integration' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'title'       		=> $this->get_setting( 'title' ),
			'description' 		=> $this->get_setting( 'description' ),
			'supports'    		=> $this->get_supported_features(),
			'payment_methods'   => $this->get_setting( 'payment_methods' ),
		];
	}
}
