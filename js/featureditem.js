(function($) {

/*
 * EDIT POST SCREEN
 *
 */

	$('#the-list').on('click', '.editinline', function(){

		// reset
		inlineEditPost.revert();

		tag_id = $(this).parents('tr').attr('id');

		checked = ( 'yes' == $('.featured-value', '#' + tag_id ).text() ) ? true : false;

		// get the value and check the correct input
		$( 'input.featured-item', '.quick-edit-row' ).prop( 'checked', checked );

	});




})(jQuery);