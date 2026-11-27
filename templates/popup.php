<?php
/**
 * Popup (finestra modale).
 *
 * Per personalizzarlo copia questo file in wp-content/themes/<tema>/gb-popup/popup.php.
 * Le immagini sono in data-src/data-srcset e vengono caricate dal JS solo quando il popup
 * deve davvero aprirsi: chi lo ha già chiuso non le scarica.
 *
 * @var array $args { @type array $popup Dati del popup, vedi GB_Popup::get(). }
 */

defined( 'ABSPATH' ) || exit;

$popup = $args['popup'];

$img        = $popup['image'] ? wp_get_attachment_image_src( $popup['image'], 'large' ) : null;
$img_mobile = $popup['image_mobile'] ? wp_get_attachment_image_src( $popup['image_mobile'], 'large' ) : null;
$title_id   = 'gb-popup-title-' . $popup['key'];

$classes = array( 'gb-popup' );
if ( ! $img ) {
	$classes[] = 'gb-popup--no-image';
} elseif ( $img_mobile ) {
	$classes[] = 'gb-popup--mobile-image';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" id="gb-popup" role="dialog" aria-modal="true"
	<?php if ( $popup['title'] ) : ?>
		aria-labelledby="<?php echo esc_attr( $title_id ); ?>"
	<?php else : ?>
		aria-label="<?php esc_attr_e( 'Notice', 'gb-popup' ); ?>"
	<?php endif; ?>
	hidden
	data-key="<?php echo esc_attr( $popup['key'] ); ?>"
	data-start="<?php echo (int) $popup['start']; ?>"
	data-end="<?php echo (int) $popup['end']; ?>"
	data-delay="<?php echo (int) $popup['delay']; ?>"
	data-days="<?php echo (int) $popup['days']; ?>"
	data-preview="<?php echo $popup['preview'] ? '1' : '0'; ?>">

	<div class="gb-popup__overlay" data-gb-popup-close></div>

	<div class="gb-popup__box" tabindex="-1">
		<button type="button" class="gb-popup__close" data-gb-popup-close aria-label="<?php esc_attr_e( 'Close', 'gb-popup' ); ?>">
			<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M5 5l14 14M19 5L5 19" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/></svg>
		</button>

		<?php if ( $img ) : ?>
			<div class="gb-popup__image">
				<picture>
					<?php if ( $img_mobile ) : ?>
						<source media="(max-width: 767px)" sizes="100vw"
							data-srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $popup['image_mobile'], 'large' ) ?: $img_mobile[0] ); ?>"
							width="<?php echo (int) $img_mobile[1]; ?>" height="<?php echo (int) $img_mobile[2]; ?>">
					<?php endif; ?>
					<img data-src="<?php echo esc_url( $img[0] ); ?>"
						data-srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $popup['image'], 'large' ) ); ?>"
						sizes="(max-width: 767px) 100vw, 480px"
						width="<?php echo (int) $img[1]; ?>" height="<?php echo (int) $img[2]; ?>"
						alt="<?php echo esc_attr( trim( (string) get_post_meta( $popup['image'], '_wp_attachment_image_alt', true ) ) ); ?>"
						decoding="async">
				</picture>
			</div>
		<?php endif; ?>

		<div class="gb-popup__content">
			<?php if ( $popup['title'] ) : ?>
				<h2 class="gb-popup__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $popup['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( '' !== trim( wp_strip_all_tags( $popup['text'] ) ) ) : ?>
				<div class="gb-popup__text"><?php echo wp_kses_post( $popup['text'] ); ?></div>
			<?php endif; ?>

			<?php
			if ( 'button' === $popup['action'] ) :
				$target = ! empty( $popup['button']['target'] ) ? $popup['button']['target'] : '_self';
				?>
				<a class="<?php echo esc_attr( gb_popup_button_class() ); ?>" href="<?php echo esc_url( $popup['button']['url'] ); ?>" target="<?php echo esc_attr( $target ); ?>"<?php echo '_blank' === $target ? ' rel="noopener"' : ''; ?> data-gb-popup-button>
					<?php echo esc_html( $popup['button']['title'] ?: __( 'Find out more', 'gb-popup' ) ); ?>
				</a>
			<?php elseif ( 'form' === $popup['action'] ) : ?>
				<div class="gb-popup__form">
					<?php echo GB_Popup_Form::render( $popup['form'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
