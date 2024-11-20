<?php

namespace Elementor\Modules\GlobalClasses;

use Elementor\Core\Utils\Collection;
use Elementor\Modules\AtomicWidgets\Styles\Style_Schema;
use Elementor\Modules\AtomicWidgets\Validators\Style_Validator;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Global_Classes_REST_API {
	const API_NAMESPACE = 'elementor/v1';
	const API_BASE = 'global-classes';

	private $repository = null;

	public function register_hooks() {
		add_action( 'rest_api_init', fn() => $this->register_routes() );
	}

	private function get_repository() {
		if ( ! $this->repository ) {
			$this->repository = new Global_Classes_Repository( Plugin::$instance->kits_manager->get_active_kit() );
		}

		return $this->repository;
	}

	// TODO: Add sanitization when implemented on prop types [EDS-574]
	private function register_routes() {
		register_rest_route( self::API_NAMESPACE, '/' . self::API_BASE, [
			[
				'methods' => 'GET',
				'callback' => fn() => $this->route_wrapper( fn() => $this->all() ),
				'permission_callback' => fn() => current_user_can( 'manage_options' ),
			],
		] );

		register_rest_route( self::API_NAMESPACE, '/' . self::API_BASE . '/(?P<id>[\w-]+)', [
			[
				'methods' => 'GET',
				'callback' => fn( $request ) => $this->route_wrapper( fn() => $this->get( $request ) ),
				'args' => [
					'id' => [
						'type' => 'string',
						'required' => true,
					],
				],
				'permission_callback' => fn() => current_user_can( 'manage_options' ),
			],
		] );

		register_rest_route( self::API_NAMESPACE, '/' . self::API_BASE . '/(?P<id>[\w-]+)', [
			[
				'methods' => 'DELETE',
				'callback' => fn( $request ) => $this->route_wrapper( fn() => $this->delete( $request ) ),
				'args' => [
					'id' => [
						'type' => 'string',
						'required' => true,
					],
				],
				'permission_callback' => fn() => current_user_can( 'manage_options' ),
			],
		] );

		register_rest_route( self::API_NAMESPACE, '/' . self::API_BASE . '/(?P<id>[\w-]+)', [
			[
				'methods' => 'PUT',
				'callback' => fn( $request ) => $this->route_wrapper( fn() => $this->patch( $request ) ),
				'validate_callback' => function( \WP_REST_Request $request ) {
					[ $is_valid ] = Style_Validator::make( Style_Schema::get() )
						->without_id()
						->validate( $request->get_body_params() );

					return $is_valid;
				},
				'permission_callback' => fn() => current_user_can( 'manage_options' ),
			],
		] );

		register_rest_route( self::API_NAMESPACE, '/' . self::API_BASE, [
			[
				'methods' => 'POST',
				'callback' => fn( $request ) => $this->route_wrapper( fn() =>  $this->create( $request ) ),
				'validate_callback' => function( \WP_REST_Request $request ) {
					[ $is_valid ] = Style_Validator::make( Style_Schema::get() )
						->without_id()
						->validate( $request->get_body_params() );

					return $is_valid;
				},
				'permission_callback' => fn() => current_user_can( 'manage_options' ),
			],
		] );

		register_rest_route( self::API_NAMESPACE, '/' . self::API_BASE . '-order', [
			[
				'methods' => 'PUT',
				'callback' => fn( $request ) => $this->route_wrapper( fn() =>  $this->arrange( $request ) ),
				'validate_callback' => function( \WP_REST_Request $request ) {
					$order = $request->get_params();

					if ( ! is_array( $order ) ) {
						return false;
					}

					$classes = $this->get_repository()->all();

					return Collection::make( $order )
							->every( fn( $id ) => $classes->get_order()->contains( $id ) );
				},
				'permission_callback' => fn() => current_user_can( 'manage_options' ),
			],
		] );
	}

	private function all() {
		$classes = $this->get_repository()->all();

		return $classes->get();
	}

	private function get( \WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		$class = $this->get_repository()->get( $id );

		if ( null === $class ) {
			return new \WP_Error( 'entity_not_found', __( 'Global class not found', 'elementor' ), [ 'status' => 404 ] );
		}

		return $class;
	}

	private function delete( \WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		$class = $this->get_repository()->get( $id );

		if ( null === $class ) {
			return new \WP_Error( 'entity_not_found', __( 'Global class not found', 'elementor' ), [ 'status' => 404 ] );
		}

		$this->get_repository()->delete( $id );

		return new \WP_REST_Response( null, 204 );
	}

	private function patch( \WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		$values = $request->get_params();

		// Remove the id from the updated values, as it should not be updated
		unset( $values['id'] );

		$class = $this->get_repository()->get( $id );

		if ( null === $class ) {
			return new \WP_Error( 'entity_not_found', __( 'Global class not found', 'elementor' ), [ 'status' => 404 ] );
		}

		$values = $this->get_repository()->patch( $id, $values );

		return new \WP_REST_Response( $values, 200 );
	}

	private function create( \WP_REST_Request $request ) {
		$class = $request->get_params();
		$new = $this->get_repository()->create( $class );

		return new \WP_REST_Response( $new, 201 );
	}

	private function arrange( \WP_REST_Request $request ) {
		$order = $request->get_params();
		$updated = $this->get_repository()->arrange( $order );

		return new \WP_REST_Response( $updated, 200 );
	}

	private function route_wrapper( callable $cb ) {
		try {
			$response = $cb();
		} catch ( \Exception $e ) {
			return new \WP_Error( 'unexpected_error', __( 'Something went wrong', 'elementor' ), [ 'status' => 500 ] );
		}

		return $response;
	}
}
