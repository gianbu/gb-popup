# GB Popup

WordPress plugin for a popup managed from an **ACF PRO** options page: image + text, an optional button or form (e.g. newsletter sign-up), scheduling, cookie, page targeting and an optional notice in the WooCommerce cart.

- Requires WordPress 6.5+, PHP 7.4+, [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/)
- Optional: WooCommerce (cart notice), WPML + ACFML (translated content)
- Admin interface in English and Italian

## Installation

1. Download `gb-popup.zip` from the [latest release](https://github.com/gianbu/gb-popup/releases/latest).
2. *Plugins → Add New → Upload Plugin*, then activate it (ACF PRO must be active).
3. Admin menu **Popup**: fill in the *Content* tab, then enable it in *Settings*.

Further versions arrive as normal plugin updates in the WordPress admin, straight from the GitHub releases.

**Preview** while logged in: add `?gb-popup-preview` to any address of the site to see the popup even if it is disabled or outside its dates.

## Features

| | |
|---|---|
| **Layout** | Image on the left, title + text on the right. On smartphones (< 768px) one column with the image on top. |
| **Mobile image** | Optional, replaces the main image on smartphones and is shown whole (landscape 5:3 recommended). |
| **Action** | None, a button with link, or any form shortcode (Contact Form 7, Mailchimp, Brevo…). |
| **Scheduling** | Start and end date/time. Works with cached pages: start date and cookie are checked in JS. |
| **Display** | Home page only, or whole site with excluded pages. Never in cart and checkout. |
| **Cookie** | Not shown again for N days after closing, for a year after a successful form submission. A new campaign (new image, title, action or dates) is shown to everyone again. |
| **Cart notice** | Repeats title and text below the cart items (block and classic cart). |
| **Cache** | Saving the options purges WP Super Cache, WP Rocket, W3TC, WP Fastest Cache, SiteGround, LiteSpeed, Breeze. |
| **LiteSpeed Cache** | `gb-popup.css` is excluded from Unique CSS (the popup classes are added by JS, UCSS would drop them) and `gb-popup.js` from JS Delayed / Guest Mode, so the opening delay is respected. Automatic, nothing to configure. |
| **Accessibility** | `role="dialog"`, focus trap, ESC to close, focus restored on close, `prefers-reduced-motion`. |
| **Performance** | CSS/JS loaded only where the popup can appear; images downloaded only when it opens. |

### Image ratios

- **Desktop/tablet:** the image fills its column, cropped in the centre. The area ranges from about 3:4 to 6:7 depending on the length of the text → recommended portrait 3:4, e.g. 1000×1300px.
- **Smartphone, main image:** band about 1.6–2:1, cropped in the centre.
- **Smartphone, mobile image:** shown in its own proportions up to 40% of the screen height → recommended landscape 5:3, e.g. 1000×600px.

## Theme customisation

### Colours and fonts

All styles use CSS variables (defaults in [`assets/css/gb-popup.css`](assets/css/gb-popup.css)). Override them in the theme:

```css
.gb-popup,
.gb-popup-cart-notice {
	--gb-popup-accent: #ffcc33;      /* button, close hover, list markers, cart notice border */
	--gb-popup-accent-text: #111;
	--gb-popup-title-font: "Josefin Sans", sans-serif;
	--gb-popup-radius: 0;
}
```

### Button style of the theme

```php
add_filter( 'gb_popup_button_class', fn( $classes ) => $classes . ' btn' );
```

### Templates

Copy `templates/popup.php` or `templates/cart-notice.php` to `wp-content/themes/<theme>/gb-popup/` and edit the copy.

### Forms

The form inside the popup submits a hidden field `gb_popup_source=popup`, so sign-ups from the popup can be told apart:

```php
add_action( 'wpcf7_before_send_mail', function ( $form ) {
	$from_popup = isset( $_POST['gb_popup_source'] ) && 'popup' === $_POST['gb_popup_source'];
	// e.g. a different "source" on the newsletter service
} );
```

Contact Form 7 successful submissions are detected automatically. With other form plugins call `window.gbPopup.subscribed()` on success. Label/field ids are made unique, because the same form is often in the footer too.

## Hooks

| Hook | Type | Description |
|---|---|---|
| `gb_popup_capability` | filter | Capability to manage the popup (default `edit_pages`). |
| `gb_popup_is_excluded` | filter | Exclude more pages (`bool`). |
| `gb_popup_data` | filter | Popup data before output (`array`). |
| `gb_popup_button_class` | filter | Button CSS classes. |
| `gb_popup_form_html` | filter | Form HTML (`$html, $shortcode`). |
| `gb_popup_template` | filter | Template path (`$path, $file`). |
| `gb_popup_purge_cache` | action | Purge other caches when the options are saved. |

JavaScript: `window.gbPopup.close()`, `window.gbPopup.subscribed()`; events `gb-popup:open` and `gb-popup:close` on `#gb-popup`.

Google Analytics (`gtag`), if present on the site: events `gb_popup_view`, `gb_popup_close`, `gb_popup_click`, `gb_popup_subscribe`.

## Development

```sh
composer install        # update library (vendor/ is not committed)
bin/build-zip.sh        # creates dist/gb-popup.zip for a manual install
```

Translations: `languages/gb-popup.pot` (source strings in English) and `gb-popup-it_IT.po/.mo`.

### Releasing a new version

```sh
bin/release.sh 1.0.1 "Short description of the changes"
```

The script:

1. checks that you are on `main`, with no uncommitted changes and in sync with GitHub;
2. updates the version in `gb-popup.php` (header + `GB_POPUP_VERSION`) and `readme.txt` (`Stable tag`);
3. adds the description to the changelog in `readme.txt` (or uses the entry you already wrote there for that version);
4. shows the changes and asks for confirmation;
5. commits, creates the tag `v1.0.1` and pushes both.

The tag starts the [Release workflow](.github/workflows/release.yml), which builds `gb-popup.zip` and attaches it to a GitHub release. Sites see the update within a few hours (or immediately with *Dashboard → Updates → Check again*).

Use `--dry-run` to see the changes without committing: `bin/release.sh 1.0.1 "…" --dry-run`.

## License

GPL-2.0-or-later © 2026 [Gianni Burei](https://www.gianniburei.it)
