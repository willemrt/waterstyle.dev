<?php
/**
 * Empty cart page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

wc_print_notices();

global $wc_email_inquiry_order_cart_page;
?>

<p class="cart-empty"><?php echo $wc_email_inquiry_order_cart_page['order_cart_empty']; ?></p>

<?php do_action( 'woocommerce_cart_is_empty' ); ?>

<p class="return-to-shop"><a class="button wc-backward" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php wc_ei_ict_t_e( 'Plugin Strings - Return To Shop', __( '&larr; Return To Shop', 'wc_email_inquiry' ) ) ?></a></p>
