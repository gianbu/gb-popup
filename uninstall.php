<?php
/**
 * Rimuove le opzioni del popup salvate da ACF, comprese quelle per lingua di WPML
 * (options_gb_popup_*, options_en_gb_popup_* e i relativi riferimenti _options_*).
 * Le immagini restano nella libreria media.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$gb_popup_options = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name REGEXP '^_?options(_[a-z]{2}(-[a-z]+)?)?_gb_popup_'" );

foreach ( $gb_popup_options as $gb_popup_option ) {
	delete_option( $gb_popup_option );
}
