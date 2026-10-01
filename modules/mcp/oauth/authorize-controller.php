<?php

namespace Elementor\Modules\Mcp\Oauth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Authorize_Controller {

	const CORE_CONSENT_PAGE = 'authorize-application.php';
	const APP_NAME_SUFFIX = ' (Elementor MCP)';
	const REQUEST_ID_LENGTH = 32;
	const CODE_LENGTH = 48;

	private Client_Registry $clients;

	public function __construct( Client_Registry $clients ) {
		$this->clients = $clients;
	}

	public function authorize( \WP_REST_Request $request ): \WP_REST_Response {
		$client_id = (string) $request->get_param( 'client_id' );
		$redirect_uri = (string) $request->get_param( 'redirect_uri' );
		$client = $this->clients->find( $client_id );

		if ( ! $client || ! Client_Registry::redirect_uri_matches( $redirect_uri, $client['redirect_uris'] ) ) {
			return Oauth_Response::error( 'invalid_client', 'Unknown client or redirect_uri mismatch.', \WP_Http::BAD_REQUEST );
		}

		if ( ! $this->is_valid_pkce_request( $request ) ) {
			return Oauth_Response::redirect_with_error( $redirect_uri, 'invalid_request', (string) $request->get_param( 'state' ) );
		}

		$request_id = wp_generate_password( self::REQUEST_ID_LENGTH, false );

		set_transient( Oauth_Server::PENDING_PREFIX . $request_id, [
			'client_id' => $client_id,
			'redirect_uri' => $redirect_uri,
			'code_challenge' => (string) $request->get_param( 'code_challenge' ),
			'state' => (string) $request->get_param( 'state' ),
		], Oauth_Server::PENDING_TTL );

		return Oauth_Response::redirect( add_query_arg( [
			'app_name' => rawurlencode( $client['client_name'] . self::APP_NAME_SUFFIX ),
			'success_url' => rawurlencode( add_query_arg( 'req', $request_id, Metadata::issuer() . '/callback' ) ),
		], admin_url( self::CORE_CONSENT_PAGE ) ) );
	}

	public function callback( \WP_REST_Request $request ): \WP_REST_Response {
		$pending_key = Oauth_Server::PENDING_PREFIX . (string) $request->get_param( 'req' );
		$pending = get_transient( $pending_key );
		delete_transient( $pending_key );

		if ( ! is_array( $pending ) ) {
			return Oauth_Response::error( 'invalid_request', 'This authorization request expired. Start the connection again from Claude.', \WP_Http::BAD_REQUEST );
		}

		if ( 'false' === $request->get_param( 'success' ) ) {
			return Oauth_Response::redirect_with_error( $pending['redirect_uri'], 'access_denied', $pending['state'] );
		}

		$password = (string) $request->get_param( 'password' );
		$user = wp_authenticate_application_password( null, (string) $request->get_param( 'user_login' ), $password );

		if ( ! $user instanceof \WP_User ) {
			return Oauth_Response::redirect_with_error( $pending['redirect_uri'], 'access_denied', $pending['state'] );
		}

		$code = wp_generate_password( self::CODE_LENGTH, false );

		set_transient( Oauth_Server::CODE_PREFIX . hash( 'sha256', $code ), [
			'client_id' => $pending['client_id'],
			'redirect_uri' => $pending['redirect_uri'],
			'code_challenge' => $pending['code_challenge'],
			'sealed_token' => Code_Sealer::seal( Code_Sealer::base64url( $user->user_login . ':' . $password ), $code ),
		], Oauth_Server::CODE_TTL );

		return Oauth_Response::redirect( add_query_arg( array_filter( [
			'code' => $code,
			'state' => $pending['state'],
			'iss' => rawurlencode( Metadata::issuer() ),
		] ), $pending['redirect_uri'] ) );
	}

	private function is_valid_pkce_request( \WP_REST_Request $request ): bool {
		return 'code' === $request->get_param( 'response_type' )
			&& 'S256' === $request->get_param( 'code_challenge_method' )
			&& ! empty( $request->get_param( 'code_challenge' ) );
	}
}
