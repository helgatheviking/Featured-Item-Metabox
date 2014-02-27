#Featured Items Metabox

Contributors: helgatheviking         
Donate link: https://inspirepay.com/pay/helgatheviking         
Tags: metabox, featured           
Requires at least: 3.8            
Tested up to: 3.8          
Stable tag: 1.2.2           
License: GPLv2 or later            
License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description

I found I constantly needed a way for clients to mark a post as something they wanted to feature and I've never found sticky posts particularly inuitive and the UI is pretty hidden for new users.  The simplest solution was a checkbox in prominently located metabox.

Please note that this plugin, by itself, will not change how your posts are displayed.  It just gives the UI to users and a meta key to theme developers to query for.

## Installation

1. Upload the `plugin` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the plugin's settings and select which post types for which you'd like to show the featured metabox

## Usage

This plugin simply adds a `_featured` meta key to every post with a value of `yes` for featured items and `no` for everything else.  Actual display of the featured items is entirely up to the theme developer, but an example ( place in your template where you'd like to display a list of featured "Portfolios") might be as follows:

```
// params for our query
$args = array(
	'post_type' => 'portfolio',
   'posts_per_page'  => 5,
   'meta_key'        => '_featured',
   'meta_value'      => 'yes'
);

// The Query
$featured_portfolios = new WP_Query( $args );

// The Loop
if ( $featured_portfolios ) :

	echo '<ul class="featured">';

	while ( $featured_portfolios->have_posts() ) :
		$featured_portfolios->the_post();
		echo '<li>' . get_the_title() . '</li>';
	endwhile;

	echo '</ul>';

else :

	echo 'No featured portfolios found.';

endif;

/* Restore original Post Data
 * NB: Because we are using new WP_Query we aren't stomping on the
 * original $wp_query and it does not need to be reset.
*/
wp_reset_postdata();
```

Multiple queries per page load can slow down your site so it is worthwhile to take advantage of the [Transients API](http://codex.wordpress.org/Transients_API), so an alternate usage would be:

```
// Get any existing copy of our transient data
if ( false === ( $featured_portfolios = get_transient( 'featured_portfolios' ) ) ) {
    // It wasn't there, so regenerate the data and save the transient

   // params for our query
	$args = array(
		'post_type' => 'portfolio',
	   'posts_per_page'  => 5,
	   'meta_key'        => '_featured',
	   'meta_value'      => 'yes'
	);

	// The Query
	$featured_portfolios = new WP_Query( $args );

	// store the transient
	set_transient( 'featured_portfolios', $featured_portfolios );

}

// Use the data like you would have normally...

// The Loop
if ( $featured_portfolios ) :

	echo '<ul class="featured">';

	while ( $featured_portfolios->have_posts() ) :
		$featured_portfolios->the_post();
		echo '<li>' . get_the_title() . '</li>';
	endwhile;

	echo '</ul>';

else :

	echo 'No featured portfolios found.';

endif;

/* Restore original Post Data
 * NB: Because we are using new WP_Query we aren't stomping on the
 * original $wp_query and it does not need to be reset.
*/
wp_reset_postdata();
```

Then to ensure that your featured posts list is updated, add a function to your theme's functions.php to delete the transient when a portfolio (in this example) post type is saved.

```
// Create a function to delete our transient when a portfolio post is saved
function save_post_delete_featured_transient( $post_id ) {
   if ( 'portfolio' == get_post_type( $post_id ) )
   	delete_transient( 'featured_portfolios' );
}
// Add the function to the save_post hook so it runs when posts are saved
add_action( 'save_post', 'save_post_delete_featured_transient' );
```

Simple queries should only need the `meta_key` and `meta_value` parameters, but if you need something more advanced then you might want to read about how to use the more [complex Meta Query parameters](http://scribu.net/wordpress/advanced-metadata-queries.html).

## Support

Support is handled in the [WordPress forums](http://wordpress.org/support/plugin/featured-item-metabox).  Please note that support is limited and does not cover any custom implementation of the plugin. 

Please report any bugs, errors, warnings, code problems at [Github](https://github.com/helgatheviking/Featured-Item-Metabox/issues)
