<?php
/**
 * Form nel popup (shortcode di qualsiasi plugin: Contact Form 7, Mailchimp, Brevo...).
 *
 * - Aggiunge il campo nascosto gb_popup_source=popup, per distinguere le iscrizioni dal popup
 *   (es. in un hook wpcf7_before_send_mail: $_POST['gb_popup_source'] === 'popup').
 * - Rende univoci gli id dei campi collegati alle label: lo stesso form può essere già
 *   presente nella pagina (es. nel footer).
 */

defined( 'ABSPATH' ) || exit;

class GB_Popup_Form {

	public static function render( $shortcode ) {
		$html = do_shortcode( $shortcode );

		if ( '' === trim( $html ) ) {
			return '';
		}

		$html = self::unique_ids( $html );

		if ( false !== stripos( $html, '</form>' ) ) {
			$html = preg_replace( '#</form>#i', '<input type="hidden" name="gb_popup_source" value="popup"></form>', $html, 1 );
		}

		/**
		 * HTML del form nel popup.
		 *
		 * @param string $html      HTML del form.
		 * @param string $shortcode Shortcode inserito nelle opzioni.
		 */
		return apply_filters( 'gb_popup_form_html', $html, $shortcode );
	}

	/**
	 * Per ogni label for="x": se esiste id="x" lo rinomina in gb-popup-x; se non esiste
	 * (es. CF7 omette gli id già usati nella pagina) assegna l'id al campo con name="x".
	 */
	private static function unique_ids( $html ) {
		if ( ! preg_match_all( '/\sfor="([^"]+)"/', $html, $matches ) ) {
			return $html;
		}

		foreach ( array_unique( $matches[1] ) as $id ) {
			$new = 'gb-popup-' . $id;
			$q   = preg_quote( $id, '/' );

			$html = preg_replace( '/(\sfor=")' . $q . '"/', '$1' . $new . '"', $html );

			if ( preg_match( '/\sid="' . $q . '"/', $html ) ) {
				$html = preg_replace( '/(\sid=")' . $q . '"/', '$1' . $new . '"', $html );
			} else {
				$html = preg_replace( '/(<(?:input|select|textarea)\b[^>]*\sname="' . $q . '")/', '$1 id="' . $new . '"', $html, 1 );
			}
		}

		return $html;
	}
}
