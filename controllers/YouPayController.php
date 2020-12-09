<?php

namespace WooYouPay\controllers;

use WooYouPay\bootstrap\Loader;

/**
 * Class YouPayController
 *
 * @package WooYouPay\controllers
 */
class YouPayController {

	use LoaderTrait;

	/**
	 * loader
	 *
	 * @param Loader $loader main loader var.
	 */
	public function loader( Loader $loader ) {
        $loader->add_action( 'init', $this,
            'register_shortcodes', 10 );

		if ( empty( $this->youpay->settings['woocommerce']['enabled'] ) || 'yes' !== $this->youpay->settings['woocommerce']['enabled'] ) {
			return;
		}

        $loader->add_action( 'add_meta_boxes', $this,
            'mv_add_meta_boxes', 20, 2 );

		if ( ! empty( $this->youpay->settings['woocommerce']['product-pages-hook'] ) ) {
			$priority = (int) $this->youpay->settings['woocommerce']['product-pages-priority'];
			if ( empty( $priority ) ) {
				$priority = 10;
			}
			if ( true === $this->youpay->settings['woocommerce']['show-info-on-product-pages'] || 'yes' === $this->youpay->settings['woocommerce']['show-info-on-product-pages'] ) {
				$loader->add_filter( $this->youpay->settings['woocommerce']['product-pages-hook'], $this,
					'youpay_text', $priority, 1 );
			}
		}

		if ( ! empty( $this->youpay->settings['woocommerce']['show-info-on-cart-page'] ) && (
			$this->youpay->settings['woocommerce']['show-info-on-cart-page'] === true || $this->youpay->settings['woocommerce']['show-info-on-cart-page'] === 'yes'
			)
		) {

			$loader->add_action( 'woocommerce_cart_totals_after_order_total', $this,
                'show_text_on_cart_page', 10, 0 );
		}

	}

	/**
	 * Show YouPay Content on Cart Page
	 */
	public function show_text_on_cart_page() {
		if ( empty( $this->youpay->settings['woocommerce']['cart-page-info-text'] ) ) {
			return;
		}

		$html = $this->youpay->settings['woocommerce']['cart-page-info-text'];
		$html = do_shortcode( $html );

		// Allow other plugins to maniplulate or replace the HTML echoed by this funtion.
		echo apply_filters( 'youpay_html_on_cart_page', $html );
	}

	/**
	 * YouPay Text
	 *
	 * @param string $desc Description Text.
	 * @return string
	 */
	public function youpay_text( $desc ) {
		if ( is_product() && $this->youpay->settings['woocommerce']['show-info-on-product-pages'] ) {
			echo do_shortcode( $this->youpay->settings['woocommerce']['product-pages-info-text'] );
		}
		return $desc;
	}

	/**
	 * Register the shortcodes
	 */
	public function register_shortcodes() {
		add_shortcode( 'youpay-popup', array( $this, 'youpay_popup' ) );
		add_shortcode( 'youpay-success', array( $this, 'youpay_success' ) );
	}

	/**
	 * Do the YouPay Shortcode
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function youpay_popup( $atts ) {
		extract(
			shortcode_atts(
				array(
					'short' => false,
				),
				$atts
			)
		);

		$script = '<script src="' . $this->youpay->api->api_url . 'popup.js?version=' . time() . '"></script>';
		if ( empty( $short ) ) {
			return '<div id="youpay-popup"></div>' . $script;
		}
		return '<div id="youpay-popup" data-short="true"></div>' . $script;
	}

	/**
	 * Get the default success page html content
	 */
	public function youpay_success() {
		ob_start();
		// include the specified file.
		include YOUPAY_PLUGIN_PATH . '/resources/views/payment-success.php';
		// assign the file output to $content variable and clean buffer.
		$content = ob_get_clean();
		return $content;
	}

	/**
	 * Add YouPay Metabox on YouPay Orders : Admin Area
	 *
	 * @param $screen
	 * @param \WP_Post $post The Post Object.
	 */
	function mv_add_meta_boxes( $screen, \WP_Post $post ) {
		if ( $post->post_type === 'shop_order' ) {
			$youpay_order_id = get_post_meta( $post->ID, 'youpay_order_id', true );
			if ( ! empty( $youpay_order_id ) ) {
				add_meta_box(
					'youpay_order_metabox',
					__( 'YouPay', 'woocommerce' ),
					array( $this, 'mv_add_other_fields_for_packaging' ),
					'shop_order',
					'normal',
					'high'
				);
			}
		}
	}

	/**
	 * Show the YouPay box on YouPay orders : Admin Area
	 */
	function mv_add_other_fields_for_packaging() {
		global $post;

		$youpay_order_id = get_post_meta( $post->ID, 'youpay_order_id', true );
		$order_link      = $this->youpay->api->api_url . 'resources/orders/' . $youpay_order_id;

		// TODO: Show more order information in this box
		// $order_details = $this->youpay->api->getOrder($youpay_order_id);

		require_once YOUPAY_PLUGIN_PATH . 'resources/views/order-metabox.php';
	}

}
