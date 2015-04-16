( function( window, $, postboxes, pagenow ) {
	'use strict';
	/* onReady */
	$( function() {
		// Initialize metaboxes
		postboxes.add_postbox_toggles(pagenow);

		// Bind to the checkboxes to hide/show display spans.
		$('.hide-column-tog', '#adv-settings').change( function() {
			var $this = $(this), id = $this.val();
			if ( $this.prop('checked') ) {
				$('#display_'+id).show();
			} else {
				$('#display_'+id).hide();
			}
		} );
	} );
} )( window, window.jQuery, postboxes, pagenow );	