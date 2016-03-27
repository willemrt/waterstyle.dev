<?php
/**
 * WC Dynamic Gallery Functions
 *
 * Table Of Contents
 *
 * reset_products_galleries_activate()
 * add_google_fonts()
 * html2rgb()
 * a3_wp_admin()
 * wc_dynamic_gallery_extension()
 * plugin_extra_links()
 */
class WC_Dynamic_Gallery_Functions
{

	public static function reset_products_galleries_activate() {
		global $wpdb;
		$wpdb->query( "DELETE FROM ".$wpdb->postmeta." WHERE meta_key='_actived_d_gallery' " );
	}

	public static function add_google_fonts() {
		global $wc_dgallery_fonts_face;

		$caption_font = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'caption_font' );

		$navbar_font = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'navbar_font' );

		$google_fonts = array( $caption_font['face'], $navbar_font['face'] );
		$wc_dgallery_fonts_face->generate_google_webfonts( $google_fonts );
	}

	public static function get_no_image_uri() {
		$no_image_uri = apply_filters( 'wc_dg_no_image_uri', WOO_DYNAMIC_GALLERY_JS_URL . '/mygallery/no-image.png' );

		return $no_image_uri;
	}

	public static function get_gallery_ids( $post_id = 0 ) {
		$a3_dynamic_gallery_db_version = get_option( 'a3_dynamic_gallery_db_version', '1.0.0' );
		if ( $post_id < 1 ) return array();

		$post_type = get_post_type( $post_id );
		if ( false === $post_type ) {
			return array();
		}

		if ( 'product' != $post_type ) {
			return array();
		}

		// Back Compatibility for DB version less than 2.1.0
		if ( version_compare( $a3_dynamic_gallery_db_version, '2.1.0', '<' ) ) {
			return self::get_gallery_ids_back_compatibility( $post_id );
		}

		$have_gallery_ids = false;

		// Use the WooCommerce Default Gallery
		$dgallery_ids = get_post_meta( $post_id, '_product_image_gallery', true );
		if ( ! empty( $dgallery_ids ) && '' != trim( $dgallery_ids ) ) {
			$dgallery_ids = explode( ',', $dgallery_ids );

			if ( count( $dgallery_ids ) > 0 ) {
				$have_gallery_ids = true;
			}
		}

		if ( $have_gallery_ids ) {

			foreach ( $dgallery_ids as $img_id ) {
				// Remove image id if it is not image
				if ( ! wp_attachment_is_image( $img_id ) ) {
					$dgallery_ids = array_diff( $dgallery_ids, array( $img_id ) );
				}
			}

			if ( count( $dgallery_ids ) > 0 ) {
				return $dgallery_ids;
			}

		}

		// set dgallery_ids to empty array if don't have Woo Default Gallery
		$dgallery_ids = array();

		$featured_img_id = (int) get_post_meta( $post_id, '_thumbnail_id', true );
		if ( ! empty( $featured_img_id ) && $featured_img_id > 0 ) {
			$dgallery_ids[] = $featured_img_id;
		}

		$attached_images = (array) get_posts( array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'numberposts'    => -1,
			'post_status'    => null,
			'post_parent'    => $post_id,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'exclude'        => array( $featured_img_id ),
		) );

		if ( is_array( $attached_images ) && count( $attached_images ) > 0 ) {
			foreach ( $attached_images as $item_thumb ) {
				$is_excluded   = get_post_meta( $item_thumb->ID, '_woocommerce_exclude_image', true );

				// Don't get if this image is excluded on main gallery
				if ( 1 == $is_excluded ) continue;

				$dgallery_ids[]    = $item_thumb->ID;
			}
		}

		return $dgallery_ids;
	}

	public static function get_gallery_ids_back_compatibility( $post_id = 0 ) {

		if ( $post_id < 1 ) return array();

		$have_gallery_ids = false;

		// 1.6.1: a3 gallery custom field for Product support for sort and new UI uploader
		$dgallery_ids = get_post_meta( $post_id, '_a3_dgallery', true );
		if ( ! empty( $dgallery_ids ) && '' != trim( $dgallery_ids ) ) {
			$dgallery_ids = explode( ',', $dgallery_ids );
			if ( count( $dgallery_ids ) > 0 ) {
				$have_gallery_ids = true;
			}
		}

		// Use the WooCommerce Default Gallery if don't have a3 dynamic gallery
		if ( ! $have_gallery_ids ) {

			$dgallery_ids = get_post_meta( $post_id, '_product_image_gallery', true );
			if ( ! empty( $dgallery_ids ) && '' != trim( $dgallery_ids ) ) {
				$dgallery_ids = explode( ',', $dgallery_ids );

				if ( count( $dgallery_ids ) > 0 ) {
					$have_gallery_ids = true;
				}
			}

		}

		if ( $have_gallery_ids ) {

			foreach ( $dgallery_ids as $img_id ) {
				// Remove image id if it is not image
				if ( ! wp_attachment_is_image( $img_id ) ) {
					$dgallery_ids = array_diff( $dgallery_ids, array( $img_id ) );
				}
			}

			if ( count( $dgallery_ids ) > 0 ) {
				return $dgallery_ids;
			}

		}

		// set dgallery_ids to empty array if don't have gallery above
		$dgallery_ids = array();

		$featured_img_id = (int) get_post_meta( $post_id, '_thumbnail_id', true );
		if ( ! empty( $featured_img_id ) && $featured_img_id > 0 ) {
			$dgallery_ids[] = $featured_img_id;
		}

		$attached_images = (array) get_posts( array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'numberposts'    => -1,
			'post_status'    => null,
			'post_parent'    => $post_id,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'exclude'        => array( $featured_img_id ),
		) );

		if ( is_array( $attached_images ) && count( $attached_images ) > 0 ) {
			foreach ( $attached_images as $item_thumb ) {
				$is_excluded   = get_post_meta( $item_thumb->ID, '_woocommerce_exclude_image', true );

				// Don't get if this image is excluded on main gallery
				if ( 1 == $is_excluded ) continue;

				$dgallery_ids[]    = $item_thumb->ID;
			}
		}

		return $dgallery_ids;
	}

	public static function a3_wp_admin() {
		wp_enqueue_style( 'a3rev-wp-admin-style', WOO_DYNAMIC_GALLERY_CSS_URL . '/a3_wp_admin.css' );
	}

	public static function plugin_extra_links($links, $plugin_name) {
		if ( $plugin_name != WOO_DYNAMIC_GALLERY_NAME) {
			return $links;
		}
		$links[] = '<a href="http://docs.a3rev.com/user-guides/woocommerce/woo-dynamic-gallery/" target="_blank">'.__('Documentation', 'woo_dgallery').'</a>';
		$links[] = '<a href="http://wordpress.org/support/plugin/woocommerce-dynamic-gallery/" target="_blank">'.__('Support', 'woo_dgallery').'</a>';
		return $links;
	}

	public static function settings_plugin_links($actions) {
		$actions = array_merge( array( 'settings' => '<a href="admin.php?page=woo-dynamic-gallery">' . __( 'Settings', 'woo_dgallery' ) . '</a>' ), $actions );

		return $actions;
	}

	public static function plugin_extension_box( $boxes = array() ) {
		global $wc_dgallery_admin_init;

		$support_box = '<a href="'.$wc_dgallery_admin_init->support_url.'" target="_blank" alt="'.__('Go to Support Forum', 'woo_dgallery').'"><img src="'.WOO_DYNAMIC_GALLERY_IMAGES_URL.'/go-to-support-forum.png" /></a>';

		$boxes[] = array(
			'content' => $support_box,
			'css' => 'border: none; padding: 0; background: none;'
		);

		$review_box = '<div style="margin-bottom: 5px; font-size: 12px;"><strong>' . __('Is this plugin is just what you needed? If so', 'woo_dgallery') . '</strong></div>';
        $review_box .= '<a href="https://wordpress.org/support/view/plugin-reviews/woocommerce-dynamic-gallery#postform" target="_blank" alt="'.__('Submit Review for Plugin on WordPress', 'woo_dgallery').'"><img src="'.WOO_DYNAMIC_GALLERY_IMAGES_URL.'/a-5-star-rating-would-be-appreciated.png" /></a>';

        $boxes[] = array(
            'content' => $review_box,
            'css' => 'border: none; padding: 0; background: none;'
        );

		$pro_box = '<a href="'.$wc_dgallery_admin_init->pro_plugin_page_url.'" target="_blank" alt="'.__('Product Dynamic Gallery Pro', 'woo_dgallery').'"><img src="'.WOO_DYNAMIC_GALLERY_IMAGES_URL.'/pro-version.jpg" /></a>';

		$boxes[] = array(
			'content' => $pro_box,
			'css' => 'border: none; padding: 0; background: none;'
		);

		$free_woocommerce_box = '<a href="https://profiles.wordpress.org/a3rev/#content-plugins" target="_blank" alt="'.__('Free WooCommerce Plugins', 'woo_dgallery').'"><img src="'.WOO_DYNAMIC_GALLERY_IMAGES_URL.'/free-woocommerce-plugins.png" /></a>';

		$boxes[] = array(
			'content' => $free_woocommerce_box,
			'css' => 'border: none; padding: 0; background: none;'
		);

		$free_wordpress_box = '<a href="https://profiles.wordpress.org/a3rev/#content-plugins" target="_blank" alt="'.__('Free WordPress Plugins', 'woo_dgallery').'"><img src="'.WOO_DYNAMIC_GALLERY_IMAGES_URL.'/free-wordpress-plugins.png" /></a>';

		$boxes[] = array(
			'content' => $free_wordpress_box,
			'css' => 'border: none; padding: 0; background: none;'
		);

		$connect_box = '<div style="margin-bottom: 5px;">' . __('Connect with us via','woo_dgallery') . '</div>';
		$connect_box .= '<a href="https://www.facebook.com/a3rev" target="_blank" alt="'.__('a3rev Facebook', 'woo_dgallery').'" style="margin-right: 5px;"><img src="'.WOO_DYNAMIC_GALLERY_IMAGES_URL.'/follow-facebook.png" /></a> ';
		$connect_box .= '<a href="https://twitter.com/a3rev" target="_blank" alt="'.__('a3rev Twitter', 'woo_dgallery').'"><img src="'.WOO_DYNAMIC_GALLERY_IMAGES_URL.'/follow-twitter.png" /></a>';

		$boxes[] = array(
			'content' => $connect_box,
			'css' => 'border-color: #3a5795;'
		);

		return $boxes;
	}
}
?>
