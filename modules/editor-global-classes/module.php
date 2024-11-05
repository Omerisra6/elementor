<?php

namespace Elementor\Modules\EditorGlobalClasses;

use Elementor\Core\Base\Module as BaseModule;
use Elementor\Core\Experiments\Manager as Experiments_Manager;
use Elementor\Modules\AtomicWidgets\Module as Atomic_Widgets_Module;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends BaseModule {
	const EXPERIMENT_NAME = 'global_classes';

	// TODO: Add global classes package
	const PACKAGES = [];

	public function get_name() {
		return 'editor-global-classes';
	}

	public function __construct() {
		parent::__construct();

		$this->register_experiment();

		$new_class = [
				'id' => 's-100',
				'label' => 'pinky',
				'variants' => [
					'meta' => [
						'breakpoint' => 'desktop',
						'state' => null,
					],
					'props' => [
						'color' => 'pink',
					],
				],
			];

		$new_class_1 =
			[
				'id' => 's-200',
				'label' => 'orangy',
				'variants' => [
					'meta' => [
						'breakpoint' => 'desktop',
						'state' => null,
					],
					'props' => [
						'color' => 'orange',
					],
				],
			];

		$classes_repository = new Repository();

		$classes_repository->create( $new_class );

		$classes_repository->create( $new_class_1 );
		echo '<pre>';
		var_dump('get_all');
		var_dump( $classes_repository->get_all() );

		$classes_repository->patch( 's-100', [ 'label' => 'pinky-winky' ] );

		var_dump('get_all_pinky-winky');
		var_dump( $classes_repository->get_all() );

		$classes_repository->arrange( [ 's-200', 's-100' ] );

		var_dump('get_all_arranged');
		var_dump( $classes_repository->get_all() );

		$classes_repository->delete( 's-100' );

		var_dump('get_all_deleted');
		var_dump( $classes_repository->get_all() );

		$classes_repository->delete( 's-200' );

		var_dump('get_all_deleted');
		var_dump( $classes_repository->get_all() );
		die;

		$is_feature_active = Plugin::$instance->experiments->is_feature_active( self::EXPERIMENT_NAME );
		$is_atomic_widgets_active = Plugin::$instance->experiments->is_feature_active( Atomic_Widgets_Module::EXPERIMENT_NAME );

		if ( $is_feature_active && $is_atomic_widgets_active ) {
			add_filter( 'elementor/editor/v2/packages', fn( $packages ) => $this->add_packages( $packages ) );
		}
	}

	private function register_experiment() {
		Plugin::$instance->experiments->add_feature( [
			'name' => self::EXPERIMENT_NAME,
			'title' => esc_html__( 'Global Classes', 'elementor' ),
			'description' => esc_html__( 'Enable global CSS classes.', 'elementor' ),
			'hidden' => true,
			'default' => Experiments_Manager::STATE_INACTIVE,
			'release_status' => Experiments_Manager::RELEASE_STATUS_ALPHA,
		] );
	}

	private function add_packages( $packages ) {
		return array_merge( $packages, self::PACKAGES );
	}
}
