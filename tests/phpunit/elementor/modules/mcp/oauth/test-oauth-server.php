<?php

namespace Elementor\Tests\Phpunit\Modules\Mcp\Oauth;

use Elementor\Modules\Mcp\Oauth\Metadata;
use Elementor\Modules\Mcp\Oauth\Oauth_Server;
use ElementorEditorTesting\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @group Elementor\Modules\Mcp
 */
class Test_Oauth_Server extends Elementor_Test_Base {

	public function tearDown(): void {
		remove_action( 'parse_request', [ Metadata::class, 'maybe_serve_well_known' ] );

		parent::tearDown();
	}

	public function test_register__serves_well_known_on_a_hook_that_runs_after_modules_load() {
		// Act
		( new Oauth_Server() )->register();

		// Assert
		$this->assertNotFalse( has_action( 'parse_request', [ Metadata::class, 'maybe_serve_well_known' ] ) );
	}
}
