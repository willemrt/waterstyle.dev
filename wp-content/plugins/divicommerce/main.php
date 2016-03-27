<?php
/*
Plugin Name: Divi Commerce
Plugin URI: https://www.boltthemes.com/
Description: Make your product pages stand out to your customers
Author: Bolt Themes
Version: 1.2
Author URI: https://www.boltthemes.com/
*/
require_once('wp-updates-plugin.php');
new WPUpdatesPluginUpdater_1387( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));
require plugin_dir_path( __FILE__ ) . 'includes/enable-pgb.php';
/*require plugin_dir_path( __FILE__ ) . 'includes/shortcodes.php';
require plugin_dir_path( __FILE__ ) . 'includes/customizer.php';
*/
function dc_custom_css() {
	 wp_register_style( 'divi_commerce_css', plugins_url('style.css', __FILE__ ),'','1.1', '' );
	 wp_enqueue_style( 'divi_commerce_css' );
}
add_action( 'wp_enqueue_scripts', 'dc_custom_css' );