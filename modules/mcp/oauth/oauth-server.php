<?php

namespace Elementor\Modules\Mcp\Oauth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Oauth_Server {

	const REST_NAMESPACE = 'elementor/oauth';
	const MCP_ROUTE = '/elementor/mcp';
	const CLIENTS_OPTION = 'elementor_mcp_oauth_clients';
	const PENDING_PREFIX = 'elementor_mcp_oauth_req_';
	const CODE_PREFIX = 'elementor_mcp_oauth_code_';
	const CIMD_PREFIX = 'elementor_mcp_oauth_cimd_';
	const PENDING_TTL = 10 * MINUTE_IN_SECONDS;
	const CODE_TTL = MINUTE_IN_SECONDS;
	const CIMD_TTL = HOUR_IN_SECONDS;
	const SCOPE = 'mcp';

	public function register(): void {
		$clients = new Client_Registry();
		$authorize = new Authorize_Controller( $clients );
		$token = new Token_Controller();
		$bearer = new Bearer_Authenticator();

		add_action( 'parse_request', [ Metadata::class, 'maybe_serve_well_known' ], 0 );
		add_action( 'rest_api_init', fn() => $this->register_routes( $clients, $authorize, $token ) );
		add_filter( 'determine_current_user', [ $bearer, 'authenticate' ], 30 );
		add_filter( 'application_password_is_api_request', [ $bearer, 'treat_mcp_route_as_api_request' ] );
		add_filter( 'rest_post_dispatch', [ $bearer, 'add_www_authenticate' ], 10, 3 );
	}

	private function register_routes( Client_Registry $clients, Authorize_Controller $authorize, Token_Controller $token ): void {
		$routes = [
			'/protected-resource' => [ 'GET', fn() => new \WP_REST_Response( Metadata::protected_resource() ) ],
			'/.well-known/oauth-authorization-server' => [ 'GET', fn() => new \WP_REST_Response( Metadata::authorization_server() ) ],
			'/register' => [ 'POST', [ $clients, 'register_client' ] ],
			'/authorize' => [ 'GET', [ $authorize, 'authorize' ] ],
			'/callback' => [ 'GET', [ $authorize, 'callback' ] ],
			'/token' => [ 'POST', [ $token, 'token' ] ],
		];

		foreach ( $routes as $route => [ $method, $callback ] ) {
			register_rest_route( self::REST_NAMESPACE, $route, [
				'methods' => $method,
				'callback' => $callback,
				'permission_callback' => '__return_true',
			] );
		}
	}
}
