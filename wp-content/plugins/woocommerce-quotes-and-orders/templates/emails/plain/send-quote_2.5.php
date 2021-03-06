<?php
/**
 * Send quote email
 *
 * @author 		A3rev
 * @package 	woocommerce-quotes-and-orders/templates/emails/plain
 * @version     2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo "= " . $email_heading . " =\n\n";

echo wptexturize( $email_description ) . "\n\n";

echo "==========\n\n";

echo wptexturize( $customer_note ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, true );

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
		echo $total['label'] . "\t " . $total['value'] . "\n";
	}
}

if ( $sent_to_admin ) {
    echo "\n" . sprintf( __( 'View order: %s', 'woocommerce'), admin_url( 'post.php?post=' . $order->id . '&action=edit' ) ) . "\n";
}

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, true );

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/**
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, true );

if ( $order->has_status( 'pending' ) ) {
	echo wc_ei_ict_t__( 'Plugin Strings - Pay Online Now', __('Pay Online Now', 'wc_email_inquiry') ). ': '; echo esc_url( $order->get_checkout_payment_url() ). "\n\n";
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, true );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );