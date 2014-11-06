jQuery( document ).ready( function() {

	jQuery( document ).on( 'click', '.dkrtbs-file-upload', function(e) {

		e.preventDefault();

		var link = jQuery( this );
		var container = jQuery( this ).parent();

		var frameArgs = {
			multiple: false,
			title: 'Select File',
		}

		library = container.attr( 'data-type' ).split(',');
		if ( library.length > 0 )
			frameArgs.library = { type: library }

		var dkrtbs_Frame = wp.media( frameArgs );

		dkrtbs_Frame.on( 'select', function() {

			var selection = dkrtbs_Frame.state().get('selection'),
				model = selection.first(),
				fileHolder = container.find( '.dkrtbs-file-holder' );

			jQuery( container ).find( '.dkrtbs-file-upload-input' ).val( model.id );

			link.hide(); // Hide 'add media' button

			dkrtbs_Frame.close();

			fileHolder.html( '' );
			fileHolder.show();
			fileHolder.siblings( '.dkrtbs-remove-file' ).show();

			var fieldType = container.closest( '.field-item' ).attr( 'data-class' );

			if ( 'dkrtbs_Image_Field' === fieldType ) {

				var data = {
					action: 'dkrtbs_request_image',
					id:     model.attributes.id,
					width:  container.width(),
					height: container.height(),
					crop:   fileHolder.attr('data-crop'),
					nonce:  link.attr( 'data-nonce' )
				}

				fileHolder.addClass( 'dkrtbs-loading' );

				jQuery.post( ajaxurl, data, function( src ) {
					// Insert image
					jQuery( '<img />', { src: src } ).prependTo( fileHolder );
					fileHolder.removeClass( 'dkrtbs-loading' );
				}).fail( function() {
					// Fallback - insert full size image.
					jQuery( '<img />', { src: model.attributes.url } ).prependTo( fileHolder );
					fileHolder.removeClass( 'dkrtbs-loading' );
				});

			} else {

				jQuery( '<img />', { src: model.attributes.icon } ).prependTo( fileHolder );
				fileHolder.append( jQuery('<div class="dkrtbs-file-name" />').html( '<strong>' + model.attributes.filename + '</strong>' ) );

			}

		});

		dkrtbs_Frame.open();

	} );

	jQuery( document ).on( 'click', '.dkrtbs-remove-file', function(e) {

		e.preventDefault();

		var container = jQuery( this ).parent().parent();

		container.find( '.dkrtbs-file-holder' ).html( '' ).hide();
		container.find( '.dkrtbs-file-upload-input' ).val( '' );
		container.find( '.dkrtbs-file-upload' ).show().css( 'display', 'inline-block' );
		container.find( '.dkrtbs-remove-file' ).hide();

	} );

	/**
	 * Recalculate the dimensions of the file upload field.
	 * It should never be larger than the available width.
	 * It should maintain the aspect ratio of the original field.
	 * It should recalculate when resized.
	 * @return {[type]} [description]
	 */
	var recalculateFileFieldSize = function() {

		jQuery( '.dkrtbs-file-wrap' ).each( function() {

			var el        = jQuery(this),
				container = el.closest( '.postbox' ),
				width     = container.width() - 12 - 10 - 10,
				ratio     =  el.height() / el.width();

			if ( el.attr( 'data-original-width' ) )
				el.width( el.attr( 'data-original-width' ) );
			else
				el.attr( 'data-original-width', el.width() );

			if ( el.attr( 'data-original-height' ) )
				el.height( el.attr( 'data-original-height' ) );
			else
				el.attr( 'data-original-height', el.height() );

			if ( el.width() > width ) {
				el.width( width );
				el.find( '.dkrtbs-file-wrap-placeholder' ).width( width - 8 );
				el.height( width * ratio );
				el.css( 'line-height', ( width * ratio ) + 'px' );
				el.find( '.dkrtbs-file-wrap-placeholder' ).height( ( width * ratio ) - 8 );
			}


		} );
			}

	recalculateFileFieldSize();
	jQuery(window).resize( recalculateFileFieldSize );

} );