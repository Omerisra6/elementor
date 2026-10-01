<?php

namespace Elementor\Tests\Phpunit\Modules\Mcp\Oauth;

use Elementor\Modules\Mcp\Oauth\Client_Registry;
use Elementor\Modules\Mcp\Oauth\Oauth_Server;
use ElementorEditorTesting\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @group Elementor\Modules\Mcp
 */
class Test_Client_Registry extends Elementor_Test_Base {

	const CLAUDE_CALLBACK = 'https://claude.ai/api/mcp/auth_callback';

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
		delete_option( Oauth_Server::CLIENTS_OPTION );

		parent::tearDown();
	}

	public function test_is_allowed_redirect_uri__https_and_loopback_only() {
		// Act & Assert
		$this->assertTrue( Client_Registry::is_allowed_redirect_uri( self::CLAUDE_CALLBACK ) );
		$this->assertTrue( Client_Registry::is_allowed_redirect_uri( 'http://localhost:54321/callback' ) );
		$this->assertTrue( Client_Registry::is_allowed_redirect_uri( 'http://127.0.0.1:8080/callback' ) );
		$this->assertFalse( Client_Registry::is_allowed_redirect_uri( 'http://evil.example.com/callback' ) );
		$this->assertFalse( Client_Registry::is_allowed_redirect_uri( 'javascript:alert(1)' ) );
	}

	public function test_redirect_uri_matches__loopback_ignores_port() {
		// Arrange
		$registered = [ 'http://localhost:3000/callback', self::CLAUDE_CALLBACK ];

		// Act & Assert
		$this->assertTrue( Client_Registry::redirect_uri_matches( 'http://localhost:61234/callback', $registered ) );
		$this->assertTrue( Client_Registry::redirect_uri_matches( self::CLAUDE_CALLBACK, $registered ) );
		$this->assertFalse( Client_Registry::redirect_uri_matches( 'http://localhost:61234/other', $registered ) );
		$this->assertFalse( Client_Registry::redirect_uri_matches( 'https://claude.ai/other', $registered ) );
	}

	public function test_register__creates_a_public_client() {
		// Arrange
		$request = new \WP_REST_Request( 'POST', '/elementor/oauth/register' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( [
			'client_name' => 'Claude',
			'redirect_uris' => [ self::CLAUDE_CALLBACK ],
		] ) );

		// Act
		$response = rest_do_request( $request );

		// Assert
		$this->assertSame( 201, $response->get_status() );
		$this->assertSame( 'none', $response->get_data()['token_endpoint_auth_method'] );
		$this->assertNotNull( ( new Client_Registry() )->find( $response->get_data()['client_id'] ) );
	}

	public function test_register__rejects_non_loopback_http_redirect() {
		// Arrange
		$request = new \WP_REST_Request( 'POST', '/elementor/oauth/register' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( [ 'redirect_uris' => [ 'http://evil.example.com/callback' ] ] ) );

		// Act
		$response = rest_do_request( $request );

		// Assert
		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'invalid_redirect_uri', $response->get_data()['error'] );
	}
}
