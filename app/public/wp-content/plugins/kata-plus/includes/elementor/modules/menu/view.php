<?php

/**
 * Menu module view.
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

$settings   = $this->get_settings();
$element_id = $this->get_id();
$triangle   = ( isset( $settings['triangle'] ) && $settings['triangle'] == 'yes' ) ? 'triangle ' : '';
$separator  = ( isset( $settings['separator'] ) && $settings['separator'] == 'yes' ) ? 'separator ' : '';

$breakpoints_manager = Plugin::$instance->breakpoints;
$active_devices      = array( 'desktop', 'tablet', 'mobile' );
if ( $breakpoints_manager ) {
	$active_devices = $breakpoints_manager->get_active_devices_list(
		array(
			'add_desktop'   => true,
			'desktop_first' => true,
		)
	);
}
$active_devices = array_values( array_unique( $active_devices ) );
if ( ! in_array( 'desktop', $active_devices, true ) ) {
	array_unshift( $active_devices, 'desktop' );
}

$responsive_devices = array();
$device_media_queries = array();
$element_selector = '.elementor-element.elementor-element-' . sanitize_html_class( $element_id );

if ( $breakpoints_manager ) {
	$active_breakpoints = $breakpoints_manager->get_active_breakpoints();
	
	$max_breakpoint_value = 0;
	foreach ( $active_devices as $device_key ) {
		if ( 'desktop' === $device_key ) {
			continue;
		}
		$breakpoint = isset( $active_breakpoints[ $device_key ] ) ? $active_breakpoints[ $device_key ] : null;
		if ( $breakpoint && 'max' === $breakpoint->get_direction() ) {
			$value = intval( $breakpoint->get_value() );
			if ( $value > $max_breakpoint_value ) {
				$max_breakpoint_value = $value;
			}
		}
	}
	
	$desktop_min = $max_breakpoint_value > 0 ? ( $max_breakpoint_value + 1 ) : intval( $breakpoints_manager->get_desktop_min_point() );
	
	foreach ( $active_devices as $device_key ) {
		$setting_key = 'desktop' === $device_key ? 'respnsive_menu' : 'respnsive_menu_' . $device_key;
		$is_enabled = isset( $settings[ $setting_key ] ) && 'yes' === $settings[ $setting_key ];
		
		if ( $is_enabled ) {
			$responsive_devices[ $device_key ] = true;
			
			if ( 'desktop' === $device_key ) {
				$device_media_queries[ $device_key ] = '(min-width: ' . $desktop_min . 'px)';
			} else {
				$breakpoint = isset( $active_breakpoints[ $device_key ] ) ? $active_breakpoints[ $device_key ] : null;
				if ( $breakpoint ) {
					$value = intval( $breakpoint->get_value() );
					$direction = $breakpoint->get_direction();
					
					if ( 'min' === $direction ) {
						$device_media_queries[ $device_key ] = '(min-width: ' . $value . 'px)';
					} else {
						$min_breakpoint = $breakpoints_manager->get_device_min_breakpoint( $device_key );
						if ( $min_breakpoint > 0 && $min_breakpoint < $value ) {
							$device_media_queries[ $device_key ] = '(max-width: ' . $value . 'px) and (min-width: ' . $min_breakpoint . 'px)';
						} else {
							$device_media_queries[ $device_key ] = '(max-width: ' . $value . 'px)';
						}
					}
				}
			}
		} else {
			$responsive_devices[ $device_key ] = false;
		}
	}
} else {
	$default_devices = array( 'desktop', 'tablet', 'mobile' );
	foreach ( $default_devices as $device_key ) {
		$setting_key = 'desktop' === $device_key ? 'respnsive_menu' : 'respnsive_menu_' . $device_key;
		$is_enabled = isset( $settings[ $setting_key ] ) && 'yes' === $settings[ $setting_key ];
		
		if ( $is_enabled ) {
			$responsive_devices[ $device_key ] = true;
			if ( 'desktop' === $device_key ) {
				$device_media_queries[ $device_key ] = '(min-width: 1025px)';
			} elseif ( 'tablet' === $device_key ) {
				$device_media_queries[ $device_key ] = '(max-width: 1024px) and (min-width: 768px)';
			} elseif ( 'mobile' === $device_key ) {
				$device_media_queries[ $device_key ] = '(max-width: 767px)';
			}
		} else {
			$responsive_devices[ $device_key ] = false;
		}
	}
}

$has_responsive = in_array( true, $responsive_devices, true );
$responsive_classes = array();
foreach ( $responsive_devices as $device => $is_enabled ) {
	if ( $is_enabled ) {
		$responsive_classes[] = 'kata-responsive-' . $device . '-on';
	}
}
$responsive = $has_responsive ? ' kata-have-responsive ' : '';
$menu_type = ( isset( $settings['menu_type'] ) && $settings['menu_type'] == 'horizontal' ) ? 'kata-menu-navigation kata-nav-menu' . $responsive . ' ' : 'kata-menu-vertical kata-nav-menu ';
$hover_effect = isset( $settings['hover_effect'] ) ? $settings['hover_effect'] : '';
$wrapper_classes = array_merge( array( 'kata-menu-wrap' ), $responsive_classes );

$wrapper_data_attributes = sprintf(
	' data-responsive-config="%1$s" data-responsive-breakpoints="%2$s"',
	esc_attr( wp_json_encode( $responsive_devices ) ),
	esc_attr( wp_json_encode( $device_media_queries ) )
);

$responsive_inline_style = '';
if ( $has_responsive && ! empty( $device_media_queries ) ) {
	$style_rules = array();
	foreach ( $responsive_devices as $device => $enabled ) {
		if ( $enabled && isset( $device_media_queries[ $device ] ) ) {
			$style_rules[] = sprintf(
				'@media %1$s { %2$s .kata-nav-menu.kata-have-responsive{display:none !important;} %2$s .cm-ham-open-icon{display:block !important;} %2$s .kata-responsive-menu-wrap{display:block !important;} }',
				$device_media_queries[ $device ],
				$element_selector
			);
		}
	}
	if ( ! empty( $style_rules ) ) {
		$responsive_inline_style = implode( ' ', $style_rules );
	}
}

if ( defined( 'WP_DEBUG' ) && WP_DEBUG && isset( $_GET['kata_debug_menu'] ) ) {
	error_log( '[Kata Menu Debug] Responsive Devices: ' . print_r( $responsive_devices, true ) );
	error_log( '[Kata Menu Debug] Media Queries: ' . print_r( $device_media_queries, true ) );
	error_log( '[Kata Menu Debug] Generated CSS: ' . $responsive_inline_style );
}

if ( $settings['nav_id'] && wp_get_nav_menu_object( $settings['nav_id'] ) != false ) {
	$settings['nav_id'] = wp_get_nav_menu_object( $settings['nav_id'] )->term_id;
}

if ( class_exists( 'Kata_Plus_Pro_Elementor' ) && ! empty( $settings['parallax'] ) ) {
	Kata_Plus_Pro_Elementor::start_parallax( $settings['parallax'], $settings['parallax_speed'], $settings['parallax_mouse_speed'] );
}
?>
<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"<?php echo $wrapper_data_attributes; ?>>
	<?php
	if ( ! empty( $settings['nav_id'] ) && is_nav_menu( $settings['nav_id'] ) ) {
		if ( wp_get_nav_menu_items( $settings['nav_id'] ) ) {
			echo wp_nav_menu(
				array(
					'menu'        => $settings['nav_id'],
					'container'   => false,
					'menu_id'     => 'kata-menu-navigation-desktop-' . uniqid(),
					'menu_class'  => $menu_type . $separator . $triangle . $hover_effect . ' desktop',
					'depth'       => '5',
					'fallback_cb' => 'wp_page_menu',
					'items_wrap'  => '<ul id="%1$s" class="%2$s">%3$s</ul>',
					'echo'        => false,
					'walker'      => new Kata_Plus_Walker_Nav_Menu(),
				)
			);
			if ( $has_responsive ) {
				echo Kata_Plus_Helpers::get_icon( '', 'themify/align-justify', 'cm-ham-open-icon', '' );
				echo wp_nav_menu(
					array(
						'menu'        => $settings['nav_id'],
						'container'   => false,
						'menu_id'     => 'kata-responsive-menu-' . uniqid(),
						'menu_class'  => 'kata-menu-vertical kata-nav-menu kata-responsive-menu' . $separator . ' ' . $triangle,
						'depth'       => '5',
						'fallback_cb' => 'wp_page_menu',
						'items_wrap'  => '<div class="kata-responsive-menu-wrap">' . Kata_Plus_Helpers::get_icon( '', 'themify/close', 'cm-ham-close-icon', '' ) . '<ul id="%1$s" class="%2$s">%3$s</ul></div>',
						'echo'        => false,
						'walker'      => new Kata_Plus_Walker_Nav_Menu(),
					)
				);
			}
		} else {
			echo '<p>';
			echo __( 'This navigation menu does not have menu items', 'kata-plus' );
			echo '</p>';
		}
	} else {
		echo '<p>';
		echo __( 'Please select a nav menu', 'kata-plus' );
		echo '</p>';
	}
	?>
</div>
<?php
if ( $responsive_inline_style ) {
	printf(
		'<style id="kata-menu-responsive-%1$s">%2$s</style>',
		esc_attr( $element_id ),
		$responsive_inline_style
	);
}
if ( class_exists( 'Kata_Plus_Pro_Elementor' ) && ! empty( $settings['parallax'] ) ) {
	Kata_Plus_Pro_Elementor::end_parallax( $settings['parallax'] );
}
if ( isset( $settings['custom_css'] ) ) {
	Kata_Plus_Elementor::module_custom_css_editor( $settings['custom_css'] );
}
