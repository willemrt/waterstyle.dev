<?php
/**
 * Customer processing order email
 *
 * @author 		A3rev
 * @package 	woocommerce-quotes-and-orders/templates/emails
 * @version     2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php global $wc_email_inquiry_rules_roles_settings; ?>

<?php
$apply_manual_quote = WC_Email_Inquiry_Quote_Order_Functions::check_apply_manual_quote();
?>

<?php do_action('woocommerce_email_header', $email_heading); ?>

<?php 
if ( ! $apply_manual_quote ) echo wpautop(wptexturize( $email_message_auto_quote )); 
else echo wpautop(wptexturize( $email_message )); 
?>

<?php do_action('woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text ); ?>

<h2><?php echo wc_ei_ict_t__( 'Plugin Strings - Quote', __( 'Quote', 'wc_email_inquiry' ) ) . ': ' . $order->get_order_number(); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col" style="text-align:left;"><?php wc_ei_ict_t_e( 'Plugin Strings - Product', __( 'Product', 'wc_email_inquiry' ) ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php wc_ei_ict_t_e( 'Plugin Strings - Quantity', __( 'Quantity', 'wc_email_inquiry' ) ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php if ( ! $apply_manual_quote ) wc_ei_ict_t_e( 'Plugin Strings - Price', __( 'Price', 'wc_email_inquiry' ) ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php 
		if ( version_compare( WC_VERSION, '2.2', '<' ) ) {
			echo $order->email_order_items_table( $order->is_download_permitted(), true, ($order->status=='processing') ? true : false ); 
		} else {
			echo $order->email_order_items_table( $order->is_download_permitted(), true, $order->has_status( 'processing' ) );
		}
		?>
	</tbody>
	<tfoot>
		<?php
			if ( $totals = $order->get_order_item_totals() ) {
				$i = 0;
				foreach ( $totals as $key => $total ) {
					if ( $apply_manual_quote && ( $key != 'shipping' || ( WC_Email_Inquiry_Quote_Order_Functions::check_hide_shipping_options() && $key == 'shipping' ) ) ) continue;
					$i++;
					?><tr>
						<th class="td" scope="row" colspan="2" style="text-align:left; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['label']; ?></th>
						<td class="td" style="text-align:left; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['value']; ?></td>
					</tr><?php
				}
			}
		?>
	</tfoot>
</table>

<?php do_action('woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text ); ?>

<?php if ( ! $apply_manual_quote ) { ?>
	<p><a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" target="_blank"><?php wc_ei_ict_t_e( 'Plugin Strings - Pay Online Now', __('Pay Online Now', 'wc_email_inquiry') ); ?></a></p>
<?php } ?>

<h2><?php wc_ei_ict_t_e( 'Plugin Strings - Customer details', __( 'Customer details', 'wc_email_inquiry' ) ); ?></h2>

<?php if ($order->billing_email) : ?>
	<p><strong><?php wc_ei_ict_t_e( 'Plugin Strings - Email', __( 'Email', 'wc_email_inquiry' ) ); ?>:</strong> <?php echo $order->billing_email; ?></p>
<?php endif; ?>
<?php if ($order->billing_phone) : ?>
	<p><strong><?php wc_ei_ict_t_e( 'Plugin Strings - Tel', __( 'Tel', 'wc_email_inquiry' ) ); ?>:</strong> <?php echo $order->billing_phone; ?></p>
<?php endif; ?>

<?php wc_get_template('emails/email-addresses.php', array( 'order' => $order )); ?>

<?php do_action('woocommerce_email_footer'); ?>
