import { type V1ElementData } from '@elementor/editor-elements';

import { type OverridableProps } from '../types';

export function applyOverridableWrappersToElements(
	elements: V1ElementData[],
	overridableProps: OverridableProps
): void {
	Object.values( overridableProps.props ).forEach( ( prop ) => {
		const element = findElementById( elements, prop.elementId );

		if ( ! element?.settings ) {
			return;
		}

		element.settings[ prop.propKey ] = {
			$$type: 'overridable',
			value: {
				override_key: prop.overrideKey,
				origin_value: element.settings[ prop.propKey ],
			},
		};
	} );
}

function findElementById( elements: V1ElementData[], targetId: string ): V1ElementData | null {
	for ( const element of elements ) {
		if ( element.id === targetId ) {
			return element;
		}

		if ( element.elements ) {
			const found = findElementById( element.elements, targetId );

			if ( found ) {
				return found;
			}
		}
	}

	return null;
}
