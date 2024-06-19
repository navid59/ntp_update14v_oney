<?php

/*
Plugin Name: NETOPIA Payments Payment Gateway
Plugin URI: https://www.netopia-payments.ro
Description: accept payments through NETOPIA Payments
Author: Netopia
Version: 1.4
License: GPLv2
*/

// The ID use as unigue identifier in Block
// define('NETOPIA_PAYMENTS_ID', 'netopia_payments');

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action( 'plugins_loaded', 'netopiapayments_init', 0 );
function netopiapayments_init() {
	// If the parent WC_Payment_Gateway class doesn't exist
	// it means WooCommerce is not installed on the site
	// so do nothing
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
	DEFINE ('NTP_PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );
	
	// If we made it this far, then include our Gateway Class
	include_once( 'wc-netopiapayments-gateway.php' );

	// Now that we have successfully included our class,
	// Lets add it too WooCommerce
	add_filter( 'woocommerce_payment_gateways', 'add_netopiapayments_gateway' );
	function add_netopiapayments_gateway( $methods ) {
		$methods[] = 'netopiapayments';
		return $methods;
	}

	// Add custom action links
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'netopia_action_links' );
	function netopia_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=netopiapayments' ) . '">' . __( 'Settings', 'netopiapayments' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	add_action( 'admin_enqueue_scripts', 'netopiapaymentsjs_init' );
    function netopiapaymentsjs_init($hook) {
        if ( 'woocommerce_page_wc-settings' != $hook ) {
            return;
        }
        wp_enqueue_script( 'netopiapaymentsjs', plugin_dir_url( __FILE__ ) . 'js/netopiapayments_.js',array('jquery'),'2.0' ,true);
        wp_enqueue_script( 'netopiatoastrjs', plugin_dir_url( __FILE__ ) . 'js/toastr.min.js',array(),'2.0' ,true);
        wp_enqueue_style( 'netopiatoastrcss', plugin_dir_url( __FILE__ ) . 'css/toastr.min.css',array(),'2.0' ,false);
    }

	/**
	 * Custom function to declare compatibility with cart_checkout_blocks feature 
	*/
	function declare_netopiapayments_blocks_compatibility() {
		// Check if the required class exists
		if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
			// Declare compatibility for 'cart_checkout_blocks'
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
		}
	}
	// Hook the custom function to the 'before_woocommerce_init' action
	add_action('before_woocommerce_init', 'declare_netopiapayments_blocks_compatibility');

	// Hook in Blocks integration. This action is called in a callback on plugins loaded
	add_action( 'woocommerce_blocks_loaded', 'woocommerce_gateway_netopia_block_support' );
	function woocommerce_gateway_netopia_block_support() {
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			
			// Include the custom Block checkout class
			require_once dirname( __FILE__ ) . '/netopia/Payment/Blocks.php';

			// Hook the registration function to the 'woocommerce_blocks_payment_method_type_registration' action
			add_action(
				'woocommerce_blocks_payment_method_type_registration',
				function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
					add_action(
						'woocommerce_blocks_payment_method_type_registration',
						function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
							// Registre an instance of netopiaBlocks
							$payment_method_registry->register( new netopiapaymentsBlocks );
						 }
					);
				},
				5
			);
		} else {
			// The Current installation of wordpress not sue WooCommerce Block
			return;
		}
	}
}


/**
 * Activation hook  once after install / update will execute
 * By "verify-regenerat" key will verify if certifications not exist
 * Then try to regenerated the certifications
 * */ 
register_activation_hook( __FILE__, 'plugin_activated' );
function plugin_activated(){
	add_option( 'woocommerce_netopiapayments_certifications', 'verify-and-regenerate' );
}

/**
 * The verify and generate certifications will add before the plugin upgrade too.
 * Just in case if installation failed,..
 * Note : We deactive the plugin before upgrade, 
 */
add_action('upgrader_pre_install', 'ntpPreUpgrade', 10, 2);
function ntpPreUpgrade($upgrader_object, $options) {
	// check if , the instalation / upgrade is related to NETOPIA plugin
	if($upgrader_object && $_POST['action'] == "install-plugin" && $_POST['slug'] == "netopia-payments-payment-gateway") {
		// Deactivate the plugin
		deactivate_plugins(plugin_basename(__FILE__));
    	add_option( 'woocommerce_netopiapayments_certifications', 'verify-and-regenerate' );
	}
}

add_action('upgrader_process_complete', 'ntpUpgrade_complete', 10, 2);
function ntpUpgrade_complete($upgrader_object, $options) {
    add_option( 'woocommerce_netopiapayments_certifications', 'verify-and-regenerate' );
}


/**
 * Deactive the plugin, before uninstall the plugin
 */
register_uninstall_hook(__FILE__, 'ntpUninstall');
function ntpUninstall() {
    // Deactivate the plugin
   deactivate_plugins(plugin_basename(__FILE__));
}
