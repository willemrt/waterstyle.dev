<?php
/**
 * Customer processing order email
 *
 * @author 		A3rev
 * @package 	woocommerce-quotes-and-orders/templates/emails/plain
 * @version     2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wc_email_inquiry_rules_roles_settings;

$apply_manual_quote = WC_Email_Inquiry_Quote_Order_Functions::check_apply_manual_quote();

echo "= " . $email_heading . " =\n\n";

if ( ! $apply_manual_quote ) echo $email_message_auto_quote . "\n\n";
else echo $email_message . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

echo strtoupper( sprintf( wc_ei_ict_t__( 'Plugin Strings - Quote number: %s', __( 'Quote number: %s', 'wc_email_inquiry') ), $order->get_order_number() ) ) . "\n";
echo date_i18n( __( 'jS F Y', 'woocommerce' ), strtotime( $order->order_date ) ) . "\n";
echo "\n" . $order->email_order_items_table( array(
	'show_sku'    => false,
	'show_image'  => false,
	'image_size' => array( 32, 32 ),
	'plain_text'  => true
) );

echo "==========\n\n";

if ( $totals = $order->get_order_item_totals() ) {
	foreach ( $totals as $total ) {
		if ( $apply_manual_quote && ( $key != 'shipping' || ( WC_Email_Inquiry_Quote_Order_Functions::check_hide_shipping_options() && $key == 'shipping' ) ) ) continue;
		echo $total['label'] . "\t " . $total['value'] . "\n";
	}
}

if ( $sent_to_admin ) {
    echo "\n" . sprintf( __( 'View order: %s', 'woocommerce'), admin_url( 'post.php?post=' . $order->id . '&action=edit' ) ) . "\n";
}

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/**
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

if ( ! $apply_manual_quote ) {
	echo wc_ei_ict_t__( 'Plugin Strings - Pay Online Now', __('Pay Online Now', 'wc_email_inquiry') ). ': '; echo esc_url( $order->get_checkout_payment_url() ). "\n\n";
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
?>