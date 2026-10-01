<?php

namespace Elementor\Modules\Mcp\Oauth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Oauth_Response {

	public static function error( string $error, string $description, int $status ): \WP_REST_Response {
		$response = new \WP_REST_Response( [
			'error' => $error,
			'error_description' => $description,
		], $status );
		$response->header( 'Cache-Control', 'no-store' );

		return $response;
	}

	public static function redirect( string $location ): \WP_REST_Response {
		$response = new \WP_REST_Response( null, \WP_Http::FOUND );
		$response->header( 'Location', $location );

		return $response;
	}

	public static function redirect_with_error( string $redirect_uri, string $error, string $state ): \WP_REST_Response {
		return self::redirect( add_query_arg( array_filter( [
			'error' => $error,
			'state' => $state,
		] ), $redirect_uri ) );
	}
}
