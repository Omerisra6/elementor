<?php
namespace Elementor\Modules\AtomicWidgets;

use Elementor\Core\Experiments\Manager as Experiments_Manager;
use Elementor\Core\Base\Module as BaseModule;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends BaseModule {
	const ATOMIC_WIDGET_EXPERIMENT_NAME = 'atomic_widgets';
	const EDITOR_V2_EXPERIMENT_NAME = 'editor_v2';

	const PACKAGES = [];

	public function get_name() {
		return 'atomic-widgets';
	}

	public function __construct() {
		parent::__construct();

		add_filter( 'elementor/editor/v2/packages', [ $this, 'register_atomic_widgets_packages' ] );

		$this->register_atomic_widgets_experiment();
	}

	public function register_atomic_widgets_packages( $packages ) {
		if ( Plugin::$instance->experiments->is_feature_active( self::ATOMIC_WIDGET_EXPERIMENT_NAME ) ) {
			return array_merge( $packages, self::PACKAGES );
		}

		return $packages;
	}

	/**
	 * Adding Atomic Widget experiment.
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function register_atomic_widgets_experiment() {
		Plugin::$instance->experiments->add_feature( [
			'name' => static::ATOMIC_WIDGET_EXPERIMENT_NAME,
			'title' => esc_html__( 'Atomic Widget', 'elementor' ),
			'description' => esc_html__( 'Enable the Atomic Widget experiment to start using the new widgets.', 'elementor' ),
			'default' => Experiments_Manager::STATE_INACTIVE,
			'release_status' => Experiments_Manager::RELEASE_STATUS_DEV,
			'dependencies' => [ static::EDITOR_V2_EXPERIMENT_NAME ],
			'hidden' => true,
		] );
	}
}

