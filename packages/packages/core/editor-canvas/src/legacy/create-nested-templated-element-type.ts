import { type DomRenderer } from '../renderers/create-dom-renderer';
import { signalizedProcess } from '../utils/signalized-process';
import {
	canBeTwigTemplated,
	createAfterRender,
	createBeforeRender,
	renderTwigTemplate,
	setupTwigRenderer,
	type TwigElementConfig,
	type TwigRenderContext,
} from './twig-rendering-utils';
import { type ElementType, type ElementView, type LegacyWindow } from './types';

export type NestedTemplatedElementConfig = TwigElementConfig & {
	support_nesting: boolean;
};

export type CreateNestedTemplatedElementTypeOptions = {
	type: string;
	renderer: DomRenderer;
	element: NestedTemplatedElementConfig;
};

export function canBeNestedTemplated(
	element: Partial< NestedTemplatedElementConfig >
): element is NestedTemplatedElementConfig {
	return canBeTwigTemplated( element ) && !! ( element as Partial< NestedTemplatedElementConfig > ).support_nesting;
}

export function createNestedTemplatedElementType( {
	type,
	renderer,
	element,
}: CreateNestedTemplatedElementTypeOptions ): typeof ElementType {
	const legacyWindow = window as unknown as LegacyWindow;

	return class extends legacyWindow.elementor.modules.elements.types.Base {
		getType() {
			return type;
		}

		getView() {
			return createNestedTemplatedElementView( {
				type,
				renderer,
				element,
			} );
		}

		getModel() {
			return legacyWindow.elementor.modules.elements.models.AtomicElementBase;
		}
	};
}

function buildEditorAttributes( model: { get: ( key: 'id' ) => string; cid?: string } ): string {
	const id = model.get( 'id' );
	const cid = ( model as { cid?: string } ).cid ?? '';

	const attrs: Record< string, string > = {
		'data-model-cid': cid,
		'data-interaction-id': id,
	};

	return Object.entries( attrs )
		.map( ( [ key, value ] ) => `${ key }="${ value }"` )
		.join( ' ' );
}

function buildEditorClasses( model: { get: ( key: 'id' ) => string }, elementType: string ): string {
	const id = model.get( 'id' );

	return [
		'elementor-element',
		'elementor-element-edit-mode',
		`elementor-element-${ id }`,
		`${ elementType }-base`,
	].join( ' ' );
}

type AlpineInstance = {
	deferMutations: () => void;
	flushAndStopDeferringMutations: () => void;
};

type PreviewWindow = Window & {
	Alpine?: AlpineInstance;
};

interface NestedTwigView extends ElementView {
	_abortController: AbortController | null;
	setElement: ( element: JQueryElement ) => void;
	_renderTwigTemplate: () => Promise< void >;
	_attachTwigContent: ( html: string ) => void;
}

type JQueryElement = ReturnType< ElementView[ 'getDomElement' ] >;

export function createNestedTemplatedElementView( {
	type,
	renderer,
	element,
}: CreateNestedTemplatedElementTypeOptions ): typeof ElementView {
	const legacyWindow = window as unknown as LegacyWindow;

	const getPreviewAlpine = (): AlpineInstance | undefined =>
		( legacyWindow.elementor?.$preview?.[ 0 ]?.contentWindow as PreviewWindow | undefined )?.Alpine;

	const { templateKey, baseStylesDictionary, resolveProps } = setupTwigRenderer( {
		type,
		renderer,
		element,
	} );

	const AtomicElementBaseView = legacyWindow.elementor.modules.elements.views.createAtomicElementBase( type );

	return AtomicElementBaseView.extend( {
		_abortController: null,

		template: false,

		getTemplateType() {
			return 'twig';
		},

		render( this: NestedTwigView ) {
			if ( this._abortController ) {
				this._abortController.abort();
			}
			this._abortController = new AbortController();

			// eslint-disable-next-line @typescript-eslint/no-this-alias
			const view = this;
			const process = signalizedProcess( this._abortController.signal )
				.then( () => createBeforeRender( view ) )
				.then( () => {
					getPreviewAlpine()?.deferMutations();
					return view._renderTwigTemplate();
				} )
				.then( () => {
					view.dispatchPreviewEvent( 'elementor/element/render' );
					view._renderChildren();
					getPreviewAlpine()?.flushAndStopDeferringMutations();
					createAfterRender( view );
				} );

			return process.execute();
		},

		async _renderTwigTemplate( this: NestedTwigView ) {
			// eslint-disable-next-line @typescript-eslint/no-this-alias
			const view = this;

			await renderTwigTemplate( {
				view: this,
				signal: this._abortController?.signal as AbortSignal,
				resolveProps,
				templateKey,
				baseStylesDictionary,
				type,
				renderer,
				buildContext: ( context: TwigRenderContext ) => ( {
					...context,
					editor_attributes: buildEditorAttributes( view.model ),
					editor_classes: buildEditorClasses( view.model, type ),
				} ),
				attachContent: ( html: string ) => this._attachTwigContent( html ),
			} );
		},

		_attachTwigContent( this: NestedTwigView, html: string ) {
			const $templateRoot = legacyWindow.jQuery( html );
			const $overlay = this.getHandlesOverlay();

			if ( $overlay ) {
				$templateRoot.prepend( $overlay );
			}

			$templateRoot.attr( 'draggable', 'true' );

			const oldEl = this.$el.get( 0 );
			const newEl = $templateRoot.get( 0 );

			if ( oldEl && newEl && oldEl.parentNode ) {
				oldEl.parentNode.replaceChild( newEl, oldEl );
			}

			this.setElement( $templateRoot );
		},

		getChildViewContainer( this: NestedTwigView ) {
			this.childViewContainer = '';
			return this.$el;
		},

		attachBuffer( this: NestedTwigView, _collectionView: ElementView, buffer: DocumentFragment ) {
			this.$el.get( 0 )?.append( buffer );
		},

		getDomElement( this: NestedTwigView ) {
			return this.$el;
		},
	} ) as unknown as typeof ElementView;
}
