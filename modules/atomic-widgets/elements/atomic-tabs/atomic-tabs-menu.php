<?php
namespace Elementor\Modules\AtomicWidgets\Elements\Atomic_Tabs;

use Elementor\Modules\AtomicWidgets\Elements\Atomic_Element_Base;
use Elementor\Modules\AtomicWidgets\Elements\Has_Nested_Template;
use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\String_Prop_Type;
use Elementor\Modules\AtomicWidgets\Styles\Style_Definition;
use Elementor\Modules\AtomicWidgets\Styles\Style_Variant;
use Elementor\Modules\AtomicWidgets\Controls\Section;
use Elementor\Modules\AtomicWidgets\Controls\Types\Text_Control;
use Elementor\Modules\AtomicWidgets\PropTypes\Classes_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Attributes_Prop_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Atomic_Tabs_Menu extends Atomic_Element_Base {
	use Has_Nested_Template;

	const BASE_STYLE_KEY = 'base';

	public static function get_type() {
		return 'e-tabs-menu';
	}

	public static function get_element_type(): string {
		return 'e-tabs-menu';
	}

	public function get_title() {
		return esc_html__( 'Tabs menu', 'elementor' );
	}

	public function get_keywords() {
		return [ 'ato', 'atom', 'atoms', 'atomic' ];
	}

	public function get_icon() {
		return 'eicon-tab-menu';
	}

	public function should_show_in_panel() {
		return false;
	}

	protected static function define_props_schema(): array {
		return [
			'classes' => Classes_Prop_Type::make()
				->default( [] ),
			'attributes' => Attributes_Prop_Type::make(),
		];
	}

	protected function define_atomic_controls(): array {
		return [
			Section::make()
				->set_label( __( 'Settings', 'elementor' ) )
				->set_id( 'settings' )
				->set_items( [
					Text_Control::bind_to( '_cssid' )
						->set_label( __( 'ID', 'elementor' ) )
						->set_meta( [
							'layout' => 'two-columns',
						] ),
				] ),
		];
	}

	protected function define_base_styles(): array {
		$styles = [
			'display' => String_Prop_Type::generate( 'flex' ),
			'justify-content' => String_Prop_Type::generate( 'center' ),
		];

		return [
			static::BASE_STYLE_KEY => Style_Definition::make()
				->add_variant(
					Style_Variant::make()
						->add_props( $styles )
				),
		];
	}

	protected function get_templates(): array {
		return [
			'elementor/elements/atomic-tabs-menu' => __DIR__ . '/atomic-tabs-menu.html.twig',
		];
	}

	protected function build_template_context(): array {
		return [
			'id' => $this->get_id(),
			'type' => $this->get_name(),
			'settings' => $this->get_atomic_settings(),
			'base_styles' => $this->get_base_styles_dictionary(),
			'interactions' => $this->get_interactions_ids(),
			'children' => $this->render_children_to_html(),
		];
	}
}
