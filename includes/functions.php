<?php
/**
 * Funzioni di supporto.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Carica un template del plugin. Il tema può sovrascriverlo copiandolo in
 * wp-content/themes/<tema>/gb-popup/<file>.
 *
 * @param string $file Nome del file in templates/.
 * @param array  $args Variabili disponibili nel template come $args.
 */
function gb_popup_template( $file, $args = array() ) {
	$template = locate_template( 'gb-popup/' . $file );

	if ( ! $template ) {
		$template = GB_POPUP_PATH . 'templates/' . $file;
	}

	/**
	 * Percorso del template da usare.
	 *
	 * @param string $template Percorso completo.
	 * @param string $file     Nome del file richiesto.
	 */
	$template = apply_filters( 'gb_popup_template', $template, $file );

	if ( $template && file_exists( $template ) ) {
		load_template( $template, false, $args );
	}
}

/**
 * Classi CSS del pulsante del popup. Il tema può aggiungere la propria classe
 * pulsante con il filtro, es. add_filter( 'gb_popup_button_class', fn( $c ) => $c . ' btn' );
 */
function gb_popup_button_class() {
	return (string) apply_filters( 'gb_popup_button_class', 'gb-popup__button' );
}
