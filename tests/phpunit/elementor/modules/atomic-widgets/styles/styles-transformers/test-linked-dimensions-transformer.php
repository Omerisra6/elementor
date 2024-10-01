<?php

namespace Elementor\Testing\Modules\AtomicWidgets\Styles\StylesTransformers;

use Elementor\Modules\AtomicWidgets\PropsResolver\StylesTransformers\Linked_Dimensions_Transformer;
use ElementorEditorTesting\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Test_Linked_Dimensions_Transformer extends Elementor_Test_Base {

	public function test_transform__returns_linked_dimensions() {
		// Arrange.
		$link_dimensions_transformer = new Linked_Dimensions_Transformer();

		// Act.
		$transformed_value = $link_dimensions_transformer->transform( [
			'isLinked' => true,
			'top' => '10px',
			'right' => '20px',
			'bottom' => '30px',
			'left' => '40px',
		] );

		// Assert.
		$this->assertSame( '10px 20px 30px 40px', $transformed_value );
	}

	public function test_transform__returns_unset_when_dimension_undefined() {
		// Arrange.
		$link_dimensions_transformer = new Linked_Dimensions_Transformer();

		// Act.
		$transformed_value = $link_dimensions_transformer->transform( [
			'isLinked' => false,
		] );

		// Assert.
		$this->assertSame( 'unset unset unset unset', $transformed_value );
	}

	public function test_transform__returns_unset_when_dimension_null() {
		// Arrange.
		$link_dimensions_transformer = new Linked_Dimensions_Transformer();

		// Act.
		$transformed_value = $link_dimensions_transformer->transform( [
			'isLinked' => false,
			'top' => null,
			'right' => null,
			'bottom' => null,
			'left' => null,
		] );

		// Assert.
		$this->assertSame( 'unset unset unset unset', $transformed_value );
	}
}
