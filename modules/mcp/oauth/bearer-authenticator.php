<?php

namespace Elementor\Modules\Mcp\Oauth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bearer_Authenticator {

	const BEARER_PREFIX = 'Bearer ';

	public function authenticate( $user_id ) {
		if ( ! empty( $user_id ) || ! $this->is_mcp_request() ) {
			return $user_id;
		}

		$header = $this->authorization_header();

		if ( 0 !== stripos( $header, self::BEARER_PREFIX ) ) {
			return $user_id;
		}

		$decoded = Code_Sealer::base64url_decode( trim( substr( $header, strlen( self::BEARER_PREFIX ) ) ) );

		if ( false === $decoded || false === strpos( $decoded, ':' ) ) {
			return $user_id;
		}

		[ $login, $password ] = explode( ':', $decoded, 2 );
		$user = wp_authenticate_application_password( null, $login, $password );

		return $user instanceof \WP_User ? $user->ID : $user_id;
	}

	public function treat_mcp_route_as_api_request( bool $is_api_request ): bool {
		return $is_api_request || $this->is_mcp_request();
	}

	public function add_www_authenticate( $response, $server, $request ) {
		if ( ! $response instanceof \WP_HTTP_Response || 0 !== strpos( $request->get_route(), Oauth_Server::MCP_ROUTE ) ) {
			return $response;
		}

		if ( \WP_Http::UNAUTHORIZED === $response->get_status() ) {
			$response->header( 'WWW-Authenticate', sprintf( 'Bearer resource_metadata="%s", scope="%s"', Metadata::protected_resource_url(), Oauth_Server::SCOPE ) );
		}

		return $response;
	}

	private function is_mcp_request(): bool {
		$uri = rawurldecode( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) );

		return false !== strpos( $uri, '/' . rest_get_url_prefix() . Oauth_Server::MCP_ROUTE )
			|| false !== strpos( $uri, 'rest_route=' . Oauth_Server::MCP_ROUTE );
	}

	private function authorization_header(): string {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- credentials must stay byte-exact.
		return (string) wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '' );
	}
}
