<?php

namespace Elementor\Modules\Mcp\Oauth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Metadata {

	public static function public_base(): string {
		/**
		 * The public origin clients use to reach this site, when it differs from home_url()
		 * (reverse proxies, tunnels). The advertised resource must match the URL the user enters.
		 *
		 * @param string $base Site origin without a trailing slash.
		 */
		return untrailingslashit( (string) apply_filters( 'elementor/mcp/oauth/public_base', home_url() ) );
	}

	public static function rest_route_url( string $route ): string {
		$path = substr( rest_url( ltrim( $route, '/' ) ), strlen( untrailingslashit( home_url() ) ) );

		return self::public_base() . $path;
	}

	public static function issuer(): string {
		return self::rest_route_url( Oauth_Server::REST_NAMESPACE );
	}

	public static function mcp_url(): string {
		return self::rest_route_url( Oauth_Server::MCP_ROUTE );
	}

	public static function protected_resource_url(): string {
		return self::issuer() . '/protected-resource';
	}

	public static function protected_resource(): array {
		return [
			'resource' => self::mcp_url(),
			'authorization_servers' => [ self::issuer() ],
			'bearer_methods_supported' => [ 'header' ],
			'scopes_supported' => [ Oauth_Server::SCOPE ],
			'resource_name' => sprintf( 'Elementor MCP (%s)', wp_parse_url( self::public_base(), PHP_URL_HOST ) ),
		];
	}

	public static function authorization_server(): array {
		$issuer = self::issuer();

		return [
			'issuer' => $issuer,
			'authorization_endpoint' => $issuer . '/authorize',
			'token_endpoint' => $issuer . '/token',
			'registration_endpoint' => $issuer . '/register',
			'response_types_supported' => [ 'code' ],
			'grant_types_supported' => [ 'authorization_code' ],
			'code_challenge_methods_supported' => [ 'S256' ],
			'token_endpoint_auth_methods_supported' => [ 'none' ],
			'client_id_metadata_document_supported' => true,
			'scopes_supported' => [ Oauth_Server::SCOPE ],
		];
	}
}
