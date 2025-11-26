<?php

namespace Elementor\Modules\AtomicWidgets\Elements;

use Elementor\Modules\AtomicWidgets\TemplateRenderer\Template_Renderer;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Has_Nested_Template {
	use Has_Template {
		Has_Template::get_initial_config as parent_get_initial_config;
	}

	public function get_initial_config() {
		$config = $this->parent_get_initial_config();

		$config['support_nesting'] = true;

		return $config;
	}

	protected function render() {
		try {
			$renderer = Template_Renderer::instance();

			foreach ( $this->get_templates() as $name => $path ) {
				if ( $renderer->is_registered( $name ) ) {
					continue;
				}

				$renderer->register( $name, $path );
			}

			$context = $this->build_template_context();

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $renderer->render( $this->get_main_template(), $context );
		} catch ( \Exception $e ) {
			if ( Utils::is_elementor_debug() ) {
				throw $e;
			}
		}
	}

	protected function build_template_context(): array {
		return [
			'id' => $this->get_id(),
			'type' => $this->get_name(),
			'settings' => $this->get_atomic_settings(),
			'base_styles' => $this->get_base_styles_dictionary(),
			'interactions' => $this->get_interactions_ids(),
			'children' => $this->render_children_to_html(),
		];
	}

	protected function render_children_to_html(): array {
		$children_html = [];

		foreach ( $this->get_children() as $child ) {
			$child_type = $child->get_type();

			ob_start();
			$child->print_element();
			$html = ob_get_clean();

			if ( ! isset( $children_html[ $child_type ] ) ) {
				$children_html[ $child_type ] = [];
			}

			$children_html[ $child_type ][] = $html;
		}

		return $children_html;
	}

	protected function render_child_by_type( string $type ): string {
		foreach ( $this->get_children() as $child ) {
			if ( $child->get_type() === $type ) {
				ob_start();
				$child->print_element();
				return ob_get_clean();
			}
		}

		return '';
	}

	public function before_render() {
	}

	public function after_render() {
	}

	public function print_content() {
		$this->render();
	}
}

