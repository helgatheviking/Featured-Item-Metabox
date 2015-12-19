(function($) {

/*
 * EDIT POST SCREEN
 *
 */

	$('#the-list').on('click', '.editinline', function(e){

		// reset
		inlineEditPost.revert();

		tag_id = $(this).parents('tr').attr('id');

		checked = ( 'yes' == $('.featured-value', '#' + tag_id ).text() ) ? true : false;

		// get the value and check the correct input
		$( 'input.featured-item', '.quick-edit-row' ).prop( 'checked', checked );

	});


	$('#the-list').on('click', '.featured-toggle', function(e){

		e.preventDefault();

		var $othis = $(this); //cache the link
	
		var $spinner = $(document.createElement('span')).addClass('spinner').css( { 'visibility': 'visible', 'float': 'none', 'margin': '0' } );

		featured_id = $(this).data('featured_id');
		post_type = $(this).data('post_type');
		_wpnonce = $(this).data('nonce');

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'featured_items_quickedit',
				_wpnonce: _wpnonce,
				featured_id: featured_id,
				post_type: post_type,
				doing_ajax: 1
			},
			beforeSend: function( xhr ) {
			    $othis.before($spinner).hide();
			},
			success:function(response) {
		    	if( response == 'yes' ){
					$othis.removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
					$othis.next('.featured-value' ).text('yes');
				} else if ( response == 'no' ){
					$othis.removeClass('dashicons-star-filled').addClass('dashicons-star-empty');
					$othis.next('.featured-value' ).text('no');
				}
		    }
		}).done(function(response) {
			$othis.prev('.spinner').remove();
			$othis.show();
		});

	});

})(jQuery);