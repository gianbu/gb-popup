<?php
/**
 * Box nel carrello con titolo e testo del popup.
 *
 * Per personalizzarlo copia questo file in wp-content/themes/<tema>/gb-popup/cart-notice.php.
 *
 * @var array $args { @type array $notice { @type string $title, @type string $text } }
 */

defined( 'ABSPATH' ) || exit;

$notice = $args['notice'];
?>
<div class="gb-popup-cart-notice" role="note">
	<svg class="gb-popup-cart-notice__icon" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M12 11v6M12 7.5v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
	<div class="gb-popup-cart-notice__body">
		<?php if ( $notice['title'] ) : ?>
			<p class="gb-popup-cart-notice__title"><?php echo esc_html( $notice['title'] ); ?></p>
		<?php endif; ?>
		<?php echo wp_kses_post( $notice['text'] ); ?>
	</div>
</div>
