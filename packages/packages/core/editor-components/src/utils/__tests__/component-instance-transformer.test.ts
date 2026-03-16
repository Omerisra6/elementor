import { type V1ElementData } from '@elementor/editor-elements';
import { __getState as getState } from '@elementor/store';

import { componentInstanceTransformer } from '../../component-instance-transformer';
import { selectUnpublishedComponents } from '../../store/store';
import { type OverridableProps, type UnpublishedComponent } from '../../types';
import { getComponentDocumentData } from '../component-document-data';

interface TransformerResult {
	elements: V1ElementData[];
	overrides?: Record< string, unknown >;
}

jest.mock( '@elementor/store', () => ( { __getState: jest.fn() } ) );
jest.mock( '../../store/store', () => ( { selectUnpublishedComponents: jest.fn() } ) );
jest.mock( '../component-document-data', () => ( { getComponentDocumentData: jest.fn() } ) );

const mockGetState = jest.mocked( getState );
const mockSelectUnpublished = jest.mocked( selectUnpublishedComponents );
const mockGetDocumentData = jest.mocked( getComponentDocumentData );

describe( 'componentInstanceTransformer', () => {
	beforeEach( () => {
		jest.clearAllMocks();
		mockGetState.mockReturnValue( {} );
	} );

	it( 'should apply overridable wrappers to unpublished component elements', async () => {
		// Arrange
		const overridableProps: OverridableProps = {
			props: {
				'prop-abc': {
					overrideKey: 'prop-abc',
					label: 'Heading Text',
					elementId: 'heading-1',
					propKey: 'title',
					elType: 'widget',
					widgetType: 'e-heading',
					originValue: 'Hello World',
					groupId: 'group-1',
				},
			},
			groups: {
				items: { 'group-1': { id: 'group-1', label: 'Default', props: [ 'prop-abc' ] } },
				order: [ 'group-1' ],
			},
		};

		const revertedElements: V1ElementData[] = [
			{
				id: 'heading-1',
				elType: 'widget',
				widgetType: 'e-heading',
				settings: { title: 'Hello World' },
				elements: [],
			},
		];

		const unpublished: UnpublishedComponent = {
			uid: 'component-test-uid',
			name: 'Test Component',
			elements: revertedElements,
			overridableProps,
		};

		mockSelectUnpublished.mockReturnValue( [ unpublished ] );

		// Act
		const result = ( await componentInstanceTransformer(
			{ component_id: 'component-test-uid', overrides: [] },
			{ key: 'component_instance' }
		) ) as TransformerResult;

		// Assert
		expect( result.elements[ 0 ]?.settings?.title ).toEqual( {
			$$type: 'overridable',
			value: {
				override_key: 'prop-abc',
				origin_value: 'Hello World',
			},
		} );
	} );

	it( 'should not modify unpublished elements without overridableProps', async () => {
		// Arrange
		const revertedElements: V1ElementData[] = [
			{
				id: 'heading-1',
				elType: 'widget',
				widgetType: 'e-heading',
				settings: { title: 'Hello World' },
				elements: [],
			},
		];

		const unpublished: UnpublishedComponent = {
			uid: 'component-no-overrides',
			name: 'No Overrides',
			elements: revertedElements,
		};

		mockSelectUnpublished.mockReturnValue( [ unpublished ] );

		// Act
		const result = ( await componentInstanceTransformer(
			{ component_id: 'component-no-overrides', overrides: [] },
			{ key: 'component_instance' }
		) ) as TransformerResult;

		// Assert
		expect( result.elements[ 0 ]?.settings?.title ).toBe( 'Hello World' );
	} );

	it( 'should not mutate the stored unpublished elements', async () => {
		// Arrange
		const overridableProps: OverridableProps = {
			props: {
				'prop-1': {
					overrideKey: 'prop-1',
					label: 'Title',
					elementId: 'heading-1',
					propKey: 'title',
					elType: 'widget',
					widgetType: 'e-heading',
					originValue: 'Original',
					groupId: 'group-1',
				},
			},
			groups: {
				items: { 'group-1': { id: 'group-1', label: 'Default', props: [ 'prop-1' ] } },
				order: [ 'group-1' ],
			},
		};

		const storedElements: V1ElementData[] = [
			{
				id: 'heading-1',
				elType: 'widget',
				widgetType: 'e-heading',
				settings: { title: 'Original' },
				elements: [],
			},
		];

		const unpublished: UnpublishedComponent = {
			uid: 'component-immutable',
			name: 'Immutable Test',
			elements: storedElements,
			overridableProps,
		};

		mockSelectUnpublished.mockReturnValue( [ unpublished ] );

		// Act
		await componentInstanceTransformer(
			{ component_id: 'component-immutable', overrides: [] },
			{ key: 'component_instance' }
		);

		// Assert - stored data should be unchanged
		expect( storedElements[ 0 ]?.settings?.title ).toBe( 'Original' );
	} );

	it( 'should load from backend for published components', async () => {
		// Arrange
		mockSelectUnpublished.mockReturnValue( [] );
		mockGetDocumentData.mockResolvedValue( {
			elements: [ { id: 'el-1', elType: 'widget', settings: { title: 'Published' }, elements: [] } ],
		} as never );

		// Act
		const result = ( await componentInstanceTransformer(
			{ component_id: 123, overrides: [] },
			{ key: 'component_instance' }
		) ) as TransformerResult;

		// Assert
		expect( mockGetDocumentData ).toHaveBeenCalledWith( 123 );
		expect( result.elements[ 0 ]?.settings?.title ).toBe( 'Published' );
	} );
} );
