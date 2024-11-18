<?php

namespace Elementor\Modules\GlobalClasses;

use Elementor\Core\Base\Module as BaseModule;
use Elementor\Core\Experiments\Manager as Experiments_Manager;
use Elementor\Modules\AtomicWidgets\Module as Atomic_Widgets_Module;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends BaseModule {
	const NAME = 'global_classes';

	private Global_Classes_REST_API $api;

	// TODO: Add global classes package
	const PACKAGES = [];

	public function get_name() {
		return 'global-classes';
	}

	public function __construct() {
		parent::__construct();

		$this->register_experiment();

		$is_feature_active = Plugin::$instance->experiments->is_feature_active( self::NAME );
		$is_atomic_widgets_active = Plugin::$instance->experiments->is_feature_active( Atomic_Widgets_Module::EXPERIMENT_NAME );

		// TODO: When the `Atomic_Widgets` feature is not hidden, add it as a dependency
		if ( $is_feature_active && $is_atomic_widgets_active ) {
			add_filter( 'elementor/editor/v2/packages', fn( $packages ) => $this->add_packages( $packages ) );
			$kit = Plugin::$instance->kits_manager->get_active_kit();

			if ( $kit->get_id() === 0 ) {
				Plugin::$instance->kits_manager->create_default_kit();
				$kit = Plugin::$instance->kits_manager->get_active_kit();
			}

			$this->api = new Global_Classes_REST_API( $kit );

			$this->api->register_hooks();
		}
	}

	private function register_experiment() {
		Plugin::$instance->experiments->add_feature( [
			'name' => self::NAME,
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
