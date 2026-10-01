<?php

namespace Elementor\Tests\Phpunit\Modules\Mcp\Oauth;

use Elementor\Modules\Mcp\Oauth\Bearer_Authenticator;
use Elementor\Modules\Mcp\Oauth\Code_Sealer;
use ElementorEditorTesting\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @group Elementor\Modules\Mcp
 */
class Test_Bearer_Authenticator extends Elementor_Test_Base {

	const NO_USER = 0;

	private array $server_backup;

	public function setUp(): void {
		parent::setUp();

		$this->server_backup = $_SERVER;

		add_filter( 'wp_is_application_passwords_available', '__return_true' );
		add_filter( 'application_password_is_api_request', '__return_true' );
	}

	public function tearDown(): void {
		$_SERVER = $this->server_backup;

		remove_filter( 'wp_is_application_passwords_available', '__return_true' );
		remove_filter( 'application_password_is_api_request', '__return_true' );

		parent::tearDown();
	}

	public function test_authenticate__accepts_bearer_on_mcp_route() {
		// Arrange
		$user = $this->set_bearer_request( '/wp-json/elementor/mcp' );

		// Act
		$user_id = ( new Bearer_Authenticator() )->authenticate( self::NO_USER );

		// Assert
		$this->assertSame( $user->ID, $user_id );
	}

	public function test_authenticate__ignores_bearer_on_other_routes() {
		// Arrange
		$this->set_bearer_request( '/wp-json/wp/v2/posts' );

		// Act
		$user_id = ( new Bearer_Authenticator() )->authenticate( self::NO_USER );

		// Assert
		$this->assertSame( self::NO_USER, $user_id );
	}

	public function test_add_www_authenticate__on_mcp_401_only() {
		// Arrange
		$authenticator = new Bearer_Authenticator();
		$server = new \WP_REST_Server();

		// Act
		$mcp_response = $authenticator->add_www_authenticate( new \WP_REST_Response( null, 401 ), $server, new \WP_REST_Request( 'POST', '/elementor/mcp' ) );
		$other_response = $authenticator->add_www_authenticate( new \WP_REST_Response( null, 401 ), $server, new \WP_REST_Request( 'GET', '/wp/v2/posts' ) );

		// Assert
		$this->assertStringContainsString( 'resource_metadata=', $mcp_response->get_headers()['WWW-Authenticate'] );
		$this->assertArrayNotHasKey( 'WWW-Authenticate', $other_response->get_headers() );
	}

	private function set_bearer_request( string $path ): \WP_User {
		$user = $this->factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		[ $password ] = \WP_Application_Passwords::create_new_application_password( $user->ID, [ 'name' => 'Claude (Elementor MCP)' ] );

		$_SERVER['REQUEST_URI'] = $path;
		$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . Code_Sealer::base64url( $user->user_login . ':' . $password );

		return $user;
	}
}
