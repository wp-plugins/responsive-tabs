/**
 * ColorPickers
 */

dkrtbs.addCallbackForInit( function() {

	// Colorpicker
	jQuery('input:text.dkrtbs_colorpicker').wpColorPicker();

} );

dkrtbs.addCallbackForClonedField( 'dkrtbs_Color_Picker', function( newT ) {

	// Reinitialize colorpickers
    newT.find('.wp-color-result').remove();
	newT.find('input:text.dkrtbs_colorpicker').wpColorPicker();

} );