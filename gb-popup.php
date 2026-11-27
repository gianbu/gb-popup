<?php
/**
 * Plugin Name:       GB Popup
 * Description:       Site-wide popup managed from an ACF options page: image + text, optional button or newsletter form, scheduling, cookie, page targeting and an optional notice in the WooCommerce cart.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Gianni Burei
 * Author URI:        https://www.gianniburei.it
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gb-popup
 * Domain Path:       /languages
 * WC tested up to:   11.2
 *
 * Copyright © 2026 Gianni Burei
 *
 * Requires Advanced Custom Fields PRO (options pages). ACF PRO is not on
 * wordpress.org, so it cannot be declared with "Requires Plugins": the
 * dependency is checked at runtime and an admin notice is shown if missing.
 */

defined( 'ABSPATH' ) || exit;

define( 'GB_POPUP_VERSION', '1.0.0' );
define( 'GB_POPUP_FILE', __FILE__ );
define( 'GB_POPUP_PATH', plugin_dir_path( __FILE__ ) );
define( 'GB_POPUP_URL', plugin_dir_url( __FILE__ ) );

/*
 * Repository GitHub per gli aggiornamenti automatici.
 * Si può sovrascrivere in wp-config.php; per un repository privato serve anche
 * define( 'GB_POPUP_GITHUB_TOKEN', 'github_pat_...' ); (token in sola lettura).
 */
if ( ! defined( 'GB_POPUP_REPOSITORY' ) ) {
	define( 'GB_POPUP_REPOSITORY', 'https://github.com/gianbu/gb-popup/' );
}

require_once GB_POPUP_PATH . 'includes/class-gb-popup-fields.php';
require_once GB_POPUP_PATH . 'includes/class-gb-popup.php';
require_once GB_POPUP_PATH . 'includes/class-gb-popup-form.php';
require_once GB_POPUP_PATH . 'includes/class-gb-popup-cart.php';
require_once GB_POPUP_PATH . 'includes/class-gb-popup-cache.php';
require_once GB_POPUP_PATH . 'includes/functions.php';

add_action( 'init', function () {
	load_plugin_textdomain( 'gb-popup', false, dirname( plugin_basename( GB_POPUP_FILE ) ) . '/languages' );
}, 0 );

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
} );

add_action( 'plugins_loaded', function () {
	gb_popup_init_updater();

	// Le pagine opzioni esistono solo in ACF PRO
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		add_action( 'admin_notices', function () {
			if ( current_user_can( 'activate_plugins' ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'GB Popup requires Advanced Custom Fields PRO.', 'gb-popup' ) . '</p></div>';
			}
		} );
		return;
	}

	GB_Popup_Fields::init();
	GB_Popup::init();
	GB_Popup_Cart::init();
	GB_Popup_Cache::init();
}, 20 );


/**
 * Aggiornamenti automatici dalle release del repository GitHub (Plugin Update Checker).
 * Il plugin cerca l'ultima release: lo zip allegato alla release, se presente, altrimenti
 * il sorgente del tag.
 */
function gb_popup_init_updater() {
	if ( false !== strpos( GB_POPUP_REPOSITORY, 'GITHUB_USER' ) || ! file_exists( GB_POPUP_PATH . 'vendor/autoload.php' ) ) {
		return;
	}

	require_once GB_POPUP_PATH . 'vendor/autoload.php';

	$checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker( GB_POPUP_REPOSITORY, GB_POPUP_FILE, 'gb-popup' );

	if ( defined( 'GB_POPUP_GITHUB_TOKEN' ) && GB_POPUP_GITHUB_TOKEN ) {
		$checker->setAuthentication( GB_POPUP_GITHUB_TOKEN );
	}

	$api = $checker->getVcsApi();
	if ( method_exists( $api, 'enableReleaseAssets' ) ) {
		$api->enableReleaseAssets( '/gb-popup\.zip$/i' );
	}
}
