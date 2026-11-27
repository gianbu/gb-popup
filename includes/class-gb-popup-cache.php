<?php
/**
 * Svuota la cache delle pagine quando si salvano le opzioni del popup,
 * altrimenti le pagine in cache continuerebbero a servire il popup precedente.
 */

defined( 'ABSPATH' ) || exit;

class GB_Popup_Cache {

	public static function init() {
		add_action( 'acf/save_post', array( __CLASS__, 'maybe_purge' ), 20 );
	}

	public static function maybe_purge( $post_id ) {
		// Con WPML il post_id delle opzioni può essere "options_en" ecc.
		if ( ! is_string( $post_id ) || 0 !== strpos( $post_id, 'option' ) ) {
			return;
		}

		if ( ! isset( $_GET['page'] ) || GB_Popup_Fields::PAGE_SLUG !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		self::purge();
	}

	public static function purge() {
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache(); // WP Super Cache
		}
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain(); // WP Rocket
		}
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all(); // W3 Total Cache
		}
		if ( function_exists( 'wpfc_clear_all_cache' ) ) {
			wpfc_clear_all_cache( true ); // WP Fastest Cache
		}
		if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache(); // SiteGround Optimizer
		}
		do_action( 'litespeed_purge_all' ); // LiteSpeed Cache
		do_action( 'breeze_clear_all_cache' ); // Breeze (Cloudways)

		/**
		 * Per svuotare altre cache (es. quella del server).
		 */
		do_action( 'gb_popup_purge_cache' );
	}
}
