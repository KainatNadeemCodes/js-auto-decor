<?php
/**
 * Gallery module config.
 *
 * @author  Webnus
 * @package Kata Plus
 * @since   1.0.0
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

class Kata_Plus_Gallery extends Widget_Base {
	public function get_name() {
		return 'kata-plus-gallery';
	}

	public function get_title() {
		return __( 'Gallery', 'kata-plus' );
	}

	public function get_icon() {
		return 'kata-widget kata-eicon-gallery';
	}

	public function get_categories() {
		return array( 'kata_plus_elementor_most_usefull' );
	}

	public function get_style_depends() {
		return array( 'kata-plus-gallery' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_gallery',
			array(
				'label' => __( 'Image Gallery', 'kata-plus' ),
			)
		);

		$this->add_control(
			'gallery_source',
			array(
				'label'   => __( 'Source', 'kata-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'media',
				'options' => array(
					'media'    => __( 'Media Gallery', 'kata-plus' ),
					'repeater' => __( 'Custom Items (Repeater)', 'kata-plus' ),
				),
			)
		);

		$this->add_control(
			'wp_gallery',
			array(
				'label'      => __( 'Add Images', 'kata-plus' ),
				'type'       => Controls_Manager::GALLERY,
				'show_label' => false,
				'dynamic'    => array(
					'active' => true,
				),
				'condition'  => array(
					'gallery_source' => 'media',
				),
			)
		);

		// Repeater-based items (new mode)
		$repeater = new Repeater();

		$repeater->add_control(
			'item_image',
			array(
				'label'   => __( 'Image', 'kata-plus' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => Utils::get_placeholder_image_src(),
				),
			)
		);

		$repeater->add_control(
			'item_caption',
			array(
				'label'   => __( 'Caption', 'kata-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$repeater->add_control(
			'item_overlay_text',
			array(
				'label'   => __( 'Overlay Text', 'kata-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$repeater->add_control(
			'item_link',
			array(
				'label'         => __( 'Link', 'kata-plus' ),
				'type'          => Controls_Manager::URL,
				'placeholder'   => __( 'https://your-link.com', 'kata-plus' ),
				'show_external' => true,
				'default'       => array(
					'url'         => '',
					'is_external' => false,
					'nofollow'    => false,
				),
			)
		);

		$repeater->add_control(
			'item_enable_lightbox',
			array(
				'label'        => __( 'Enable Lightbox', 'kata-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'kata-plus' ),
				'label_off'    => __( 'Off', 'kata-plus' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$repeater->add_control(
			'item_lightbox_icon',
			array(
				'label'     => __( 'Lightbox Icon', 'kata-plus' ),
				'type'      => 'kata_plus_icons',
				'default'   => 'themify/zoom-in',
				'condition' => array(
					'item_enable_lightbox' => 'yes',
				),
			)
		);

		$repeater->add_control(
			'item_link_icon',
			array(
				'label'   => __( 'Link Icon', 'kata-plus' ),
				'type'    => 'kata_plus_icons',
				'default' => 'themify/link',
			)
		);

		$this->add_control(
			'gallery_items',
			array(
				'label'         => __( 'Items', 'kata-plus' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $repeater->get_controls(),
				'prevent_empty' => false,
				'title_field'   => '{{{ item_caption || item_overlay_text || item_image.url }}}',
				'condition'     => array(
					'gallery_source' => 'repeater',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'thumbnail', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `thumbnail_size` and `thumbnail_custom_dimension`.
				// 'exclude'   => array( 'custom' ),
				'separator' => 'none',
			)
		);
		$gallery_columns = range( 1, 10 );
		$gallery_columns = array_combine( $gallery_columns, $gallery_columns );
		$this->add_control(
			'gallery_columns',
			array(
				'label'   => __( 'Columns', 'kata-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 4,
				'options' => $gallery_columns,
			)
		);
		$this->add_control(
			'gallery_link',
			array(
				'label'   => __( 'Link', 'kata-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'file',
				'options' => array(
					'file'       => __( 'Media File', 'kata-plus' ),
					'attachment' => __( 'Attachment Page', 'kata-plus' ),
					'none'       => __( 'None', 'kata-plus' ),
				),
			)
		);
		$this->add_control(
			'link_to_whole_wrapper',
			array(
				'label'        => __( 'Make the Whole Wrapper Clickable', 'kata-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'kata-plus' ),
				'label_off'    => __( 'Off', 'kata-plus' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'gallery_link' => 'none',
				),
			)
		);
		$this->add_control(
			'external_url',
			array(
				'label'         => __( 'Link', 'kata-plus' ),
				'type'          => Controls_Manager::URL,
				'placeholder'   => __( 'https://your-link.com', 'kata-plus' ),
				'show_external' => true,
				'default'       => array(
					'url'         => '',
					'is_external' => false,
					'nofollow'    => false,
				),
				'condition'     => array(
					'link_to_whole_wrapper' => 'yes',
				),
			)
		);
		$this->add_control(
			'open_lightbox',
			array(
				'label'     => __( 'Lightbox', 'kata-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'default',
				'options'   => array(
					'default' => __( 'Default', 'kata-plus' ),
					'yes'     => __( 'Yes', 'kata-plus' ),
					'no'      => __( 'No', 'kata-plus' ),
				),
				'condition' => array(
					'gallery_link' => 'file',
				),
			)
		);
		$this->add_control(
			'gallery_rand',
			array(
				'label'   => __( 'Order By', 'kata-plus' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					''     => __( 'Default', 'kata-plus' ),
					'rand' => __( 'Random', 'kata-plus' ),
				),
				'default' => '',
			)
		);
		$this->add_control(
			'view',
			array(
				'label'   => __( 'View', 'kata-plus' ),
				'type'    => Controls_Manager::HIDDEN,
				'default' => 'traditional',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_caption',
			array(
				'label' => __( 'Caption', 'kata-plus' ),
			)
		);
		$this->add_control(
			'gallery_display_caption',
			array(
				'label'     => __( 'Display', 'kata-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''     => __( 'Show', 'kata-plus' ),
					'none' => __( 'Hide', 'kata-plus' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .gallery-item .gallery-caption' => 'display: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'kata-plus' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array(
						'title' => __( 'Left', 'kata-plus' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => __( 'Center', 'kata-plus' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'   => array(
						'title' => __( 'Right', 'kata-plus' ),
						'icon'  => 'eicon-text-align-right',
					),
					'justify' => array(
						'title' => __( 'Justified', 'kata-plus' ),
						'icon'  => 'eicon-text-align-justify',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .gallery-item .gallery-caption' => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'gallery_display_caption' => '',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_gallery_images',
			array(
				'label' => __( 'Gallery', 'kata-plus' ),
			)
		);
		$this->add_control(
			'image_spacing',
			array(
				'label'        => __( 'Spacing', 'kata-plus' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => array(
					''       => __( 'Default', 'kata-plus' ),
					'custom' => __( 'Custom', 'kata-plus' ),
				),
				'prefix_class' => 'gallery-spacing-',
				'default'      => '',
			)
		);
		$columns_margin  = is_rtl() ? '0 0 -{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}};' : '0 -{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}} 0;';
		$columns_padding = is_rtl() ? '0 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}};' : '0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0;';
		$this->add_control(
			'image_spacing_custom',
			array(
				'label'      => __( 'Image Spacing', 'kata-plus' ),
				'type'       => Controls_Manager::SLIDER,
				'show_label' => false,
				'range'      => array(
					'px' => array(
						'max' => 100,
					),
				),
				'default'    => array(
					'size' => 15,
				),
				'selectors'  => array(
					'{{WRAPPER}} .gallery-item' => 'padding:' . $columns_padding,
					'{{WRAPPER}} .gallery'      => 'margin: ' . $columns_margin,
				),
				'condition'  => array(
					'image_spacing' => 'custom',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'imge_gallery_wrapper_style',
			array(
				'label' => __( 'Gallery', 'kata-plus' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'styler_image_gallery_wrapper',
			array(
				'label'    => esc_html__( 'Wrapper', 'kata-plus' ),
				'type'     => 'styler',
				'selector' => '.kata-plus-image-gallery',
				'isSVG'    => true,
				'isInput'  => false,
				'wrapper'  => '{{WRAPPER}}',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'imge_gallery_item_style',
			array(
				'label' => __( 'Items', 'kata-plus' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'styler_image_gallery_item_wrapper',
			array(
				'label'    => esc_html__( 'Item', 'kata-plus' ),
				'type'     => 'styler',
				'selector' => '.gallery-item',
				'isSVG'    => true,
				'isInput'  => false,
				'wrapper'  => '{{WRAPPER}}',
			)
		);
		$this->add_control(
			'styler_image_gallery_image_wrapper',
			array(
				'label'    => esc_html__( 'Image Wrapper', 'kata-plus' ),
				'type'     => 'styler',
				'selector' => '.gallery-icon',
				'isSVG'    => true,
				'isInput'  => false,
				'wrapper'  => '{{WRAPPER}}',
			)
		);
		$this->add_control(
			'styler_image_gallery_image',
			array(
				'label'    => esc_html__( 'Image', 'kata-plus' ),
				'type'     => 'styler',
				'selector' => '.gallery-icon img',
				'isSVG'    => true,
				'isInput'  => false,
				'wrapper'  => '{{WRAPPER}}',
			)
		);
		$this->add_control(
			'styler_image_caption_image',
			array(
				'label'    => esc_html__( 'Caption', 'kata-plus' ),
				'type'     => 'styler',
				'selector' => '.gallery-caption',
				'isSVG'    => true,
				'isInput'  => false,
				'wrapper'  => '{{WRAPPER}}',
				'condition' => array(
					'gallery_display_caption!' => 'none',
				),
			)
		);
		$this->add_control(
			'styler_image_gallery_overlay',
			array(
				'label'    => esc_html__( 'Overlay', 'kata-plus' ),
				'type'     => 'styler',
				'selector' => '.kata-gallery-overlay',
				'isSVG'    => true,
				'isInput'  => false,
				'wrapper'  => '{{WRAPPER}} .gallery-item',
			)
		);
		$this->add_control(
			'styler_image_gallery_overlay_icons',
			array(
				'label'    => esc_html__( 'Overlay Icons', 'kata-plus' ),
				'type'     => 'styler',
				'selector' => '{{WRAPPER}} .gallery-item .kata-gallery-overlay .kata-gallery-overlay-icon',
				'isSVG'    => true,
				'isInput'  => false,
			)
		);
		$this->end_controls_section();

		// Common controls
		do_action( 'kata_plus_common_controls', $this );
	}

	public function custom_gallery_image_html( $html, $attachment_id, $size, $icon, $attr ) {
		$settings = $this->get_settings_for_display();

		if ( $settings['thumbnail_size'] === 'custom' && ! empty( $settings['thumbnail_custom_dimension'] ) ) {
			$width = ! empty( $settings['thumbnail_custom_dimension']['width'] ) ? $settings['thumbnail_custom_dimension']['width'] : null;
			$height = ! empty( $settings['thumbnail_custom_dimension']['height'] ) ? $settings['thumbnail_custom_dimension']['height'] : null;

			if ( $width || $height ) {
				// Use CSS object-fit: cover to crop the image instead of stretching
				$style = 'object-fit: cover;';

				if ( $width ) {
					$style .= ' width: ' . $width . 'px;';
					$attr['width'] = $width;
				}

				if ( $height ) {
					$style .= ' height: ' . $height . 'px;';
					$attr['height'] = $height;
				}

				// Update the style attribute
				if ( isset( $attr['style'] ) ) {
					$attr['style'] .= ' ' . $style;
				} else {
					$attr['style'] = $style;
				}

				// Rebuild the HTML with updated attributes
				$attributes = '';
				foreach ( $attr as $name => $value ) {
					$attributes .= ' ' . $name . '="' . esc_attr( $value ) . '"';
				}

				// Get the image source (use full size for cropping)
				$full_src = wp_get_attachment_image_src( $attachment_id, 'full' );
				if ( $full_src ) {
					$src = $full_src[0];
					$alt = isset( $attr['alt'] ) ? $attr['alt'] : get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

					$html = '<img src="' . esc_url( $src ) . '"' . $attributes . ' alt="' . esc_attr( $alt ) . '">';
				}
			}
		}

		return $html;
	}

	protected function render() {
		require __DIR__ . '/view.php';
	}
}
