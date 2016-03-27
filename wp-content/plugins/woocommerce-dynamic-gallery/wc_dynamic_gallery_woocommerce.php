<?php
/*
Plugin Name: WooCommerce Dynamic Gallery LITE
Plugin URI: http://a3rev.com/shop/woocommerce-dynamic-gallery/
Description: Auto adds a fully customizable dynamic images gallery to every single product page with thumbnails, caption text and lazy-load. Over 28 settings to fine tune every aspect of the gallery. Creates an image gallery manager on every product edit page - greatly simplifies managing product images. Search engine optimized images with WooCommerce Dynamic Gallery Pro.
Version: 2.2.0
Author: A3 Revolution
Author URI: http://www.a3rev.com/
License: GPLv2 or later
*/

/*
	WooCommerce Dynamic Gallery. Plugin for the WooCommerce plugin.
	Copyright © 2011 A3 Revolution Software Development team

	A3 Revolution Software Development team
	admin@a3rev.com
	PO Box 1170
	Gympie 4570
	QLD Australia
*/
?>
<?php
define( 'WOO_DYNAMIC_GALLERY_FILE_PATH', dirname(__FILE__) );
define( 'WOO_DYNAMIC_GALLERY_DIR_NAME', basename(WOO_DYNAMIC_GALLERY_FILE_PATH) );
define( 'WOO_DYNAMIC_GALLERY_FOLDER', dirname(plugin_basename(__FILE__)) );
define( 'WOO_DYNAMIC_GALLERY_NAME', plugin_basename(__FILE__) );
define( 'WOO_DYNAMIC_GALLERY_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'WOO_DYNAMIC_GALLERY_DIR', WP_PLUGIN_DIR.'/'.WOO_DYNAMIC_GALLERY_FOLDER );
define( 'WOO_DYNAMIC_GALLERY_CSS_URL',  WOO_DYNAMIC_GALLERY_URL . '/assets/css' );
define( 'WOO_DYNAMIC_GALLERY_IMAGES_URL',  WOO_DYNAMIC_GALLERY_URL . '/assets/images' );
define( 'WOO_DYNAMIC_GALLERY_JS_URL',  WOO_DYNAMIC_GALLERY_URL . '/assets/js' );
define( 'WOO_DYNAMIC_GALLERY_PREFIX', 'wc_dgallery_' );
if(!defined("WOO_DYNAMIC_GALLERY_DOCS_URI"))
    define("WOO_DYNAMIC_GALLERY_DOCS_URI", "http://docs.a3rev.com/user-guides/woocommerce/woo-dynamic-gallery/");

define( 'WOO_DYNAMIC_GALLERY_VERSION', '2.2.0' );
define( 'WOO_DYNAMIC_GALLERY_DB_VERSION', '2.2.0' );

include('admin/admin-ui.php');
include('admin/admin-interface.php');

include('admin/admin-pages/dynamic-gallery-page.php');

include('admin/admin-init.php');
include('admin/less/sass.php');

include('classes/class-wc-dynamic-gallery-functions.php');
include('classes/class-wc-dynamic-gallery-variations.php');
include('classes/class-wc-dynamic-gallery.php');
include('classes/class-wc-dynamic-gallery-preview.php');
include('classes/class-wc-dynamic-gallery-metaboxes.php');

include('admin/wc_gallery_woocommerce_admin.php');

include('includes/class-plugin-notices.php');

/**
* Call when the plugin is activated
*/
register_activation_hook(__FILE__,'wc_dynamic_gallery_install');

?>
