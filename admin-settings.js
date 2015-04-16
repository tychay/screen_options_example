( function( window, $, undefined ) {
	'use strict';
	/* onReady */
	$( function() {
		// Initialize metaboxes
		window.postboxes.add_postbox_toggles(window.pagenow);
		// postboxes:defined in postbox.js
		// pagenow: auto localized into all wp-admin pages ;-)

		// Bind to the checkboxes to hide/show display spans.
		$('.hide-column-tog', '#adv-settings').change( function() {
			var $this = $(this), id = $this.val();
			if ( $this.prop('checked') ) {
				$('#display_'+id).show();
			} else {
				$('#display_'+id).hide();
			}
		} );

		//<input class="screen-bgcolor" name="scroptex_bgcolor" id="scroptex_bgcolor" value="transparent">
		$('#scroptex_bgcolor', '#adv-settings').change( function() {
			var $this = $(this), bgcolor=$this.val();
			// change the display
			$('#post-body-content').css('background-color', bgcolor);
			// update server:
			$.post(
				// by posting elsewhere we could exploit a set_screen_options side effect (won't work quite ajaxian because of the redirect)
				window.ajaxurl, // ajaxurl: auto localized into all wp-admin pages ;-)
				{
					screenoptionnonce          : $('#screenoptionnonce').val(),
					action                     : 'scroptex_set_value',
					'wp_screen_options[option]': this.id,
					'wp_screen_options[value]' : bgcolor
				}/*, function(the_data) { //uncomment for debugging
					console.log(the_data);
				}/* */
			);	

		} );
	} );
} )( window, window.jQuery );	