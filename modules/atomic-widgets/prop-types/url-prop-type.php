<?php

namespace Elementor\Modules\AtomicWidgets\PropTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Url_Prop_Type extends Prop_Type {

	public static function get_key(): string {
		return 'url';
	}

	public function validate( $value ): void {
	}
}
