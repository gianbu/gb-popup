<?php
/**
 * Popup: dati, caricamento degli asset e stampa del markup nel footer.
 *
 * La data di inizio e il cookie vengono controllati in JS, così il popup funziona
 * anche con le pagine servite da una cache. In PHP si scartano solo i casi certi
 * (disattivato, scaduto, pagina esclusa) per non stampare markup inutile.
 */

defined( 'ABSPATH' ) || exit;

class GB_Popup {

	/** @var array|null|false Dati calcolati una volta per richiesta (false = non ancora calcolati). */
	private static $data = false;

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_footer', array( __CLASS__, 'render' ), 5 );
	}

	/**
	 * Valore di un campo della pagina opzioni.
	 */
	public static function option( $name ) {
		return get_field( 'gb_popup_' . $name, 'option' );
	}

	/**
	 * Data ACF (Y-m-d H:i:s, fuso orario del sito) in timestamp Unix.
	 */
	public static function timestamp( $value ) {
		if ( empty( $value ) ) {
			return 0;
		}

		$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $value, wp_timezone() );

		return $date ? $date->getTimestamp() : 0;
	}

	/**
	 * Titolo e testo, se c'è almeno uno dei due (altrimenti null).
	 */
	public static function content() {
		$title = trim( (string) self::option( 'title' ) );
		$text  = (string) self::option( 'text' );

		if ( '' === $title && '' === trim( wp_strip_all_tags( $text ) ) ) {
			return null;
		}

		return array( 'title' => $title, 'text' => $text );
	}

	/**
	 * Campagna in corso adesso (attiva e dentro le date)?
	 */
	public static function is_running() {
		if ( ! self::option( 'enabled' ) ) {
			return false;
		}

		$start = self::timestamp( self::option( 'start' ) );
		$end   = self::timestamp( self::option( 'end' ) );

		return ! ( ( $start && $start > time() ) || ( $end && $end < time() ) );
	}

	public static function is_preview() {
		return isset( $_GET['gb-popup-preview'] ) && current_user_can( apply_filters( 'gb_popup_capability', 'edit_pages' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Pagina corrente esclusa? Carrello e checkout sempre, poi "solo home page" o le pagine escluse.
	 */
	private static function is_excluded_page() {
		if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
			return true;
		}

		if ( 'all' !== ( self::option( 'display' ) ?: 'home' ) ) {
			return ! is_front_page();
		}

		$excluded = false;

		if ( is_singular() ) {
			$ids = array_map( 'intval', (array) self::option( 'excluded' ) );
			if ( $ids ) {
				// Confronto sia l'ID corrente sia quello nella lingua principale (WPML)
				$id       = get_queried_object_id();
				$original = (int) apply_filters( 'wpml_object_id', $id, get_post_type( $id ), true, apply_filters( 'wpml_default_language', null ) );
				$excluded = in_array( $id, $ids, true ) || in_array( $original, $ids, true );
			}
		}

		/**
		 * Permette di escludere altre pagine, ad es. per template o tipo di contenuto.
		 *
		 * @param bool $excluded Pagina esclusa.
		 */
		return (bool) apply_filters( 'gb_popup_is_excluded', $excluded );
	}

	/**
	 * Dati del popup da mostrare nella pagina corrente, oppure null.
	 */
	public static function get() {
		if ( false !== self::$data ) {
			return self::$data;
		}

		self::$data = null;

		if ( is_admin() || wp_doing_ajax() || ! function_exists( 'get_field' ) ) {
			return null;
		}

		$preview = self::is_preview();

		if ( ! $preview && ! self::option( 'enabled' ) ) {
			return null;
		}

		$content = self::content();
		if ( ! $content ) {
			return null;
		}

		$start = self::timestamp( self::option( 'start' ) );
		$end   = self::timestamp( self::option( 'end' ) );

		if ( ! $preview && ( ( $end && $end < time() ) || self::is_excluded_page() ) ) {
			return null;
		}

		$action = self::option( 'action' ) ?: 'none';
		$button = self::option( 'button' );
		$button = ( 'button' === $action && is_array( $button ) && ! empty( $button['url'] ) ) ? $button : null;
		$form   = 'form' === $action ? trim( (string) self::option( 'form' ) ) : '';

		if ( ( 'button' === $action && ! $button ) || ( 'form' === $action && '' === $form ) ) {
			$action = 'none';
		}

		// Se c'è solo l'immagine mobile la uso ovunque
		$image        = (int) self::option( 'image' );
		$image_mobile = (int) self::option( 'image_mobile' );
		if ( ! $image ) {
			$image        = $image_mobile;
			$image_mobile = 0;
		}
		if ( $image_mobile === $image ) {
			$image_mobile = 0;
		}

		$delay = self::option( 'delay' );
		$delay = ( '' === $delay || null === $delay || false === $delay ) ? 3 : max( 0, (int) $delay );

		// La chiave cambia quando cambia la campagna: il nuovo popup ricompare anche a chi
		// aveva chiuso il precedente. Il testo è escluso, così correggere un refuso non lo ripropone.
		$key = substr( md5( implode( '|', array( $image, $image_mobile, $content['title'], $action, $button['url'] ?? '', $form, $start, $end ) ) ), 0, 8 );

		/**
		 * Dati del popup prima della stampa.
		 *
		 * @param array $data Dati del popup.
		 */
		self::$data = apply_filters(
			'gb_popup_data',
			array(
				'key'          => $key,
				'preview'      => $preview,
				'start'        => $start,
				'end'          => $end,
				'delay'        => $delay,
				'days'         => max( 1, (int) ( self::option( 'cookie_days' ) ?: 7 ) ),
				'image'        => $image,
				'image_mobile' => $image_mobile,
				'title'        => $content['title'],
				'text'         => $content['text'],
				'action'       => $action,
				'button'       => $button,
				'form'         => $form,
			)
		);

		return self::$data;
	}

	public static function enqueue() {
		if ( ! self::get() ) {
			return;
		}

		wp_enqueue_style( 'gb-popup', GB_POPUP_URL . 'assets/css/gb-popup.css', array(), GB_POPUP_VERSION );
		wp_enqueue_script( 'gb-popup', GB_POPUP_URL . 'assets/js/gb-popup.js', array(), GB_POPUP_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}

	public static function render() {
		$popup = self::get();
		if ( ! $popup ) {
			return;
		}

		gb_popup_template( 'popup.php', array( 'popup' => $popup ) );
	}
}
