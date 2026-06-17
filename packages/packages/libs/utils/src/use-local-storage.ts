import { useEffect, useState } from 'react';

export function useLocalStorage< T >( key: string, initialValue: T ) {
	const [ value, setValue ] = useState< T >( () => {
		const stored = localStorage.getItem( key );

		if ( stored === null ) {
			return initialValue;
		}

		try {
			return JSON.parse( stored ) as T;
		} catch {
			return initialValue;
		}
	} );

	useEffect( () => {
		const abortController = new AbortController();

		window.addEventListener(
			'storage',
			( e ) => {
				if ( e.key !== key || e.storageArea !== localStorage ) {
					return;
				}

				const newValue = e.newValue;

				if ( newValue === null ) {
					setValue( initialValue );
					return;
				}

				try {
					setValue( JSON.parse( newValue ) as T );
				} catch {
					setValue( initialValue );
				}
			},
			{ signal: abortController.signal }
		);

		return () => {
			abortController.abort();
		};
	}, [ key, initialValue ] );

	const setStoredValue = ( newValue: T ) => {
		localStorage.setItem( key, JSON.stringify( newValue ) );
		setValue( newValue );
	};

	const removeValue = () => {
		localStorage.removeItem( key );
		setValue( initialValue );
	};

	return [ value, setStoredValue, removeValue ] as const;
}
