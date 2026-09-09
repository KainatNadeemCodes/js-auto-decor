<?php
/**
 * Counter module view.
 *
 * @author  Webnus
 * @package Kata Plus
 * @since   1.0.0
 */

use Elementor\Utils;
use Elementor\Plugin;
use Elementor\Group_Control_Image_Size;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = $this->get_settings_for_display();

if ( class_exists( 'Kata_Plus_Pro_Elementor' ) && ! empty( $settings['parallax'] ) ) {
	Kata_Plus_Pro_Elementor::start_parallax( $settings['parallax'], $settings['parallax_speed'], $settings['parallax_mouse_speed'] );
}

$url = Kata_Plus_Helpers::get_link_attr( $settings['external_url'] );

// Decide which source to use: repeater (new) or legacy media gallery.
$use_repeater = ( isset( $settings['gallery_source'] ) && 'repeater' === $settings['gallery_source'] && ! empty( $settings['gallery_items'] ) );

// Legacy gallery via shortcode.
if ( ! $use_repeater ) {
	if ( ! $settings['wp_gallery'] ) {
		return;
	}

	$ids = wp_list_pluck( $settings['wp_gallery'], 'id' );

	$this->add_render_attribute( 'shortcode', 'ids', implode( ',', $ids ) );

	// Handle custom dimensions
	if ( $settings['thumbnail_size'] === 'custom' && ! empty( $settings['thumbnail_custom_dimension'] ) ) {
		// For custom dimensions, we'll use a filter to crop the images
		add_filter( 'wp_get_attachment_image', array( $this, 'custom_gallery_image_html' ), 10, 5 );
		$this->add_render_attribute( 'shortcode', 'size', 'full' ); // Use full size as base, then crop with filter
	} else {
		$this->add_render_attribute( 'shortcode', 'size', $settings['thumbnail_size'] );
	}

	if ( $settings['gallery_columns'] ) {
		$this->add_render_attribute( 'shortcode', 'columns', $settings['gallery_columns'] );
	}

	if ( $settings['gallery_link'] ) {
		$this->add_render_attribute( 'shortcode', 'link', $settings['gallery_link'] );
	}

	if ( ! empty( $settings['gallery_rand'] ) ) {
		$this->add_render_attribute( 'shortcode', 'orderby', $settings['gallery_rand'] );
	}
	?>
	<?php if ( $url->src && $settings['link_to_whole_wrapper'] == 'yes' ) { ?>
		<a <?php echo $url->src . ' ' . $url->rel . ' ' . $url->target; ?>>
	<?php } ?>
		<div class="kata-plus-image-gallery">
			<?php
			add_filter( 'wp_get_attachment_link', array( $this, 'add_lightbox_data_to_image_link' ), 10, 2 );

			echo do_shortcode( '[gallery ' . $this->get_render_attribute_string( 'shortcode' ) . ']' );

			remove_filter( 'wp_get_attachment_link', array( $this, 'add_lightbox_data_to_image_link' ) );
			remove_filter( 'wp_get_attachment_image', array( $this, 'custom_gallery_image_html' ) );
			?>
		</div>
	<?php if ( $url->src && $settings['link_to_whole_wrapper'] == 'yes' ) { ?>
		</a>
	<?php } ?>
	<?php
} else {
	// New repeater-based gallery with overlay, icon and link.
	$items   = $settings['gallery_items'];
	$columns = ! empty( $settings['gallery_columns'] ) ? (int) $settings['gallery_columns'] : 4;
	$columns = max( 1, min( 10, $columns ) );
	?>
	<?php if ( $url->src && $settings['link_to_whole_wrapper'] == 'yes' ) { ?>
		<a <?php echo $url->src . ' ' . $url->rel . ' ' . $url->target; ?>>
	<?php } ?>
		<div class="kata-plus-image-gallery">
			<div class="gallery kata-gallery-repeater gallery-columns-<?php echo esc_attr( $columns ); ?>">
			<?php foreach ( $items as $item ) : ?>
				<?php
				if ( empty( $item['item_image']['id'] ) && empty( $item['item_image']['url'] ) ) {
					continue;
				}

				$image_id  = isset( $item['item_image']['id'] ) ? $item['item_image']['id'] : 0;
				$image_url = '';

				if ( $image_id ) {
					$image_url = Group_Control_Image_Size::get_attachment_image_src( $image_id, 'thumbnail', $settings );
					if ( ! $image_url ) {
						$src       = wp_get_attachment_image_src( $image_id, 'full' );
						$image_url = $src ? $src[0] : '';
					}
				} elseif ( ! empty( $item['item_image']['url'] ) ) {
					$image_url = $item['item_image']['url'];
				}

				if ( ! $image_url ) {
					continue;
				}

				$full_src = $image_id ? wp_get_attachment_image_src( $image_id, 'full' ) : false;
				$full_url = $full_src ? $full_src[0] : $image_url;

				$caption        = ! empty( $item['item_caption'] ) ? $item['item_caption'] : '';
				$overlay_text   = ! empty( $item['item_overlay_text'] ) ? $item['item_overlay_text'] : '';
				$item_link_raw  = isset( $item['item_link'] ) ? $item['item_link'] : array();
				$item_link      = Kata_Plus_Helpers::get_link_attr( $item_link_raw );
				$has_link       = ! empty( $item_link->src );
				$enable_lightbox = isset( $item['item_enable_lightbox'] ) && 'yes' === $item['item_enable_lightbox'] && 'file' === $settings['gallery_link'];

				// Lightbox attributes (Elementor-compatible).
				$lightbox_attrs = '';
				if ( $enable_lightbox && 'no' !== $settings['open_lightbox'] ) {
					$lightbox_attrs  = 'data-elementor-open-lightbox="yes"';
					$lightbox_attrs .= ' data-elementor-lightbox-slideshow="' . esc_attr( $this->get_id() ) . '"';
					if ( $caption ) {
						$lightbox_attrs .= ' data-elementor-lightbox-title="' . esc_attr( wp_strip_all_tags( $caption ) ) . '"';
					}
				}
				?>
				<div class="gallery-item">
					<figure class="gallery-icon">
						<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $caption ); ?>" />
						<?php if ( $enable_lightbox || $has_link || $overlay_text ) : ?>
							<div class="kata-gallery-overlay">
								<?php if ( $overlay_text ) : ?>
									<span class="kata-gallery-overlay-text"><?php echo esc_html( $overlay_text ); ?></span>
								<?php endif; ?>

								<?php if ( $enable_lightbox ) : ?>
									<a href="<?php echo esc_url( $full_url ); ?>" class="kata-gallery-overlay-icon kata-gallery-lightbox" <?php echo $lightbox_attrs; ?>>
										<?php
										if ( ! empty( $item['item_lightbox_icon'] ) ) {
											echo Kata_Plus_Helpers::get_icon( '', $item['item_lightbox_icon'] );
										}
										?>
									</a>
								<?php endif; ?>

								<?php if ( $has_link ) : ?>
									<a class="kata-gallery-overlay-icon kata-gallery-link" <?php echo $item_link->src . ' ' . $item_link->rel . ' ' . $item_link->target; ?>>
										<?php
										if ( ! empty( $item['item_link_icon'] ) ) {
											echo Kata_Plus_Helpers::get_icon( '', $item['item_link_icon'] );
										}
										?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</figure>
					<?php if ( $caption ) : ?>
						<figcaption class="gallery-caption"><?php echo wp_kses_post( $caption ); ?></figcaption>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			</div>
		</div>
	<?php if ( $url->src && $settings['link_to_whole_wrapper'] == 'yes' ) { ?>
		</a>
	<?php } ?>
	<?php
}
?>
<?php

if ( class_exists( 'Kata_Plus_Pro_Elementor' ) && ! empty( $settings['parallax'] ) ) {
	Kata_Plus_Pro_Elementor::end_parallax( $settings['parallax'] );
}
if ( isset( $settings['custom_css'] ) ) {
	Kata_Plus_Elementor::module_custom_css_editor( $settings['custom_css'] );
}
