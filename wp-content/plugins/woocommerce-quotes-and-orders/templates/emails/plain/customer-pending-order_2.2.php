<?php
/**
 * Customer processing order email
 *
 * @author 		A3rev
 * @package 	woocommerce-quotes-and-orders/templates/emails/plain
 * @version     2.2.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

echo $email_heading . "\n\n";

echo $email_message . "\n\n";

echo "****************************************************\n\n";

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text );

echo sprintf( wc_ei_ict_t__( 'Plugin Strings - Order number: %s', __( 'Order number: %s', 'wc_email_inquiry') ), $order->get_order_number() ) . "\n";
echo sprintf( wc_ei_ict_t__( 'Plugin Strings - Order date: %s', __( 'Order date: %s', 'wc_email_inquiry') ), date_i18n( wc_date_format(), strtotime( $order->order_date ) ) ) . "\n";

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text );

if ( version_compare( WC_VERSION, '2.2', '<' ) ) {
echo "\n" . $order->email_order_items_table( $order->is_download_permitted(), true, ($order->status=='processing') ? true : false, '', '', true );
} else {
echo "\n" . $order->email_order_items_table( $order->is_download_permitted(), true, $order->has_status( 'processing' ), '', '', true );	
}

echo "----------\n\n";

if ( $totals = $order->get_order_item_totals() ) {
	foreach ( $totals as $total ) {
		echo $total['label'] . "\t " . $total['value'] . "\n";
	}
}

echo "\n****************************************************\n\n";

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text );

echo wc_ei_ict_t__( 'Plugin Strings - Your details', __( 'Your details', 'wc_email_inquiry' ) ) . "\n\n";

if ( $order->billing_email )
	echo wc_ei_ict_t__( 'Plugin Strings - Email', __( 'Email', 'wc_email_inquiry' ) ).':'; echo $order->billing_email. "\n";

if ( $order->billing_phone )
	echo wc_ei_ict_t__( 'Plugin Strings - Tel', __( 'Tel', 'wc_email_inquiry' ) ).':'; ?> <?php echo $order->billing_phone. "\n";

wc_get_template( 'emails/plain/email-addresses.php', array( 'order' => $order ) );

echo "\n****************************************************\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
