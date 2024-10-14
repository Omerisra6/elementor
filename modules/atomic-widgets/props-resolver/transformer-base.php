<?php
namespace Elementor\Modules\AtomicWidgets\PropsResolver;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class Transformer_Base {
	abstract public function transform( $value, $key );

	protected function multi( $value ) {
		return [
			'$$multi' => true,
			'value' => $value,
		];
	}

}
