import { getWidgetsCache } from '@elementor/editor-elements';
import { __privateListenTo, v1ReadyEvent } from '@elementor/editor-v1-adapters';

import { createDomRenderer } from '../renderers/create-dom-renderer';
import { createElementType } from './create-element-type';
import {
	createNestedTemplatedElementType,
	type NestedTemplatedElementConfig,
} from './create-nested-templated-element-type';
import {
	canBeTemplated,
	createTemplatedElementType,
	type CreateTemplatedElementTypeOptions,
} from './create-templated-element-type';
import type { ElementType, LegacyWindow } from './types';

type ElementLegacyType = {
	[ key: string ]: ( options: CreateTemplatedElementTypeOptions ) => typeof ElementType;
};
export const elementsLegacyTypes: ElementLegacyType = {};

export function registerElementType(
	type: string,
	elementTypeGenerator: ElementLegacyType[ keyof ElementLegacyType ]
) {
	elementsLegacyTypes[ type ] = elementTypeGenerator;
}

const NESTED_TEMPLATED_ELEMENT_TYPES = [ 'e-tabs', 'e-tabs-menu', 'e-tabs-content-area', 'e-tab', 'e-tab-content' ];

export function initLegacyViews() {
	__privateListenTo( v1ReadyEvent(), () => {
		const config = getWidgetsCache() ?? {};
		const legacyWindow = window as unknown as LegacyWindow;

		const renderer = createDomRenderer();

		Object.entries( config ).forEach( ( [ type, element ] ) => {
			if ( ! element.atomic ) {
				return;
			}

			if ( NESTED_TEMPLATED_ELEMENT_TYPES.includes( type ) ) {
				return;
			}

			let ElementType;

			if ( !! elementsLegacyTypes[ type ] && canBeTemplated( element ) ) {
				ElementType = elementsLegacyTypes[ type ]( { type, renderer, element } );
			} else if ( canBeTemplated( element ) ) {
				ElementType = createTemplatedElementType( { type, renderer, element } );
			} else {
				ElementType = createElementType( type );
			}

			legacyWindow.elementor.elementsManager.registerElementType( new ElementType() );
		} );

		NESTED_TEMPLATED_ELEMENT_TYPES.forEach( ( type ) => {
			const element = config[ type ];

			if ( ! element ) {
				return;
			}

			const NestedElementType = createNestedTemplatedElementType( {
				type,
				renderer,
				element: element as NestedTemplatedElementConfig,
			} );

			legacyWindow.elementor.elementsManager.registerElementType( new NestedElementType() );
		} );
	} );
}
