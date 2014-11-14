var dkrtbsSelectInit = function() {

	jQuery( '.dkrtbs_select' ).each( function() {

		var el = jQuery(this);
 		var fieldID = el.attr( 'data-field-id'); // JS Friendly ID

 		// If fieldID is set
 		// If fieldID options exist
 		// If Element is not hidden template field.
 		// If elemnt has not already been initialized.
 		if ( fieldID && fieldID in window.dkrtbs_select_fields && el.is( ':visible' ) && ! el.hasClass( 'select2-added' ) ) {

 			// Get options for this field.
 			options = window.dkrtbs_select_fields[fieldID];

			el.addClass( 'select2-added' ).select2( options );

 		}

	})

};

// Hook this in for all the required fields.
dkrtbs.addCallbackForInit( dkrtbsSelectInit );
dkrtbs.addCallbackForClonedField( 'dkrtbs_Select', dkrtbsSelectInit );
dkrtbs.addCallbackForClonedField( 'dkrtbs_Post_Select', dkrtbsSelectInit );
dkrtbs.addCallbackForClonedField( 'dkrtbs_Taxonomy', dkrtbsSelectInit );
