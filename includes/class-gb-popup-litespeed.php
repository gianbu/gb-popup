<?php
/**
 * Compatibilità con LiteSpeed Cache (i filtri sono usati solo se il plugin è attivo).
 *
 * - Unique CSS (UCSS): il CSS ridotto contiene solo i selettori presenti nell'HTML, ma il popup
 *   è nascosto e classi come .is-open e html.gb-popup-open vengono aggiunte dal JS. Il file
 *   gb-popup.css viene quindi escluso da UCSS e inserito intero nella pagina (circa 6 KB,
 *   solo dove il popup può comparire).
 * - JS Delayed: gli script ritardati partono alla prima interazione dell'utente; escludendo
 *   gb-popup.js (circa 4 KB, già con defer) il ritardo impostato nel popup viene rispettato.
 */

defined( 'ABSPATH' ) || exit;

class GB_Popup_LiteSpeed {

	public static function init() {
		add_filter( 'litespeed_optimize_ucss_file_exc_inline', array( __CLASS__, 'exclude_css' ) );
		add_filter( 'litespeed_optm_js_defer_exc', array( __CLASS__, 'exclude_js' ) );
		add_filter( 'litespeed_optm_gm_js_exc', array( __CLASS__, 'exclude_js' ) ); // Guest Mode
	}

	public static function exclude_css( $list ) {
		return self::add( $list, 'gb-popup/assets/css/gb-popup.css' );
	}

	public static function exclude_js( $list ) {
		return self::add( $list, 'gb-popup/assets/js/gb-popup.js' );
	}

	/**
	 * Aggiunge una voce alla lista di LiteSpeed (array di stringhe confrontate con l'URL).
	 */
	private static function add( $list, $item ) {
		if ( ! is_array( $list ) ) {
			$list = array_filter( array_map( 'trim', explode( "\n", (string) $list ) ) );
		}

		if ( ! in_array( $item, $list, true ) ) {
			$list[] = $item;
		}

		return $list;
	}
}
