/**
 * Custom jQuery for Custom Metaboxes and Fields
 */

/*jslint browser: true, devel: true, indent: 4, maxerr: 50, sub: true */
/*global jQuery, tb_show, tb_remove */

'use strict';

var dkrtbs = {

	_initCallbacks: [],
	_clonedFieldCallbacks: [],
	_deletedFieldCallbacks: [],

	_sortStartCallbacks: [],
	_sortEndCallbacks: [],

	init : function() {

		jQuery( '.field.repeatable' ).each( function() {
			dkrtbs.isMaxFields( jQuery(this) );
		} );

		// Unbind & Re-bind all dkrtbs events to prevent duplicates.
		jQuery(document).unbind( 'click.dkrtbs' );
		jQuery(document).on( 'click.dkrtbs', '.dkrtbs-delete-field', dkrtbs.deleteField );
		jQuery(document).on( 'click.dkrtbs', '.repeat-field', dkrtbs.repeatField );

		// When toggling the display of the meta box container - reinitialize
		jQuery(document).on( 'click.dkrtbs', '.postbox h3, .postbox .handlediv', dkrtbs.init );

		dkrtbs.doneInit();

		jQuery('.field.dkrtbs-sortable' ).each( function() {
			dkrtbs.sortableInit( jQuery(this) );
		} );


	},

	repeatField : function( e ) {

		var templateField, newT, field, index, attr;

		field = jQuery( this ).closest('.field' );

		e.preventDefault();
		jQuery(this).blur();

		if ( dkrtbs.isMaxFields( field, 1 ) )
			return;

		templateField = field.children( '.field-item.hidden' );

		newT = templateField.clone();
		newT.removeClass( 'hidden' );

		var excludeInputTypes = '[type=submit],[type=button],[type=checkbox],[type=radio],[readonly]';
		newT.find( 'input' ).not( excludeInputTypes ).val( '' );

		newT.find( '.dkrtbs_upload_status' ).html('');

		newT.insertBefore( templateField );

		 // Recalculate group ids & update the name fields..
		index = 0;
		attr  = ['id','name','for','data-id','data-name'];

		field.children( '.field-item' ).not( templateField ).each( function() {

			var search  = field.hasClass( 'dkrtbs_Group_Field' ) ? /dkrtbs-group-(\d|x)*/g : /dkrtbs-field-(\d|x)*/g;
			var replace = field.hasClass( 'dkrtbs_Group_Field' ) ? 'dkrtbs-group-' + index : 'dkrtbs-field-' + index;

			jQuery(this).find( '[' + attr.join('],[') + ']' ).each( function() {

				for ( var i = 0; i < attr.length; i++ )
					if ( typeof( jQuery(this).attr( attr[i] ) ) !== 'undefined' )
						jQuery(this).attr( attr[i], jQuery(this).attr( attr[i] ).replace( search, replace ) );

			} );

			index += 1;

		} );

		dkrtbs.clonedField( newT );

		if ( field.hasClass( 'dkrtbs-sortable' ) )
			dkrtbs.sortableInit( field );


	},

	deleteField : function( e ) {

		var fieldItem, field;

		e.preventDefault();
		jQuery(this).blur();

		if ( ! confirm( dkrtbsData.strings.confirmDeleteField ) ) {
			return;
		}

		fieldItem = jQuery( this ).closest('.field-item' );
		field     = fieldItem.closest( '.field' );

		dkrtbs.isMaxFields( field, -1 );
		dkrtbs.deletedField( fieldItem );

		fieldItem.remove();

	},

	/**
	 * Prevent having more than the maximum number of repeatable fields.
	 * When called, if there is the maximum, disable .repeat-field button.
	 * Note: Information Passed using data-max attribute on the .field element.
	 *
	 * @param jQuery .field
	 * @param int modifier - adjust count by this ammount. 1 If adding a field, 0 if checking, -1 if removing a field... etc
	 * @return null
	 */
	isMaxFields: function( field, modifier ) {

		var count, addBtn, min, max, count;

		modifier = (modifier) ? parseInt( modifier, 10 ) : 0;

		addBtn = field.children( '.repeat-field' );
		count  = field.children('.field-item').not('.hidden').length + modifier; // Count after anticipated action (modifier)
		max    = field.attr( 'data-rep-max' );

		// Show all the remove field buttons.
		field.find( '> .field-item > .dkrtbs-delete-field, > .field-item > .group > .dkrtbs-delete-field' ).show();

		if ( typeof( max ) === 'undefined' )
			return false;

		// Disable the add new field button?
		if ( count >= parseInt( max, 10 ) )
			addBtn.attr( 'disabled', 'disabled' );
		else
			addBtn.removeAttr( 'disabled' );

		if ( count > parseInt( max, 10 ) )
			return true;

	},

	addCallbackForInit: function( callback ) {

		this._initCallbacks.push( callback )

	},

	/**
	 * Fire init callbacks.
	 * Called when dkrtbs has been set up.
	 */
	doneInit: function() {

		var _this = this,
			callbacks = dkrtbs._initCallbacks;

		if ( callbacks ) {
			for ( var a = 0; a < callbacks.length; a++) {
				callbacks[a]();
			}
		}

	},

	addCallbackForClonedField: function( fieldName, callback ) {

		if ( jQuery.isArray( fieldName ) )
			for ( var i = 0; i < fieldName.length; i++ )
				dkrtbs.addCallbackForClonedField( fieldName[i], callback );

		this._clonedFieldCallbacks[fieldName] = this._clonedFieldCallbacks[fieldName] ? this._clonedFieldCallbacks[fieldName] : []
		this._clonedFieldCallbacks[fieldName].push( callback )

	},

	/**
	 * Fire clonedField callbacks.
	 * Called when a field has been cloned.
	 */
	clonedField: function( el ) {

		// also check child elements
		el.add( el.find( 'div[data-class]' ) ).each( function( i, el ) {

			el = jQuery( el )
			var callbacks = dkrtbs._clonedFieldCallbacks[el.attr( 'data-class') ]

			if ( callbacks )
				for ( var a = 0; a < callbacks.length; a++ )
					callbacks[a]( el );

		})
	},

	addCallbackForDeletedField: function( fieldName, callback ) {

		if ( jQuery.isArray( fieldName ) )
			for ( var i = 0; i < fieldName.length; i++ )
				dkrtbs._deletedFieldCallbacks( fieldName[i], callback );

		this._deletedFieldCallbacks[fieldName] = this._deletedFieldCallbacks[fieldName] ? this._deletedFieldCallbacks[fieldName] : []
		this._deletedFieldCallbacks[fieldName].push( callback )

	},

	/**
	 * Fire deletedField callbacks.
	 * Called when a field has been cloned.
	 */
	deletedField: function( el ) {

		// also check child elements
		el.add( el.find( 'div[data-class]' ) ).each( function(i, el) {

			el = jQuery( el )
			var callbacks = dkrtbs._deletedFieldCallbacks[el.attr( 'data-class') ]

			if ( callbacks )
				for ( var a = 0; a < callbacks.length; a++ )
					callbacks[a]( el )

		})
	},

	sortableInit : function( field ) {

		var items = field.find(' > .field-item').not('.hidden');

		field.find( '> .field-item > .dkrtbs-handle' ).remove();

		items.each( function() {
			jQuery(this).append( '<div class="dkrtbs-handle"></div>' );
		} );

		field.sortable( {
			handle: "> .dkrtbs-handle" ,
			cursor: "move",
			items: " > .field-item",
			beforeStop: function( event, ui ) { dkrtbs.sortStart( jQuery( ui.item[0] ) ); },
			deactivate: function( event, ui ) { dkrtbs.sortEnd( jQuery( ui.item[0] ) ); },
		} );

	},

	sortStart : function ( el ) {

		// also check child elements
		el.add( el.find( 'div[data-class]' ) ).each( function(i, el) {

			el = jQuery( el )
			var callbacks = dkrtbs._sortStartCallbacks[el.attr( 'data-class') ]

			if ( callbacks )
				for ( var a = 0; a < callbacks.length; a++ )
					callbacks[a]( el )

		})

	},

	addCallbackForSortStart: function( fieldName, callback ) {

		if ( jQuery.isArray( fieldName ) )
			for ( var i = 0; i < fieldName.length; i++ )
				dkrtbs.addCallbackForSortStart( fieldName[i], callback );

		this._sortStartCallbacks[fieldName] = this._sortStartCallbacks[fieldName] ? this._sortStartCallbacks[fieldName] : []
		this._sortStartCallbacks[fieldName].push( callback )

	},

	sortEnd : function ( el ) {

		// also check child elements
		el.add( el.find( 'div[data-class]' ) ).each( function(i, el) {

			el = jQuery( el )
			var callbacks = dkrtbs._sortEndCallbacks[el.attr( 'data-class') ]

			if ( callbacks )
				for ( var a = 0; a < callbacks.length; a++ )
					callbacks[a]( el )

		})

	},

	addCallbackForSortEnd: function( fieldName, callback ) {

		if ( jQuery.isArray( fieldName ) )
			for ( var i = 0; i < fieldName.length; i++ )
				dkrtbs.addCallbackForSortEnd( fieldName[i], callback );

		this._sortEndCallbacks[fieldName] = this._sortEndCallbacks[fieldName] ? this._sortEndCallbacks[fieldName] : []
		this._sortEndCallbacks[fieldName].push( callback )

	}

}

jQuery(document).ready( function() {

	dkrtbs.init();

});
