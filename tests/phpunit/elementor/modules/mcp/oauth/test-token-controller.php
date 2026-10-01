<?php

namespace Elementor\Tests\Phpunit\Modules\Mcp\Oauth;

use Elementor\Modules\Mcp\Oauth\Code_Sealer;
use Elementor\Modules\Mcp\Oauth\Oauth_Server;
use ElementorEditorTesting\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @group Elementor\Modules\Mcp
 */
class Test_Token_Controller extends Elementor_Test_Base {

	const REDIRECT_URI = 'http://localhost:43210/callback';
	const CODE_VERIFIER = 'a-code-verifier-that-is-long-enough-for-pkce-0123456789';

	private string $client_id;

	public function setUp(): void {
		parent::setUp();

		global $wp_rest_server;

		$wp_rest_server = new \WP_REST_Server();

		( new Oauth_Server() )->register();

		do_action( 'rest_api_init' );

		add_filter( 'wp_is_application_passwords_available', '__return_true' );
		add_filter( 'application_password_is_api_request', '__return_true' );

		$this->client_id = $this->register_client();
	}

	public function tearDown(): void {
		global $wp_rest_server;

		$wp_rest_server = null;
		delete_option( Oauth_Server::CLIENTS_OPTION );
		remove_filter( 'wp_is_application_passwords_available', '__return_true' );
		remove_filter( 'application_password_is_api_request', '__return_true' );

		parent::tearDown();
	}

	public function test_token__valid_pkce_returns_application_password_bearer() {
		// Arrange
		[ $user, $password ] = $this->create_application_password();
		$code = $this->obtain_code( $user, $password );

		// Act
		$response = $this->exchange( $code, self::CODE_VERIFIER );

		// Assert
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 'Bearer', $response->get_data()['token_type'] );
		$this->assertSame( Code_Sealer::base64url( $user->user_login . ':' . $password ), $response->get_data()['access_token'] );
	}

	public function test_token__wrong_verifier_is_rejected() {
		// Arrange
		[ $user, $password ] = $this->create_application_password();
		$code = $this->obtain_code( $user, $password );

		// Act
		$response = $this->exchange( $code, 'a-different-verifier-that-does-not-match-the-challenge-00' );

		// Assert
		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'invalid_grant', $response->get_data()['error'] );
	}

	public function test_token__code_cannot_be_reused() {
		// Arrange
		[ $user, $password ] = $this->create_application_password();
		$code = $this->obtain_code( $user, $password );
		$this->exchange( $code, self::CODE_VERIFIER );

		// Act
		$response = $this->exchange( $code, self::CODE_VERIFIER );

		// Assert
		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'invalid_grant', $response->get_data()['error'] );
	}

	private function register_client(): string {
		$request = new \WP_REST_Request( 'POST', '/elementor/oauth/register' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( [
			'client_name' => 'Test client',
			'redirect_uris' => [ self::REDIRECT_URI ],
		] ) );

		return rest_do_request( $request )->get_data()['client_id'];
	}

	private function create_application_password(): array {
		$user = $this->factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		[ $password ] = \WP_Application_Passwords::create_new_application_password( $user->ID, [ 'name' => 'Test client (Elementor MCP)' ] );

		return [ $user, $password ];
	}

	private function obtain_code( \WP_User $user, string $password ): string {
		$authorize = new \WP_REST_Request( 'GET', '/elementor/oauth/authorize' );
		$authorize->set_query_params( [
			'client_id' => $this->client_id,
			'redirect_uri' => self::REDIRECT_URI,
			'response_type' => 'code',
			'code_challenge' => Code_Sealer::base64url( hash( 'sha256', self::CODE_VERIFIER, true ) ),
			'code_challenge_method' => 'S256',
			'state' => 'test-state',
		] );

		$consent_location = rest_do_request( $authorize )->get_headers()['Location'];

		$callback = new \WP_REST_Request( 'GET', '/elementor/oauth/callback' );
		$callback->set_query_params( [
			'req' => $this->query_arg( $this->query_arg( $consent_location, 'success_url' ), 'req' ),
			'success' => 'true',
			'user_login' => $user->user_login,
			'password' => $password,
		] );

		return $this->query_arg( rest_do_request( $callback )->get_headers()['Location'], 'code' );
	}

	private function exchange( string $code, string $verifier ): \WP_REST_Response {
		$request = new \WP_REST_Request( 'POST', '/elementor/oauth/token' );
		$request->set_body_params( [
			'grant_type' => 'authorization_code',
			'code' => $code,
			'client_id' => $this->client_id,
			'redirect_uri' => self::REDIRECT_URI,
			'code_verifier' => $verifier,
		] );

		return rest_do_request( $request );
	}

	private function query_arg( string $url, string $key ): string {
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );

		return (string) ( $query[ $key ] ?? '' );
	}
}
