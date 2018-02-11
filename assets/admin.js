/**
 * Plugin front end scripts
 *
 * @package Vimeo_LLMS
 * @version 1.0.0
 */
jQuery( function ( $ ) {

	var
		$form = $( '#vimeo-llms-upload-form' ),
		$input = $( '#vimeo-llms-file-input' );

	$input.change( function () {
		if ( window.FileReader && window.Blob ) {
			if( $input[0].files ) {
				var file = $input[0].files[0];
				if ( file.type.indexOf( 'video' ) === 0 ) {
					$form.addClass( 'uploading' ).submit();
				} else {
					$form.addClass( 'file-error' );
				}
			}
		} else {
			$form.addClass( 'uploading' ).submit();
		}
	} );
} );