<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Designed for Toolset Layouts Plugin Compatibility
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	function woocommerce_default_index_loop() {
	
		if ( have_posts() ) : ?>
		    <?php 
		    	if (!( function_exists( 'the_ddlayout' ) )) {
		    		do_action( 'woocommerce_before_shop_loop' );
		    	}
		    ?>			
			<?php woocommerce_product_loop_start(); ?>

				<?php woocommerce_product_subcategories(); ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

			<?php
				/**
				 * woocommerce_after_shop_loop hook
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				if (!( function_exists( 'the_ddlayout' ) )) {
					do_action( 'woocommerce_after_shop_loop' );
				}
			?>
			
		<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

			<?php wc_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif; 
		
		if (!( function_exists( 'the_ddlayout' ) )) {
			/**
			 * woocommerce_after_main_content hook
			*
			* @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
			*/
			do_action( 'woocommerce_after_main_content' );
		}
	}

	if (( function_exists( 'the_ddlayout' ) ) && (defined('WC_VIEWS_ARCHIVES_LAYOUTS')))  {
		
		/** Layouts plugin activated, use Layouts */
		get_header('layouts'); 
		
	} else {
		
		/** Otherwise use the usual shop */
		get_header('shop');	
			
	}
?>
	<?php if (( function_exists( 'the_ddlayout' ) ) && (defined('WC_VIEWS_ARCHIVES_LAYOUTS'))) : ?>

		<?php the_ddlayout( 'home', array('post-loop-callback' => 'woocommerce_default_index_loop') );?>
	
	<?php else: ?>
	
			<?php
			/**
			 * woocommerce_before_main_content hook
			 *
			 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
			 * @hooked woocommerce_breadcrumb - 20
			 */
			do_action( 'woocommerce_before_main_content' );
		    ?>
	
			<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
	
				<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
	
			<?php endif; ?>
	
			<?php do_action( 'woocommerce_archive_description' ); ?>
			
	        <?php woocommerce_default_index_loop(); ?>
	
	<?php endif; ?>	
		
	<?php
	if (!( function_exists( 'the_ddlayout' ) )) {
		/**
		 * woocommerce_sidebar hook
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		do_action( 'woocommerce_sidebar' );
	}
	?>
	
<?php 
	if (( function_exists( 'the_ddlayout' ) ) && (defined('WC_VIEWS_ARCHIVES_LAYOUTS'))) {
		
		/** Layouts plugin activated, use Layouts */
		get_footer('layouts');
		
	} else {
		
		/** Otherwise use the usual shop */
		get_footer( 'shop' );
	}   
?>