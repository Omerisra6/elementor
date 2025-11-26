import { type DomRenderer } from '../renderers/create-dom-renderer';
import { signalizedProcess } from '../utils/signalized-process';
import { createElementViewClassDeclaration } from './create-element-type';
import {
	canBeTwigTemplated,
	createAfterRender,
	createBeforeRender,
	renderTwigTemplate,
	setupTwigRenderer,
	type TwigElementConfig,
} from './twig-rendering-utils';
import { type ElementType, type ElementView, type LegacyWindow } from './types';

export type CreateTemplatedElementTypeOptions = {
	type: string;
	renderer: DomRenderer;
	element: TwigElementConfig;
};

export function createTemplatedElementType( {
	type,
	renderer,
	element,
}: CreateTemplatedElementTypeOptions ): typeof ElementType {
	const legacyWindow = window as unknown as LegacyWindow;

	return class extends legacyWindow.elementor.modules.elements.types.Widget {
		getType() {
			return type;
		}

		getView() {
			return createTemplatedElementView( {
				type,
				renderer,
				element,
			} );
		}
	};
}

export { canBeTwigTemplated as canBeTemplated };

export function createTemplatedElementView( {
	type,
	renderer,
	element,
}: CreateTemplatedElementTypeOptions ): typeof ElementView {
	const BaseView = createElementViewClassDeclaration();

	const { templateKey, baseStylesDictionary, resolveProps } = setupTwigRenderer( {
		type,
		renderer,
		element,
	} );

	return class extends BaseView {
		#abortController: AbortController | null = null;

		getTemplateType() {
			return 'twig';
		}

		renderOnChange() {
			this.render();
		}

		render() {
			this.#abortController?.abort();
			this.#abortController = new AbortController();

			// eslint-disable-next-line @typescript-eslint/no-this-alias
			const view = this;
			const process = signalizedProcess( this.#abortController.signal )
				.then( () => createBeforeRender( view ) )
				.then( () => view._renderTemplate() )
				.then( () => {
					view._renderChildren();
					createAfterRender( view );
				} );

			return process.execute();
		}

		async _renderTemplate() {
			await renderTwigTemplate( {
				view: this,
				signal: this.#abortController?.signal as AbortSignal,
				resolveProps,
				templateKey,
				baseStylesDictionary,
				type,
				renderer,
				attachContent: ( html ) => this.$el.html( html ),
			} );
		}
	};
}
