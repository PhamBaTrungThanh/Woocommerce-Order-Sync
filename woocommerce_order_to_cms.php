<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://phambatrungthanh.com
 * @since             1.0.0
 * @package           Woocommerce_custom_page
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Sync Order To CMS
 * Plugin URI:        https://phambatrungthanh.com
 * Description:       WWooCommerce Sync Order To CMS is an extension for Woocommerce that call to Pukido's CMS to push order
 * Version:           1.0.0
 * Author:            Phạm Bá Trung Thành
 * Author URI:        https://phambatrungthanh.com
 * WC requires at least: 3.6.0
 * WC tested up to: 3.6.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	/**
	 * The core plugin class that is used to define wordpress hooks and actions.
	 */
	require plugin_dir_path( __FILE__ ) . 'classes/woocommerce-order-sync.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_wc_custom_page() {

		$order_sync = new Woocommerce_Order_Sync();
		$order_sync->run();
	}

	add_action( 'woocommerce_init' ,'run_wc_custom_page');

} else {

	add_action('admin_notices', 'wc_custom_page');
	function wc_custom_page(){
		global $current_screen;
		if($current_screen->parent_base == 'plugins'){
			echo '<div class="error"><p>WWooCommerce Sync Order To CMS '.__('requires <a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a> to be activated in order to work. Please install and activate <a href="'.admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce').'" target="_blank">WooCommerce</a> first.', 'wc_onsale_page').'</p></div>';
		}
	}
	$plugin = plugin_basename(__FILE__);

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	if(is_plugin_active($plugin)){
	 	deactivate_plugins( $plugin);
	}
	 if ( isset( $_GET['activate'] ) )
    		unset( $_GET['activate'] );
}
