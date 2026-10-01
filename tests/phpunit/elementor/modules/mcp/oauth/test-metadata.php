<?php

namespace Elementor\Tests\Phpunit\Modules\Mcp\Oauth;

use Elementor\Modules\Mcp\Oauth\Metadata;
use ElementorEditorTesting\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @group Elementor\Modules\Mcp
 */
class Test_Metadata extends Elementor_Test_Base {

	const TUNNEL_BASE = 'https://tunnel.example.com';

	public function tearDown(): void {
		remove_all_filters( 'elementor/mcp/oauth/public_base' );

		parent::tearDown();
	}

	public function test_protected_resource__resource_is_the_mcp_route() {
		// Act
		$document = Metadata::protected_resource();

		// Assert
		$this->assertSame( rest_url( 'elementor/mcp' ), $document['resource'] );
		$this->assertSame( [ rest_url( 'elementor/oauth' ) ], $document['authorization_servers'] );
	}

	public function test_protected_resource__public_base_filter_rewrites_every_url() {
		// Arrange
		add_filter( 'elementor/mcp/oauth/public_base', fn() => self::TUNNEL_BASE );

		// Act
		$resource = Metadata::protected_resource();
		$server = Metadata::authorization_server();

		// Assert
		$this->assertStringStartsWith( self::TUNNEL_BASE . '/', $resource['resource'] );
		$this->assertStringEndsWith( 'elementor/mcp', $resource['resource'] );
		$this->assertStringStartsWith( self::TUNNEL_BASE . '/', $server['issuer'] );
		$this->assertStringStartsWith( self::TUNNEL_BASE . '/', $server['token_endpoint'] );
	}

	public function test_authorization_server__advertises_cimd_and_public_clients() {
		// Act
		$document = Metadata::authorization_server();

		// Assert
		$this->assertTrue( $document['client_id_metadata_document_supported'] );
		$this->assertSame( [ 'none' ], $document['token_endpoint_auth_methods_supported'] );
		$this->assertSame( [ 'S256' ], $document['code_challenge_methods_supported'] );
		$this->assertSame( $document['issuer'] . '/register', $document['registration_endpoint'] );
	}
}
