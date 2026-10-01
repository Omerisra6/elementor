<?php

namespace Elementor\Modules\Mcp\Oauth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Token_Controller {

	const INVALID_CODE_MESSAGE = 'Code is invalid, expired, or already used.';

	public function token( \WP_REST_Request $request ): \WP_REST_Response {
		if ( 'authorization_code' !== $request->get_param( 'grant_type' ) ) {
			return Oauth_Response::error( 'unsupported_grant_type', 'Only authorization_code is supported.', \WP_Http::BAD_REQUEST );
		}

		$code = (string) $request->get_param( 'code' );
		$grant = $this->consume_grant( $code );

		if ( null === $grant ) {
			return Oauth_Response::error( 'invalid_grant', self::INVALID_CODE_MESSAGE, \WP_Http::BAD_REQUEST );
		}

		if ( $grant['client_id'] !== $request->get_param( 'client_id' ) || $grant['redirect_uri'] !== $request->get_param( 'redirect_uri' ) ) {
			return Oauth_Response::error( 'invalid_grant', 'Code was issued to a different client or redirect_uri.', \WP_Http::BAD_REQUEST );
		}

		if ( ! $this->is_valid_verifier( (string) $request->get_param( 'code_verifier' ), $grant['code_challenge'] ) ) {
			return Oauth_Response::error( 'invalid_grant', 'PKCE verification failed.', \WP_Http::BAD_REQUEST );
		}

		$access_token = Code_Sealer::unseal( $grant['sealed_token'], $code );

		if ( null === $access_token ) {
			return Oauth_Response::error( 'invalid_grant', self::INVALID_CODE_MESSAGE, \WP_Http::BAD_REQUEST );
		}

		$response = new \WP_REST_Response( [
			'access_token' => $access_token,
			'token_type' => 'Bearer',
			'scope' => Oauth_Server::SCOPE,
		] );
		$response->header( 'Cache-Control', 'no-store' );

		return $response;
	}

	private function consume_grant( string $code ): ?array {
		$code_key = Oauth_Server::CODE_PREFIX . hash( 'sha256', $code );
		$grant = get_transient( $code_key );
		delete_transient( $code_key );

		return is_array( $grant ) ? $grant : null;
	}

	private function is_valid_verifier( string $verifier, string $challenge ): bool {
		return hash_equals( $challenge, Code_Sealer::base64url( hash( 'sha256', $verifier, true ) ) );
	}
}
