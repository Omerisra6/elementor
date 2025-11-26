import { Alpine } from '@alpinejs/csp';
declare global {
	interface Window {
		Alpine: typeof Alpine;
	}
}
export function init() {
	window.Alpine = Alpine;

	document.addEventListener(
		'DOMContentLoaded',
		() => {
			Alpine.start();
		},
		{ once: true }
	);
}
