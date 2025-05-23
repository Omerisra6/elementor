import environment from 'elementor-common/utils/environment';
import AddSectionBase from 'elementor-views/add-section/base';

var ContextMenu = require( 'elementor-editor-utils/context-menu' );

module.exports = Marionette.Behavior.extend( {

	defaults: {
		context: 'preview',
		groups: [],
		eventTargets: [ 'el' ],
	},

	events() {
		const events = {};

		this.getOption( 'eventTargets' ).forEach( function( eventTarget ) {
			let eventName = 'contextmenu';

			if ( 'el' !== eventTarget ) {
				eventName += ' ' + eventTarget;
			}

			events[ eventName ] = 'onContextMenu';
		} );

		return events;
	},

	initialize() {
		this.listenTo( this.view.options.model, 'request:contextmenu', this.onRequestContextMenu );
	},

	initContextMenu() {
		const controlSign = environment.mac ? '&#8984;' : '^';

		var contextMenuGroups = this.getOption( 'groups' ),
			deleteGroup = _.findWhere( contextMenuGroups, { name: 'delete' } ),
			afterGroupIndex = contextMenuGroups.indexOf( deleteGroup );

		if ( -1 === afterGroupIndex ) {
			afterGroupIndex = contextMenuGroups.length;
		}

		if (
			'preview' === this.getOption( 'context' ) &&
			$e.components.get( 'document/elements' ).utils.showNavigator()
		) {
			contextMenuGroups.splice( afterGroupIndex, 0, {
				name: 'tools',
				actions: [
					{
						name: 'navigator',
						icon: 'eicon-navigator',
						title: __( 'Structure', 'elementor' ),
						shortcut: controlSign + '+I',
						callback: () => $e.route( 'navigator', {
							reOpen: true,
							model: this.view.model,
						} ),
					},
				],
			} );
		}

		this.contextMenu = new ContextMenu( {
			groups: contextMenuGroups,
			context: this.getOption( 'context' ),
		} );

		this.contextMenu.getModal().on( 'hide', () => this.onContextMenuHide() );
	},

	getContextMenu() {
		if ( ! this.contextMenu ) {
			this.initContextMenu();
		}

		if ( 'preview' === this.getOption( 'context' ) && ! elementor.selection.has( this.view.getContainer?.() ) ) {
			$e.run( 'document/elements/deselect-all' );
		}

		return this.contextMenu;
	},

	onContextMenu( event ) {
		if ( $e.shortcuts.isControlEvent( event ) ) {
			return;
		}

		if ( 'preview' === this.getOption( 'context' ) ) {
			const isAddSectionView = this.view instanceof AddSectionBase;
			if ( ! isAddSectionView && ( ! this.view.container || ! this.view.container.isDesignable() ) ) {
				return;
			}
		}

		event.preventDefault();

		event.stopPropagation();

		// Disable sortable when context menu opened
		// TODO: Should be in UI hook when the context menu will move to command
		if ( this.view._parent ) {
			this.view._parent.triggerMethod( 'toggleSortMode', false );
		}

		this.getContextMenu().show( event );

		elementor.channels.editor.reply( 'contextMenu:targetView', this.view );
	},

	onRequestContextMenu( event ) {
		var modal = this.getContextMenu().getModal(),
			iframe = modal.getSettings( 'iframe' ),
			toolsGroup = _.findWhere( this.contextMenu.getSettings( 'groups' ), { name: 'tools' } );

		toolsGroup.isVisible = false;

		modal.setSettings( 'iframe', null );

		this.onContextMenu( event );

		toolsGroup.isVisible = true;

		modal.setSettings( 'iframe', iframe );
	},

	onContextMenuHide() {
		// Enable sortable when context menu closed
		// TODO: Should be in UI hook when the context menu will move to command
		if ( this.view._parent ) {
			this.view._parent.triggerMethod( 'toggleSortMode', true );
		}

		elementor.channels.editor.reply( 'contextMenu:targetView', null );
	},

	onDestroy() {
		if ( this.contextMenu ) {
			this.contextMenu.destroy();
		}
	},
} );
