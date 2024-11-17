<?php
namespace Elementor\Modules\AtomicWidgets\Widgets;

use Elementor\Modules\AtomicWidgets\Controls\Section;
use Elementor\Modules\AtomicWidgets\Controls\Types\Text_Control;
use Elementor\Modules\AtomicWidgets\Controls\Types\Textarea_Control;
use Elementor\Modules\AtomicWidgets\Base\Atomic_Widget_Base;
use Elementor\Modules\AtomicWidgets\Controls\Types\Switch_Control;
use Elementor\Modules\AtomicWidgets\Controls\Types\Url_Control;
use Elementor\Modules\AtomicWidgets\PropTypes\Classes_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\Boolean_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\String_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Url_Prop_Type;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Atomic_Video extends Atomic_Widget_Base {
	public function get_name() {
		return 'a-video';
	}

	public function get_title() {
		return esc_html__( 'Atomic Video', 'elementor' );
	}

	public function get_icon() {
		return 'eicon-video';
	}

	protected function render() {
		$settings = $this->get_atomic_settings();

		$src = $settings['src'];
		$show_controls = $settings['show_controls'];

		$attrs = array_filter( [
			'class' => $settings['classes'] ?? '',
		] );

		$source = sprintf(
			'<source src="%1$s" type="video/mp4">',
			esc_url( $src )
		);

		echo sprintf(
			'<video %1$s %2$s>%3$s</video>',
			Utils::render_html_attributes( $attrs ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$show_controls ? 'controls' : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$source, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	protected function define_atomic_controls(): array {
		return [
			Section::make()
				->set_label( __( 'Content', 'elementor' ) )
				->set_items( [
					Url_Control::bind_to( 'src' )
						->set_label( __( 'SRC', 'elementor' ) )
						->set_placeholder( __( 'Type your video source here', 'elementor' ) ),
					Switch_Control::bind_to( 'show_controls' )
						->set_label( __( 'Video Controls', 'elementor' ) ),
					Switch_Control::bind_to( 'autoplay' )
						->set_label( __( 'Autoplay', 'elementor' ) ),
				]),
		];
	}

	protected static function define_props_schema(): array {
		return [
			'classes' => Classes_Prop_Type::make()
				->default( [] ),

			'src' => Url_Prop_Type::make()
				->default( 'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4' ),

			'show_controls' => Boolean_Prop_Type::make()
				->default( true ),

			'autoplay' => Boolean_Prop_Type::make()
				->default( false ),
		];
	}
}
