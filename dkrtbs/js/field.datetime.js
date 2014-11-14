
/**
 * Date & Time Fields
 */

dkrtbs.addCallbackForClonedField( ['dkrtbs_Date_Field', 'dkrtbs_Time_Field', 'dkrtbs_Date_Timestamp_Field', 'dkrtbs_Datetime_Timestamp_Field' ], function( newT ) {

	// Reinitialize all the datepickers
	newT.find( '.dkrtbs_datepicker' ).each(function () {
		jQuery(this).attr( 'id', '' ).removeClass( 'hasDatepicker' ).removeData( 'datepicker' ).unbind().datepicker();
	});

	// Reinitialize all the timepickers.
	newT.find('.dkrtbs_timepicker' ).each(function () {
		jQuery(this).timePicker({
			startTime: "00:00",
			endTime: "23:30",
			show24Hours: false,
			separator: ':',
			step: 30
		});
	});

} );

dkrtbs.addCallbackForInit( function() {

	// Datepicker
	jQuery('.dkrtbs_datepicker').each(function () {
		jQuery(this).datepicker();
	});
	
	// Wrap date picker in class to narrow the scope of jQuery UI CSS and prevent conflicts
	jQuery("#ui-datepicker-div").wrap('<div class="dkrtbs_element" />');

	// Timepicker
	jQuery('.dkrtbs_timepicker').each(function () {
		jQuery(this).timePicker({
			startTime: "00:00",
			endTime: "23:30",
			show24Hours: false,
			separator: ':',
			step: 30
		});
	} );

});