<?php
namespace Elementor\Modules\AtomicWidgets\Widgets;

use Elementor\Includes\Elements\Container;
use Elementor\Modules\AtomicWidgets\Base\Atomic_Widget_Base;
use Elementor\Modules\AtomicWidgets\Schema\Atomic_Prop;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Atomic_Container extends Atomic_Widget_Base {
	private $data = [];
	public function __construct( $data = [], $args = null ) {
		if ( isset( $data ) && is_array( $data ) ) {
			$this->data = $data;
		}

		parent::__construct( $data, $args );
	}

	public function get_icon() {
		return 'eicon-container';
	}

	public function get_title() {
		return esc_html__( 'Atomic Container', 'elementor' );
	}

	public function get_name() {
		return 'atomic-container';
	}

	protected function render() {
		$settings = $this->get_atomic_settings();

		$class = '';
		if ( ! empty( $settings['classes'] ) ) {
			$class = "class='" . esc_attr( $settings['classes'] ) . "'";
		}

		$this->data['elType'] = 'container';
		unset( $this->data['classes'] );
		unset( $this->data['version'] );
		unset( $this->data['styles'] );
		unset( $this->data['widgetType'] );

		$container = new Container( $this->data );
		ob_start();
		$container->print_element();
		$content = ob_get_clean();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "<div $class>$content</div>";
	}

	protected function define_atomic_controls(): array {
		return [];
	}

	protected static function define_props_schema(): array {
		return [
			'classes' => Atomic_Prop::make()
				->type( 'classes' )
				->default( [] ),
		];
	}
}
