<?php
/**
 * Checkout billing information form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;
?>

<?php if ( $woocommerce->cart->ship_to_billing_address_only() && $woocommerce->cart->needs_shipping() ) : ?>

	<h3><?php wc_ei_ict_t_e( 'Plugin Strings - Billing &amp; Shipping', __( 'Billing &amp; Shipping', 'wc_email_inquiry' ) ); ?></h3>

<?php else : ?>

	<h3><?php wc_ei_ict_t_e( 'Plugin Strings - Billing Address', __( 'Billing Address', 'wc_email_inquiry' ) ); ?></h3>

<?php endif; ?>

<?php do_action('woocommerce_before_checkout_billing_form', $checkout ); ?>

<?php foreach ($checkout->checkout_fields['billing'] as $key => $field) : ?>

	<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>

<?php endforeach; ?>

<?php do_action('woocommerce_after_checkout_billing_form', $checkout ); ?>