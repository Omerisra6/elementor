import type { V1ElementConfig } from '@elementor/editor-elements';

import { type DomRenderer } from '../renderers/create-dom-renderer';
import { createPropsResolver } from '../renderers/create-props-resolver';
import { settingsTransformersRegistry } from '../settings-transformers-registry';
import { signalizedProcess } from '../utils/signalized-process';
import { type ElementView } from './types';

export type TwigElementConfig = Required<
	Pick< V1ElementConfig, 'twig_templates' | 'twig_main_template' | 'atomic_props_schema' | 'base_styles_dictionary' >
>;

export type TwigRenderContext = {
	id: string;
	type: string;
	settings: Record< string, unknown >;
	base_styles: Record< string, unknown >;
	[ key: string ]: unknown;
};

export type TwigRenderingOptions = {
	type: string;
	renderer: DomRenderer;
	element: TwigElementConfig;
};

export type TwigViewInterface = {
	model: ElementView[ 'model' ];
	triggerMethod: ElementView[ 'triggerMethod' ];
	bindUIElements: ElementView[ 'bindUIElements' ];
	_ensureViewIsIntact: ElementView[ '_ensureViewIsIntact' ];
	resetChildViewContainer: ElementView[ 'resetChildViewContainer' ];
	_isRendering: boolean;
	isRendered: boolean;
};

export function setupTwigRenderer( { renderer, element }: TwigRenderingOptions ) {
	const templateKey = element.twig_main_template;
	const baseStylesDictionary = element.base_styles_dictionary;

	Object.entries( element.twig_templates ).forEach( ( [ key, template ] ) => {
		renderer.register( key, template );
	} );

	const resolveProps = createPropsResolver( {
		transformers: settingsTransformersRegistry,
		schema: element.atomic_props_schema,
	} );

	return {
		templateKey,
		baseStylesDictionary,
		resolveProps,
	};
}

export type ResolvePropsFunction = ReturnType< typeof createPropsResolver >;

export type RenderTemplateOptions< TView extends TwigViewInterface > = {
	view: TView;
	signal: AbortSignal;
	resolveProps: ResolvePropsFunction;
	templateKey: string;
	baseStylesDictionary: Record< string, unknown >;
	type: string;
	renderer: DomRenderer;
	buildContext?: ( baseContext: TwigRenderContext ) => TwigRenderContext;
	afterSettingsResolve?: ( settings: Record< string, unknown > ) => Record< string, unknown >;
	attachContent: ( html: string ) => void;
};

export async function renderTwigTemplate< TView extends TwigViewInterface >( {
	view,
	signal,
	resolveProps,
	templateKey,
	baseStylesDictionary,
	type,
	renderer,
	buildContext,
	afterSettingsResolve,
	attachContent,
}: RenderTemplateOptions< TView > ): Promise< void > {
	view.triggerMethod( 'before:render:template' );

	const process = signalizedProcess( signal )
		.then( ( _: unknown, sig: AbortSignal ) => {
			const settings = view.model.get( 'settings' ).toJSON();

			return resolveProps( {
				props: settings,
				signal: sig,
			} );
		} )
		.then( ( settings: Record< string, unknown > ) => {
			if ( afterSettingsResolve ) {
				return afterSettingsResolve( settings );
			}
			return settings;
		} )
		.then( async ( settings: Record< string, unknown > ) => {
			let context: TwigRenderContext = {
				id: view.model.get( 'id' ),
				type,
				settings,
				base_styles: baseStylesDictionary,
			};

			if ( buildContext ) {
				context = buildContext( context );
			}

			return renderer.render( templateKey, context );
		} )
		.then( ( html: string ) => {
			attachContent( html );
		} );

	await process.execute();

	view.bindUIElements();

	view.triggerMethod( 'render:template' );
}

export function createBeforeRender< TView extends TwigViewInterface >( view: TView ): void {
	view._ensureViewIsIntact();
	view._isRendering = true;
	view.resetChildViewContainer();
	view.triggerMethod( 'before:render', view );
}

export function createAfterRender< TView extends TwigViewInterface >( view: TView ): void {
	view._isRendering = false;
	view.isRendered = true;
	view.triggerMethod( 'render', view );
}

export function canBeTwigTemplated( element: Partial< TwigElementConfig > ): element is TwigElementConfig {
	return !! (
		element.atomic_props_schema &&
		element.twig_templates &&
		element.twig_main_template &&
		element.base_styles_dictionary
	);
}
