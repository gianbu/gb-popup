=== GB Popup ===
Author: Gianni Burei
Author URI: https://www.gianniburei.it
Copyright: © 2026 Gianni Burei
Requires at least: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Popup managed from an ACF options page: image + text, optional button or
form (e.g. newsletter), scheduling, cookie, page targeting and an optional
notice in the WooCommerce cart. Requires Advanced Custom Fields PRO.

== Setup ==
1. Activate the plugin (ACF PRO must be active).
2. Admin menu > Popup: fill in the Content tab, then enable it in Settings.
3. Preview while logged in: add ?gb-popup-preview to any address.

With WPML + ACFML title, text, button and form are filled in per language
(switch language from the admin bar); settings and images are copied.

== How it works ==
* One popup at a time: image on the left, title + text on the right, and an
  action below the text: none, a button with link, or any form shortcode.
* Smartphones (< 768px): one column, image as a band on top. An optional
  "Mobile image" replaces it and is shown whole (landscape 5:3 recommended).
* Display: home page only or whole site (with excluded pages). Cart and
  checkout never show the popup.
* Dates: the end date is checked in PHP and JS, the start date and the cookie
  in JS, so the popup also works with cached pages. Saving the options purges
  the most common page caches (WP Super Cache, WP Rocket, W3TC, WP Fastest
  Cache, SiteGround, LiteSpeed, Breeze) and fires `gb_popup_purge_cache`.
* Cookie gb_popup_<key>: set on close for the configured days, for a year
  after a successful form submission. The key changes when image, title,
  action or dates change, so a new campaign is shown to everyone again.
* Images are downloaded only when the popup actually opens.
* LiteSpeed Cache: gb-popup.css is excluded from Unique CSS (inlined whole,
  since the popup classes are added by JS) and gb-popup.js from JS Delayed /
  Guest Mode, so the opening delay is respected. Nothing to configure.
* WooCommerce: "Show in the cart too" repeats title and text in a box below
  the cart items (block cart and classic cart).
* Google Analytics (gtag), if present: events gb_popup_view, gb_popup_close,
  gb_popup_click, gb_popup_subscribe.

== Theme customisation ==
CSS variables (defaults in assets/css/gb-popup.css), e.g. in the theme CSS:

	.gb-popup,
	.gb-popup-cart-notice {
		--gb-popup-accent: #ffcc33;
		--gb-popup-accent-text: #111;
		--gb-popup-title-font: "Josefin Sans", sans-serif;
	}

Use the theme button style:

	add_filter( 'gb_popup_button_class', fn( $classes ) => $classes . ' btn' );

Templates: copy templates/popup.php or templates/cart-notice.php to
wp-content/themes/<theme>/gb-popup/ and edit the copy.

== Forms ==
The form receives a hidden field gb_popup_source=popup, e.g. with Contact
Form 7:

	add_action( 'wpcf7_before_send_mail', function ( $form ) {
		$from_popup = isset( $_POST['gb_popup_source'] ) && 'popup' === $_POST['gb_popup_source'];
	} );

Contact Form 7 successful submissions are detected automatically. For other
form plugins call window.gbPopup.subscribed() on success.
Label/field ids are made unique, since the same form is often in the footer.

== Hooks ==
* gb_popup_capability (string, default edit_pages): who can manage the popup.
* gb_popup_is_excluded (bool): exclude more pages.
* gb_popup_data (array): popup data before output.
* gb_popup_button_class (string): button CSS classes.
* gb_popup_form_html (string, shortcode): form HTML.
* gb_popup_template (path, file): template path.
* gb_popup_purge_cache (action): purge other caches on save.
* JS events on #gb-popup: gb-popup:open, gb-popup:close.

== Updates ==
Automatic updates from the GitHub releases of GB_POPUP_REPOSITORY (set in
gb-popup.php, can be overridden in wp-config.php). For a private repository
add a read-only token in wp-config.php:

	define( 'GB_POPUP_GITHUB_TOKEN', 'github_pat_...' );

Release: bin/release.sh X.Y.Z "Description" updates the version (header,
GB_POPUP_VERSION, Stable tag) and the changelog, commits, tags vX.Y.Z and
pushes. The GitHub Action builds gb-popup.zip (with vendor/) and attaches it
to the release. See README.md.

== Uninstall ==
Deleting the plugin removes its options (all languages). Images stay in the
media library.

== Changelog ==
= 1.0.0 =
* First release.
