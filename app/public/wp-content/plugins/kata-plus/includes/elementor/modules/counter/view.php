<?php
/**
 * Counter module view.
 *
 * @author  Webnus
 * @package Kata Plus
 * @since   1.0.0
 */

use Elementor\Plugin;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = $this->get_settings_for_display();

if ( class_exists( 'Kata_Plus_Pro_Elementor' ) && ! empty( $settings['parallax'] ) ) {
	Kata_Plus_Pro_Elementor::start_parallax( $settings['parallax'], $settings['parallax_speed'], $settings['parallax_mouse_speed'] );
}

$space           = ' ';
$align           = $space . $settings['layout'] . $space . $settings['aligne'];
$ending_number   = isset( $settings['ending_number'] ) ? $settings['ending_number'] : 0;
$starting_number = isset( $settings['starting_number'] ) ? $settings['starting_number'] : '';
$animation_from  = Kata_Plus_Counter::get_animation_from_number( $ending_number, $starting_number );

// Use Elementor counter markup/styles; show ending number first, animate from $animation_from.
$counter = Plugin::instance()->elements_manager->create_element_instance(
	array(
		'elType'     => 'widget',
		'widgetType' => 'counter',
		'id'         => 'kata-counter',
		'settings'   => array(
			'starting_number'         => $ending_number,
			'ending_number'           => $ending_number,
			'prefix'                  => isset( $settings['prefix'] ) ? $settings['prefix'] : '',
			'suffix'                  => isset( $settings['suffix'] ) ? $settings['suffix'] : '',
			'duration'                => isset( $settings['duration'] ) ? $settings['duration'] : '',
			'thousand_separator'      => isset( $settings['thousand_separator'] ) ? $settings['thousand_separator'] : '',
			'thousand_separator_char' => isset( $settings['thousand_separator_char'] ) ? $settings['thousand_separator_char'] : '',
			'title'                   => '',
		),
	)
);
?>

<div class="kata-plus-counter<?php echo esc_attr( $align ); ?>">
	<?php if ( $settings['symbol'] ) { ?>
		<div class="kata-plus-counter-icon kata-lazyload">
		<?php
		if ( ! empty( $settings['icon'] ) && $settings['symbol'] == 'icon' ) {
			echo Kata_Plus_Helpers::get_icon( '', $settings['icon'], '', '' );
		} elseif ( isset( $settings['image']['url'] ) && Kata_Plus_Helpers::string_is_contain( $settings['image']['url'], 'svg' ) && $settings['symbol'] == 'imagei' ) {
			echo Kata_Plus_Helpers::get_image_srcset( $settings['image']['id'], 'full' );
		} elseif ( ! empty( $settings['image']['id'] ) && ! Kata_Plus_Helpers::string_is_contain( $settings['image']['url'], 'svg' ) && $settings['symbol'] == 'imagei' ) {
			echo Kata_Plus_Helpers::get_attachment_image_html( $settings, 'image' );
		} elseif ( ! empty( $settings['image']['id'] ) && Kata_Plus_Helpers::string_is_contain( $settings['image']['url'], 'svg' ) && $settings['symbol'] == 'svg' ) {
			$svg_size = '';
			if ( isset( $settings['image_custom_dimension']['width'] ) || isset( $settings['image_custom_dimension']['height'] ) ) {
				$svg_size = Kata_Plus_Helpers::svg_resize( $settings['image_size'], $settings['image_custom_dimension']['width'], $settings['image_custom_dimension']['height'] );
			} else {
				$svg_size = Kata_Plus_Helpers::svg_resize( $settings['image_size'] );
			}
			Kata_Plus_Helpers::get_attachment_svg_path( $settings['image']['id'], $settings['image']['url'], $svg_size );
		}
		?>
		</div>
	<?php } ?>
	<div class="content-wrap">
	<?php
	ob_start();
	$counter->print_element();
	$counter_html = ob_get_clean();

	// Elementor binds data-from-value to starting_number; we show ending first, then animate from $animation_from.
	if ( (string) $animation_from !== (string) $ending_number ) {
		$counter_html = str_replace(
			'data-from-value="' . esc_attr( $ending_number ) . '"',
			'data-from-value="' . esc_attr( $animation_from ) . '"',
			$counter_html
		);
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor widget HTML.
	echo $counter_html;

	if ( $settings['description'] ) {
		?>
			<div class="kata-plus-counter-text-wrap">
				<p><?php echo wp_kses( $settings['description'], wp_kses_allowed_html( 'post' ) ); ?></p>
			</div>
		<?php
	}
	?>
	</div>
</div>
<?php

if ( class_exists( 'Kata_Plus_Pro_Elementor' ) && ! empty( $settings['parallax'] ) ) {
	Kata_Plus_Pro_Elementor::end_parallax( $settings['parallax'] );
}
if ( isset( $settings['custom_css'] ) ) {
	Kata_Plus_Elementor::module_custom_css_editor( $settings['custom_css'] );
}
