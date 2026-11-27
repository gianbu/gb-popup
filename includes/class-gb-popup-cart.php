<?php
/**
 * Titolo e testo del popup nel carrello WooCommerce, sotto la lista dei prodotti.
 *
 * - Carrello a blocchi: il box viene stampato dopo il blocco della lista prodotti. Il frontend
 *   React di WooCommerce conserva così com'è l'HTML degli elementi senza un componente registrato.
 * - Carrello classico: hook woocommerce_after_cart_table.
 *
 * Il carrello non viene messo in cache, quindi qui le date si controllano in PHP.
 */

defined( 'ABSPATH' ) || exit;

class GB_Popup_Cart {

	public static function init() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		add_filter( 'render_block_woocommerce/cart-line-items-block', array( __CLASS__, 'after_block' ) );
		add_action( 'woocommerce_after_cart_table', array( __CLASS__, 'output' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Nel carrello il popup non compare, quindi il suo CSS va caricato qui per il box.
	 */
	public static function enqueue() {
		if ( is_cart() && GB_Popup::option( 'cart' ) && GB_Popup::is_running() && GB_Popup::content() ) {
			wp_enqueue_style( 'gb-popup', GB_POPUP_URL . 'assets/css/gb-popup.css', array(), GB_POPUP_VERSION );
		}
	}

	public static function html() {
		if ( ! function_exists( 'get_field' ) || ! GB_Popup::option( 'cart' ) || ! GB_Popup::is_running() ) {
			return '';
		}

		$content = GB_Popup::content();
		if ( ! $content ) {
			return '';
		}

		ob_start();
		gb_popup_template( 'cart-notice.php', array( 'notice' => $content ) );
		$html = trim( ob_get_clean() );

		return $html;
	}

	public static function after_block( $block_content ) {
		return $block_content . self::html();
	}

	public static function output() {
		echo self::html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the template
	}
}
