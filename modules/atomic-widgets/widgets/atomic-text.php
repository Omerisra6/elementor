<?php
namespace Elementor\Modules\AtomicWidgets\Widgets;

use Elementor\Modules\AtomicWidgets\Controls\Section;
use Elementor\Modules\AtomicWidgets\Controls\Types\Select_Control;
use Elementor\Modules\AtomicWidgets\Controls\Types\Textarea_Control;
use Elementor\Modules\AtomicWidgets\Base\Atomic_Widget_Base;
use Elementor\Modules\AtomicWidgets\PropTypes\Classes_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\String_Prop_Type;
use Elementor\Modules\AtomicWidgets\Schema\Atomic_Prop;
use Elementor\Modules\AtomicWidgets\Schema\Constraints\Enum;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Atomic_Text extends Atomic_Widget_Base {
	public function get_icon() {
		return 'eicon-text';
	}

	public function get_title() {
		return esc_html__( 'Atomic Text', 'elementor' );
	}

	public function get_name() {
		return 'atomic-text';
	}

	protected function render() {
		$settings = $this->get_atomic_settings();

		$escaped_text = esc_html( $settings['text'] );

		$class = '';
		if ( ! empty( $settings['classes'] ) ) {
			$class = "class='" . esc_attr( $settings['classes'] ) . "'";
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "<p $class>$escaped_text</p>";
	}

	protected function define_atomic_controls(): array {

		$text_control = Textarea_Control::bind_to( 'text' )
			->set_label( __( 'Title', 'elementor' ) )
			->set_placeholder( __( 'Type your text here', 'elementor' ) );

		$tag_and_title_section = Section::make()
			->set_label( __( 'Content', 'elementor' ) )
			->set_items( [
				$text_control,
			]);

		return [
			$tag_and_title_section,
		];
	}

	protected static function define_props_schema(): array {
		return [
			'classes' => Classes_Prop_Type::make()
				->default( [] ),

			'text' => String_Prop_Type::make()
				->default( __( 'Your Text Here', 'elementor' ) ),
		];
	}
}
