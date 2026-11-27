<?php
/**
 * Pagina opzioni "Popup" e campi ACF.
 *
 * I campi sono registrati in PHP (non in acf-json) così sono uguali su tutti i siti,
 * le etichette sono traducibili e non serve alcuna sincronizzazione.
 * "wpml_cf_preferences": 1 = copia (impostazioni), 2 = traduci (testi) con WPML + ACFML.
 */

defined( 'ABSPATH' ) || exit;

class GB_Popup_Fields {

	const PAGE_SLUG = 'gb-popup';
	const GROUP_KEY = 'group_gb_popup';

	public static function init() {
		add_action( 'acf/init', array( __CLASS__, 'register_page' ) );
		add_action( 'acf/init', array( __CLASS__, 'register_fields' ) );
	}

	public static function register_page() {
		acf_add_options_page(
			array(
				'page_title' => __( 'Popup', 'gb-popup' ),
				'menu_title' => __( 'Popup', 'gb-popup' ),
				'menu_slug'  => self::PAGE_SLUG,
				/**
				 * Capability necessaria per gestire il popup (default: Editor e superiori).
				 */
				'capability' => apply_filters( 'gb_popup_capability', 'edit_pages' ),
				'icon_url'   => 'dashicons-megaphone',
				'position'   => 30,
				'redirect'   => false,
			)
		);
	}

	/**
	 * Campo ACF con le impostazioni comuni.
	 */
	private static function field( $name, $label, $type, $wpml, $args = array() ) {
		return array_merge(
			array(
				'key'                 => 'field_gb_popup_' . $name,
				'label'               => $label,
				'name'                => 'gb_popup_' . $name,
				'type'                => $type,
				'instructions'        => '',
				'required'            => 0,
				'conditional_logic'   => 0,
				'wrapper'             => array( 'width' => '', 'class' => '', 'id' => '' ),
				'wpml_cf_preferences' => $wpml,
			),
			$args
		);
	}

	private static function width( $width ) {
		return array( 'width' => (string) $width, 'class' => '', 'id' => '' );
	}

	private static function when( $field, $value ) {
		return array( array( array( 'field' => 'field_gb_popup_' . $field, 'operator' => '==', 'value' => $value ) ) );
	}

	public static function register_fields() {
		$woocommerce = class_exists( 'WooCommerce' );

		$help = __( 'When a visitor closes the popup it is not shown again for the number of days set below; visitors who subscribe through the popup form do not see it again for a year.', 'gb-popup' )
			. '<br>' . __( 'Changing image, title, action or dates makes it a new popup that is shown to everyone again (editing only the text does not).', 'gb-popup' );
		if ( $woocommerce ) {
			$help .= '<br>' . __( 'The popup never appears in cart and checkout.', 'gb-popup' );
		}
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$help .= '<br><br><strong>' . __( 'Translations:', 'gb-popup' ) . '</strong> ' . __( 'title, text, button and form are filled in for each language, switching language from the admin bar. In a language without title and text the popup does not appear.', 'gb-popup' );
		}
		$help .= '<br><br><strong>' . __( 'Preview:', 'gb-popup' ) . '</strong> '
			/* translators: %s: query string to add to the URL */
			. sprintf( __( 'while logged in, add %s to any address of the site to see the popup even if it is disabled or outside its dates.', 'gb-popup' ), '<code>?gb-popup-preview</code>' );

		$image_args = array(
			'return_format' => 'id',
			'library'       => 'all',
			'max_size'      => '1',
			'mime_types'    => 'jpg, jpeg, png, webp',
			'preview_size'  => 'medium',
			'wrapper'       => self::width( 50 ),
		);

		$fields = array(
			self::field( 'tab_settings', __( 'Settings', 'gb-popup' ), 'tab', 0, array( 'name' => '', 'placement' => 'top' ) ),
			self::field( 'help', __( 'Instructions', 'gb-popup' ), 'message', 0, array( 'name' => '', 'message' => $help, 'new_lines' => '', 'esc_html' => 0 ) ),
			self::field( 'enabled', __( 'Popup enabled', 'gb-popup' ), 'true_false', 1, array( 'ui' => 1, 'ui_on_text' => __( 'Yes', 'gb-popup' ), 'ui_off_text' => __( 'No', 'gb-popup' ), 'wrapper' => self::width( 33 ) ) ),
			self::field( 'start', __( 'Start date', 'gb-popup' ), 'date_time_picker', 1, array( 'instructions' => __( 'Leave empty to show it right away.', 'gb-popup' ), 'display_format' => 'd/m/Y H:i', 'return_format' => 'Y-m-d H:i:s', 'first_day' => 1, 'wrapper' => self::width( 33 ) ) ),
			self::field( 'end', __( 'End date', 'gb-popup' ), 'date_time_picker', 1, array( 'instructions' => __( 'After this date the popup is no longer shown.', 'gb-popup' ), 'display_format' => 'd/m/Y H:i', 'return_format' => 'Y-m-d H:i:s', 'first_day' => 1, 'wrapper' => self::width( 33 ) ) ),
			self::field( 'delay', __( 'Opening delay', 'gb-popup' ), 'number', 1, array( 'instructions' => __( 'Seconds to wait after the page has loaded.', 'gb-popup' ), 'default_value' => 3, 'min' => 0, 'max' => 60, 'step' => 1, 'append' => __( 'seconds', 'gb-popup' ), 'wrapper' => self::width( 50 ) ) ),
			self::field( 'cookie_days', __( 'Do not show again for', 'gb-popup' ), 'number', 1, array( 'instructions' => __( 'Days during which the popup does not reappear after the visitor has closed it.', 'gb-popup' ), 'default_value' => 7, 'min' => 1, 'max' => 365, 'step' => 1, 'append' => __( 'days', 'gb-popup' ), 'wrapper' => self::width( 50 ) ) ),
			self::field( 'display', __( 'Where to show it', 'gb-popup' ), 'radio', 1, array( 'choices' => array( 'home' => __( 'Home page only', 'gb-popup' ), 'all' => __( 'Whole site', 'gb-popup' ) ), 'default_value' => 'home', 'return_format' => 'value', 'layout' => 'horizontal' ) ),
			self::field( 'excluded', __( 'Excluded pages', 'gb-popup' ), 'relationship', 1, array( 'instructions' => __( 'Pages where the popup must not appear.', 'gb-popup' ), 'post_type' => array( 'page' ), 'filters' => array( 'search' ), 'return_format' => 'id', 'conditional_logic' => self::when( 'display', 'all' ) ) ),

			self::field( 'tab_content', __( 'Content', 'gb-popup' ), 'tab', 0, array( 'name' => '', 'placement' => 'top' ) ),
			self::field( 'image', __( 'Image', 'gb-popup' ), 'image', 1, array_merge( $image_args, array( 'instructions' => __( 'Shown on the left of the text, cropped to fill the space (about 3:4 to 6:7 depending on the text). Recommended: portrait 3:4, e.g. 1000×1300px, subject in the centre. Max 1MB. Alt text is set in the media library.', 'gb-popup' ) ) ) ),
			self::field( 'image_mobile', __( 'Mobile image', 'gb-popup' ), 'image', 1, array_merge( $image_args, array( 'instructions' => __( 'Optional: replaces the image on smartphones (below 768px), where it is shown above the text, whole and uncropped (up to 40% of the screen height). Recommended: landscape 5:3, e.g. 1000×600px. If empty, the main image is used as a band cropped in the centre.', 'gb-popup' ) ) ) ),
			self::field( 'title', __( 'Title', 'gb-popup' ), 'text', 2 ),
			self::field( 'text', __( 'Text', 'gb-popup' ), 'wysiwyg', 2, array( 'instructions' => __( 'Keep it short: bold, italic, lists and links are supported.', 'gb-popup' ), 'tabs' => 'visual', 'toolbar' => 'basic', 'media_upload' => 0, 'delay' => 0 ) ),
			self::field( 'action', __( 'Action', 'gb-popup' ), 'radio', 1, array(
				'instructions'  => __( 'What to show below the text.', 'gb-popup' ),
				'choices'       => array(
					'none'   => __( 'None (text only)', 'gb-popup' ),
					'button' => __( 'Button with link', 'gb-popup' ),
					'form'   => __( 'Form (e.g. newsletter sign-up)', 'gb-popup' ),
				),
				'default_value' => 'none',
				'return_format' => 'value',
				'layout'        => 'vertical',
			) ),
			self::field( 'button', __( 'Button', 'gb-popup' ), 'link', 2, array( 'required' => 1, 'instructions' => __( 'Button text and destination. UTM parameters are recommended (e.g. ?utm_source=site&utm_medium=popup&utm_campaign=christmas).', 'gb-popup' ), 'return_format' => 'array', 'conditional_logic' => self::when( 'action', 'button' ) ) ),
			self::field( 'form', __( 'Form shortcode', 'gb-popup' ), 'text', 2, array( 'required' => 1, 'instructions' => __( 'Shortcode of the form to show, e.g. [contact-form-7 id="123"]. The form submits a hidden field gb_popup_source=popup, so sign-ups from the popup can be told apart.', 'gb-popup' ), 'conditional_logic' => self::when( 'action', 'form' ) ) ),
		);

		if ( $woocommerce ) {
			$fields[] = self::field( 'cart', __( 'Show in the cart too', 'gb-popup' ), 'true_false', 1, array( 'instructions' => __( 'Repeats title and text in a box in the cart, below the list of products (no image, no action). It follows the same dates as the popup.', 'gb-popup' ), 'ui' => 1, 'ui_on_text' => __( 'Yes', 'gb-popup' ), 'ui_off_text' => __( 'No', 'gb-popup' ) ) );
		}

		acf_add_local_field_group(
			array(
				'key'                   => self::GROUP_KEY,
				'title'                 => __( 'Popup', 'gb-popup' ),
				'fields'                => $fields,
				'location'              => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => self::PAGE_SLUG ) ) ),
				'style'                 => 'seamless',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'active'                => true,
				'acfml_field_group_mode' => 'advanced',
			)
		);
	}
}
