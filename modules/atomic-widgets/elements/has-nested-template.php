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

			$template_html = $renderer->render( $this->get_main_template(), $context );

			$children_html = $this->render_children_to_html();

			$output = str_replace( $this->get_children_placeholder(), $children_html, $template_html );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $output;
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
			'children_placeholder' => $this->get_children_placeholder(),
		];
	}

	protected function render_children_to_html(): string {
		$html = '';

		foreach ( $this->get_children() as $child ) {
			ob_start();
			$child->print_element();
			$html .= ob_get_clean();
		}

		return $html;
	}

	public function before_render() {
	}

	public function after_render() {
	}

	public function print_content() {
		$this->render();
	}

	protected function get_children_placeholder(): string {
		return '<!-- elementor-children-placeholder -->';
	}
}

