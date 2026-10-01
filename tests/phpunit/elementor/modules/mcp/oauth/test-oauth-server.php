<?php

namespace Elementor\Tests\Phpunit\Modules\Mcp\Oauth;

use Elementor\Modules\Mcp\Oauth\Metadata;
use Elementor\Modules\Mcp\Oauth\Oauth_Server;
use ElementorEditorTesting\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @group Elementor\Modules\Mcp
 */
class Test_Oauth_Server extends Elementor_Test_Base {

	public function setUp(): void {
		parent::setUp();

		global $wp_rest_server;

		$wp_rest_server = new \WP_REST_Server();

		( new Oauth_Server() )->register();

		do_action( 'rest_api_init' );
	}

	public function tearDown(): void {
		global $wp_rest_server;

		$wp_rest_server = null;

		parent::tearDown();
	}

	public function test_register__serves_nothing_outside_the_rest_namespace() {
		// Arrange
		$well_known_routes = array_filter(
			array_keys( rest_get_server()->get_routes() ),
			fn( $route ) => false !== strpos( $route, '.well-known' )
		);

		// Act & Assert
		$this->assertFalse( $this->hooks_metadata( 'parse_request' ) );
		$this->assertFalse( $this->hooks_metadata( 'init' ) );
		$this->assertNotEmpty( $well_known_routes );

		foreach ( $well_known_routes as $route ) {
			$this->assertStringStartsWith( '/' . Oauth_Server::REST_NAMESPACE . '/', $route );
		}
	}

	public function test_openid_configuration__path_appended_to_issuer_returns_authorization_server_metadata() {
		// Arrange
		$request = new \WP_REST_Request( 'GET', '/' . Oauth_Server::REST_NAMESPACE . '/.well-known/openid-configuration' );

		// Act
		$response = rest_do_request( $request );

		// Assert
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( Metadata::authorization_server(), $response->get_data() );
	}

	private function hooks_metadata( string $hook ): bool {
		global $wp_filter;

		foreach ( $wp_filter[ $hook ]->callbacks ?? [] as $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( is_array( $callback['function'] ) && Metadata::class === $callback['function'][0] ) {
					return true;
				}
			}
		}

		return false;
	}
}
