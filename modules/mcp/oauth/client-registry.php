<?php

namespace Elementor\Modules\Mcp\Oauth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Client_Registry {

	const LOOPBACK_HOSTS = [ 'localhost', '127.0.0.1', '[::1]', '::1' ];
	const CLIENT_ID_PREFIX = 'emcp_';
	const CLIENT_ID_LENGTH = 24;
	const CIMD_FETCH_TIMEOUT = 5;
	const DEFAULT_CLIENT_NAME = 'MCP client';

	public static function is_loopback_host( string $host ): bool {
		return in_array( strtolower( $host ), self::LOOPBACK_HOSTS, true );
	}

	public static function is_allowed_redirect_uri( string $uri ): bool {
		$scheme = wp_parse_url( $uri, PHP_URL_SCHEME );
		$host = wp_parse_url( $uri, PHP_URL_HOST );

		if ( ! $scheme || ! $host ) {
			return false;
		}

		return 'https' === $scheme || ( 'http' === $scheme && self::is_loopback_host( $host ) );
	}

	public static function redirect_uri_matches( string $requested, array $registered ): bool {
		if ( in_array( $requested, $registered, true ) ) {
			return true;
		}

		if ( ! self::is_loopback_host( (string) wp_parse_url( $requested, PHP_URL_HOST ) ) ) {
			return false;
		}

		foreach ( $registered as $candidate ) {
			if ( self::without_port( $candidate ) === self::without_port( $requested ) ) {
				return true;
			}
		}

		return false;
	}

	public function register_client( \WP_REST_Request $request ): \WP_REST_Response {
		$body = $request->get_json_params() ? $request->get_json_params() : $request->get_body_params();
		$redirect_uris = isset( $body['redirect_uris'] ) && is_array( $body['redirect_uris'] ) ? array_values( $body['redirect_uris'] ) : [];

		if ( empty( $redirect_uris ) ) {
			return Oauth_Response::error( 'invalid_redirect_uri', 'redirect_uris is required.', \WP_Http::BAD_REQUEST );
		}

		foreach ( $redirect_uris as $uri ) {
			if ( ! is_string( $uri ) || ! self::is_allowed_redirect_uri( $uri ) ) {
				return Oauth_Response::error( 'invalid_redirect_uri', 'Redirect URIs must be https or http loopback.', \WP_Http::BAD_REQUEST );
			}
		}

		$client = [
			'client_id' => self::CLIENT_ID_PREFIX . wp_generate_password( self::CLIENT_ID_LENGTH, false ),
			'client_id_issued_at' => time(),
			'client_name' => sanitize_text_field( $body['client_name'] ?? self::DEFAULT_CLIENT_NAME ),
			'redirect_uris' => $redirect_uris,
			'grant_types' => [ 'authorization_code' ],
			'response_types' => [ 'code' ],
			'token_endpoint_auth_method' => 'none',
		];

		$clients = get_option( Oauth_Server::CLIENTS_OPTION, [] );
		$clients[ $client['client_id'] ] = $client;
		update_option( Oauth_Server::CLIENTS_OPTION, $clients, false );

		return new \WP_REST_Response( $client, \WP_Http::CREATED );
	}

	public function find( string $client_id ): ?array {
		if ( $this->is_metadata_document_url( $client_id ) ) {
			return $this->fetch_metadata_document( $client_id );
		}

		$clients = get_option( Oauth_Server::CLIENTS_OPTION, [] );

		return $clients[ $client_id ] ?? null;
	}

	private function is_metadata_document_url( string $client_id ): bool {
		return 'https' === wp_parse_url( $client_id, PHP_URL_SCHEME ) && '' !== (string) wp_parse_url( $client_id, PHP_URL_PATH );
	}

	private function fetch_metadata_document( string $client_id ): ?array {
		$cache_key = Oauth_Server::CIMD_PREFIX . md5( $client_id );
		$cached = get_transient( $cache_key );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$response = wp_safe_remote_get( $client_id, [
			'timeout' => self::CIMD_FETCH_TIMEOUT,
			'redirection' => 0,
		] );

		if ( is_wp_error( $response ) || \WP_Http::OK !== wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}

		$document = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $document ) || ( $document['client_id'] ?? '' ) !== $client_id || empty( $document['redirect_uris'] ) ) {
			return null;
		}

		$client = [
			'client_id' => $client_id,
			'client_name' => sanitize_text_field( $document['client_name'] ?? $client_id ),
			'redirect_uris' => array_values( array_filter( (array) $document['redirect_uris'], 'is_string' ) ),
		];

		set_transient( $cache_key, $client, Oauth_Server::CIMD_TTL );

		return $client;
	}

	private static function without_port( string $uri ): string {
		$parts = wp_parse_url( $uri );

		return ( $parts['scheme'] ?? '' ) . '://' . ( $parts['host'] ?? '' ) . ( $parts['path'] ?? '' );
	}
}
