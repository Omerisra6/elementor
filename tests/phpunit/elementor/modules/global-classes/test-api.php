<?php
namespace Elementor\Testing\Modules\GlobalClasses;

use Elementor\Modules\GlobalClasses\Global_Classes_Repository;
use Elementor\Modules\GlobalClasses\Module;
use Elementor\Modules\AtomicWidgets\Module as Atomic_Widgets_Module;
use Elementor\Core\Experiments\Manager as Experiments_Manager;
use Elementor\Plugin;
use ElementorEditorTesting\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Test_API extends Elementor_Test_Base {
	private $mock_global_class = [
		"label" => "flexy",
		"variants" => [
			[
				"meta" => [
					"breakpoint" => "desktop",
					"state" => null
				],
				'props' => [
					'color' => [
						'$$type' => 'color',
						'value' => 'pink',
					],
				],
			]
		]
	];

	private $mock_global_classes = [
		'items' => [
			'g-4-123' => [
				'id' => 'g-4-123',
				'label' => 'pinky',
				'variants' => [
					[
						'meta' => [
							'breakpoint' => 'desktop',
							'state' => null,
						],
						'props' => [
							'color' => [
								'$$type' => 'color',
								'value' => 'pink',
							],
						],
					],
				],
			],
			'g-4-124' => [
				'id' => 'g-4-124',
				'label' => 'bluey',
				'variants' => [
					[
						'meta' => [
							'breakpoint' => 'desktop',
							'state' => null,
						],
						'props' => [
							'color' => [
								'$$type' => 'color',
								'value' => 'blue',
							],
						],
					],
				],
			],
		],
		'order' => [ 'g-4-123', 'g-4-124' ],
	];

    public function set_up() {
        parent::set_up();

        $this->experiment_on();

        do_action( 'rest_api_init' );
    }

	public function tear_down() {
		parent::tear_down();

		remove_all_actions( 'rest_api_init' );

		Plugin::$instance->kits_manager->get_active_kit()->delete_meta( Global_Classes_Repository::META_KEY );

        $this->experiment_off();
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		update_option( Plugin::$instance->kits_manager::OPTION_ACTIVE, 0 );
	}

	public function test_get__returns_all_global_classes() {
		// Arrange
		$this->act_as_admin();

		Plugin::$instance->kits_manager->get_active_kit()->update_json_meta( Global_Classes_Repository::META_KEY, $this->mock_global_classes );

		// Act
		$request = new \WP_REST_Request( 'GET', '/elementor/v1/global-classes' );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( $this->mock_global_classes, $response->get_data() );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_get__returns_empty_data_when_no_classes() {
		// Arrange
		$this->act_as_admin();

		// Act
		$request = new \WP_REST_Request( 'GET', '/elementor/v1/global-classes' );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( [
			'items' => [],
			'order' => [],
		], $response->get_data() );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_get__returns_error_when_unauthorized() {
		// Arrange
		$this->act_as_subscriber();

		// Act
		$request = new \WP_REST_Request( 'GET', '/elementor/v1/global-classes' );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 403, $response->get_status() );
	}

	public function test_get_by_id__returns_single_class() {
		// Arrange
		$this->act_as_admin();

		Plugin::$instance->kits_manager->get_active_kit()->update_json_meta( Global_Classes_Repository::META_KEY, $this->mock_global_classes );

		// Act
		$request = new \WP_REST_Request( 'GET', '/elementor/v1/global-classes/g-4-123' );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( $this->mock_global_classes['items']['g-4-123'], $response->get_data() );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_get_by_id__returns_error_when_class_not_found() {
		// Arrange
		$this->act_as_admin();

		// Act
		$request = new \WP_REST_Request( 'GET', '/elementor/v1/global-classes/g-4-123' );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_get_by_id__returns_error_when_unauthorized() {
		// Arrange
		$this->act_as_subscriber();

		// Act
		$request = new \WP_REST_Request( 'GET', '/elementor/v1/global-classes/g-4-123' );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 403, $response->get_status() );
	}

	public function test_delete__removes_class() {
		// Arrange
		$this->act_as_admin();

		Plugin::$instance->kits_manager->get_active_kit()->update_json_meta( Global_Classes_Repository::META_KEY, $this->mock_global_classes );

		// Act
		$request = new \WP_REST_Request( 'DELETE', '/elementor/v1/global-classes/g-4-123' );
		$response = rest_do_request( $request );

		// Assert
		$classes = Plugin::$instance->kits_manager->get_active_kit()->get_json_meta( Global_Classes_Repository::META_KEY );

		$this->assertEquals( 204, $response->get_status() );
		$this->assertArrayNotHasKey( 'g-4-123', $classes['items'] );
	}

	public function test_delete__returns_error_when_class_not_found() {
		// Arrange
		$this->act_as_admin();

		// Act
		$request = new \WP_REST_Request( 'DELETE', '/elementor/v1/global-classes/g-4-123' );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_delete__returns_error_when_unauthorized() {
		// Arrange
		$this->act_as_subscriber();

		// Act
		$request = new \WP_REST_Request( 'DELETE', '/elementor/v1/global-classes/g-4-123' );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 403, $response->get_status() );
	}

	public function test_put__updates_class() {
		// Arrange
		$this->act_as_admin();

		Plugin::$instance->kits_manager->get_active_kit()->update_json_meta( Global_Classes_Repository::META_KEY, $this->mock_global_classes );

		// Act
		$request = new \WP_REST_Request( 'PUT', '/elementor/v1/global-classes/g-4-123' );
		$updated_class = array_merge( $this->mock_global_class, [ 'label' => 'new label' ] );
		$request->set_body_params( $updated_class );
		$response = rest_do_request( $request );

		// Assert
		$classes = Plugin::$instance->kits_manager->get_active_kit()->get_json_meta( Global_Classes_Repository::META_KEY );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 'new label', $classes['items']['g-4-123']['label'] );
	}

	public function test_put__returns_error_when_class_not_found(){
		// Arrange
		$this->act_as_admin();

		// Act
		$request = new \WP_REST_Request( 'PUT', '/elementor/v1/global-classes/g-4-123' );
		$request->set_body_params( $this->mock_global_class );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_put__returns_error_when_data_invalid() {
		// Arrange
		$this->act_as_admin();

		// Act
		$request = new \WP_REST_Request( 'PUT', '/elementor/v1/global-classes/g-4-123' );
		$request->set_body_params( [] );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 400, $response->get_status() );
	}

	public function test_put__returns_error_when_unauthorized() {
		// Arrange
		$this->act_as_subscriber();

		// Act
		$request = new \WP_REST_Request( 'PUT', '/elementor/v1/global-classes/g-4-123' );
		$request->set_body_params( $this->mock_global_class );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 403, $response->get_status() );
	}

	public function test_post__creates_new_class() {
		// Arrange
		$this->act_as_admin();

		// Act
		$request = new \WP_REST_Request( 'POST', '/elementor/v1/global-classes' );
		$request->set_body_params( $this->mock_global_class );
		$response = rest_do_request( $request );

		// Assert
		$classes = Plugin::$instance->kits_manager->get_active_kit()->get_json_meta( Global_Classes_Repository::META_KEY );
		$id = $response->get_data()['id'];

		$this->assertEquals( 201, $response->get_status() );
		$this->assertArrayHasKey( 'items', $classes );
		$this->assertArrayHasKey( 'order', $classes );
		$this->assertArrayHasKey( $id, $classes['items'] );
	}

	public function test_post__returns_error_when_data_invalid() {
		// Arrange
		$this->act_as_admin();

		// Act
		$request = new \WP_REST_Request( 'POST', '/elementor/v1/global-classes' );
		$request->set_body_params( [] );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 400, $response->get_status() );
	}

	public function test_post__returns_error_when_unauthorized() {
		// Arrange
		$this->act_as_subscriber();

		// Act
		$request = new \WP_REST_Request( 'POST', '/elementor/v1/global-classes' );
		$request->set_body_params( $this->mock_global_class );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 403, $response->get_status() );
	}

	public function test_put_order__updates_order() {
		// Arrange
		$this->act_as_admin();

		Plugin::$instance->kits_manager->get_active_kit()->update_json_meta( Global_Classes_Repository::META_KEY, $this->mock_global_classes );

		// Act
		$request = new \WP_REST_Request( 'PUT', '/elementor/v1/global-classes-order' );
		$request->set_body_params( [ 'g-4-124', 'g-4-123' ] );
		$response = rest_do_request( $request );

		// Assert
		$classes = Plugin::$instance->kits_manager->get_active_kit()->get_json_meta( Global_Classes_Repository::META_KEY );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( [ 'g-4-124', 'g-4-123' ], $classes['order'] );
	}

	public function test_put_order__returns_error_when_class_id_missing(){
		// Arrange
		$this->act_as_admin();

		// Act
		$request = new \WP_REST_Request( 'PUT', '/elementor/v1/global-classes-order' );
		$request->set_body_params( [ 'g-4-124' ] );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 400, $response->get_status() );
	}

	public function test_put_order__returns_error_when_class_not_exists_in_data(){
		// Arrange
		$this->act_as_admin();

		Plugin::$instance->kits_manager->get_active_kit()->update_json_meta( Global_Classes_Repository::META_KEY, $this->mock_global_classes );

		// Act
		$request = new \WP_REST_Request( 'PUT', '/elementor/v1/global-classes-order' );
		$request->set_body_params( [ 'g-4-124', 'g-4-123', 'g-4-125' ] );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 400, $response->get_status() );
	}

	public function test_put_order__returns_error_when_unauthorized(){
		// Arrange
		$this->act_as_subscriber();

		Plugin::$instance->kits_manager->get_active_kit()->update_json_meta( Global_Classes_Repository::META_KEY, $this->mock_global_classes );

		// Act
		$request = new \WP_REST_Request( 'PUT', '/elementor/v1/global-classes-order' );
		$request->set_body_params( [ 'g-4-124', 'g-4-123' ] );
		$response = rest_do_request( $request );

		// Assert
		$this->assertEquals( 403, $response->get_status() );
	}

    private function experiment_on() {
        Plugin::instance()->experiments->set_feature_default_state( Atomic_Widgets_Module::EXPERIMENT_NAME, Experiments_Manager::STATE_ACTIVE );
        Plugin::instance()->experiments->set_feature_default_state( Module::NAME, Experiments_Manager::STATE_ACTIVE );
    }

    private function experiment_off() {
        Plugin::instance()->experiments->set_feature_default_state( Atomic_Widgets_Module::EXPERIMENT_NAME, Experiments_Manager::STATE_INACTIVE );
        Plugin::instance()->experiments->set_feature_default_state( Module::NAME, Experiments_Manager::STATE_INACTIVE );
    }
}
