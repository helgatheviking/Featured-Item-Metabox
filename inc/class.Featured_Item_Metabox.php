<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists( 'Featured_Item_Metabox' ) ) :

class Featured_Item_Metabox {

    /**
     * @var type - the post type
     * @since 1.2
     */
	public $type = null;

    /**
     * @var post_obj - the post type's object
     * @since 1.2
     */
    public $type_obj = null;

    /**
     * Class Constructor
     *
     * @param  string $type - the post type
     * @return Featured_Item_Metabox()
     * @since  1.0
     */
    
	public function __construct( $type ){

		$this->type = $type;

		//get the taxonomy object - need to get it after init but before admin_menu
		add_action( 'wp_loaded', array( $this, 'get_post_type_object' ) );

		//Add new taxonomy meta box
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		//Ajax callback for setting as featured
		add_action( 'wp_ajax_featured_items_quickedit', array( $this, 'ajax_callback' ) );

		//add columns to the edit screen
		add_filter( 'admin_init', array( $this, 'add_columns_init' ), 20 );

		//save featured meta
		add_action( 'save_post', array( $this, 'save_meta' ) );

		//add to quick edit - irrelevant for wp 3.4.2
		add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_custom_box' ), 10, 2);

		//add quick edit scripts
 		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) );


	}

	/**
	 * Set up the post type object
	 * need to do this after all custom post types are registered
	 *
	 * @return object type_obj
	 * @since 1.0
	 */
	public function get_post_type_object(){
		$this->type_obj = get_post_type_object( $this->type );
	}

	/**
	 * Add our new customized metabox
	 *
	 * @return void
	 * @since 1.0
	 */
	public function add_meta_box() {
		if( ! is_wp_error( $this->type_obj ) ):
			$label = sprintf( __( 'Featured %s', 'featured-items-metabox' ), $this->type_obj->labels->singular_name );
			add_meta_box( '_featured_metabox', $label, array( $this,'metabox' ), $this->type, 'side', 'high' );
		endif;
	}


	/**
	 * Callback to set up the metabox
	 *
	 * @param  object $post - the post object
	 * @return  print HTML for metabox
	 * @since 1.0
	 */
	public function metabox( $post ) {

		//get current terms
		$featured = ( 'yes' == get_post_meta( $post->ID, '_featured', true ) ) ? 'yes' : 'no';

		?>
		<div id="featured-items">
			<input type="checkbox" name="featured" class="featured-item" id="featured-item" value="1" <?php checked( 'yes', $featured );?> /> <label for="featured-item"><?php _e('Featured', 'featured-items-metabox');?></label>

			<br>
		</div>
			<?php wp_nonce_field( 'featured_nonce', '_featured_nonce' ); ?>


	<?php
	}

	/**
	 * Only ever save a single term
	 *
	 * @param  int $post_id
	 * @return $post_id
	 * @since 1.0
	 */
	public function save_meta ( $post_id ) {

		// make sure we're on a supported post type
		$options = get_option('featured_items_metabox_options', false);
	    $types = isset($options['types']) ? $options['types'] : array();

		if ( ! isset( $_POST['post_type'] ) || ! in_array( $_POST['post_type'], $types ) )
			return;

    	// verify this came from our plugin
	 	if ( ! isset( $_POST['_featured_nonce'] ) || ! wp_verify_nonce( $_POST['_featured_nonce'], 'featured_nonce' ) ) 
	 		return;

	  	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
	  	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
	    	return $post_id;

	  	// Check permissions
	  	if ( 'page' == $_POST['post_type'] ) {
	    	if ( ! current_user_can( 'edit_page', $post_id ) ) 
	    		return;
	  	} else {
	    	if ( ! current_user_can( 'edit_post', $post_id ) ) 
	    		return;
	  	}

	  	// OK, we're authenticated: we need to find and save the data
	  	if ( isset ( $_POST['featured'] ) && 1 == $_POST['featured'] )
	  		update_post_meta( $post_id, '_featured', 'yes' );
	  	else
	  		update_post_meta( $post_id, '_featured', 'no' );
	  	
		return $post_id;
	}


	/**
	 * Feature a product from admin
	 *
	 * @access public
	 * @return void
	 * Props to WooTheme's WooCommerce
	 */
	public function ajax_callback() {

		if ( ! is_admin() )
			die();

	  	if ( ! check_admin_referer('featured-items-metabox')) wp_die( __( 'You have taken too long. Please go back and retry.', 'featured-items-metabox' ) );

	  	// get the post ID
		$post_id = isset( $_GET['featured_id'] ) && (int) $_GET['featured_id'] ? (int) $_GET['featured_id'] : '';

		// get the post type
		$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type']: '';

		if ( ! $post_id || ! $post_type )
			die();

		// Check permissions
	  	if ( 'page' == $post_type ) {
	    	if ( ! current_user_can( 'edit_page', $post_id ) ) wp_die( __( 'You do not have sufficient permissions to access this page.', 'featured-items-metabox' ) );
	  	} else {
	    	if ( ! current_user_can( 'edit_post', $post_id ) ) wp_die( __( 'You do not have sufficient permissions to access this page.', 'featured-items-metabox' ) );
	  	}

	  	$options = get_option('featured_items_metabox_options', false );
		$types = isset($options['types']) ? $options['types'] : array();

		if ( ! in_array( $post_type, $types ) )
			die();

		// since it is 'toggle' get the featured status and set to opposite
		$featured = get_post_meta( $post_id, '_featured', true );

		if ( $featured == 'yes' )
			update_post_meta( $post_id, '_featured', 'no' );
		else
			update_post_meta( $post_id, '_featured', 'yes' );

		// redirect back to where we came from
		wp_safe_redirect( remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer() ) );

		die();
	}



	/**
	 * Add extra columns for radio taxonomies on the edit screen
	 *
	 * @return void
	 * @since 1.0
	 */
	public function add_columns_init() {

		$screen = get_current_screen();

		if ( isset( $screen->base ) && 'edit' != $screen->base ) return;

			//add some hidden data that we'll need for the quickedit
			add_filter( "manage_{$this->type}_posts_columns", array( $this, 'add_column' ) );
			add_action( "manage_{$this->type}_posts_custom_column", array( $this, 'custom_column' ), 99, 2);
			add_filter( "manage_edit-{$this->type}_sortable_columns", array( $this, 'register_sortable' ) );
			add_filter( 'request', array( $this, 'column_orderby' ) );

	}


	/**
	 * Add New Custom Columns
	 *
	 * @param array $columns
	 * @return array
	 * @since 1.0
	 */
	public function add_column( $columns ) {
		$columns['featured'] = __( 'Featured', 'featured-items-metabox' );
		return $columns;
	}

	/**
	 * New Custom Column content
	 *
	 * @param string $column
	 * @param int $post_id
	 * @return print HTML for column
	 * @since 1.0
	 */
	public function custom_column( $column, $post_id ) {
		global $post;

		switch ( $column ) {
			case "featured":

				$ajax_url = add_query_arg( array( 'action' => 'featured_items_quickedit',
									'featured_id' => $post_id,
									'post_type' => $this->type ), admin_url('admin-ajax.php') );

				$url = wp_nonce_url( $ajax_url, 'featured-items-metabox' );

				echo '<a href="' . $url . '" title="'. __( 'Toggle featured', 'featured-items-metabox' ) . '">';
				if ( 'yes' == ( $featured = get_post_meta ( $post_id, '_featured', true ) ) ) {
					echo '<img src="' . plugins_url( 'images/featured.png', __FILE__ ) . '" alt="'. __( 'yes', 'featured-items-metabox' ) . '" height="14" width="14" />';
				} else {
					echo '<img src="' . plugins_url( 'images/featured-off.png', __FILE__ ) . '"" alt="'. __( 'no', 'featured-items-metabox' ) . '" height="14" width="14" />';
				}
				echo '</a>';
			echo '<div id="featured-' . $post_id.'" class="hidden featured-value '. $this->type . '">' . $featured . '</div>';
			break;
		}

	}


	/**
	 * Register the column as sortable
	 *
	 * @param array $columns
	 * @return array
	 * @since 1.0
	 */

	public function register_sortable( $columns ) {
	    $columns['featured'] = 'featured';
	    return $columns;
	}

	/**
	 * Change the sort order of the column
	 *
	 * @param array $vars
	 * @return array
	 * @since 1.0
	 */
	public function column_orderby( $vars ) {
	    if ( is_admin() && isset( $vars['orderby'] ) && 'featured' == $vars['orderby'] ) {
	        $vars = array_merge( $vars, array(
	            'meta_key' => '_featured'
	        ) );
	    }

	    return $vars;
	}


	/**
	 * Quick edit form
	 *
	 * @param string $column_name
	 * @param object $screen
	 * @return print HTML
	 * @since 1.0
	 */
	public function quick_edit_custom_box( $column_name, $screen ) {
		if ( $screen != $this->type || $column_name != 'featured' ) return false;

		global $post; 

	    //needs the same name as metabox nonce
	    wp_nonce_field( 'featured_nonce', '_featured_nonce' );

	    //get current status
		$featured = ( 'yes' == get_post_meta( $post->ID, '_featured', true ) ) ? 'yes' : 'no';

	    ?>

		<fieldset class="inline-edit-col-left inline-edit-categories">
			<div class="inline-edit-col">

		<div id="featured-items">

			<label class="alignleft inline-edit-featured">
					<input type="checkbox" name="featured" class="featured-item" id="featured-item-<?php echo $post->ID;?>" value="1" <?php checked( 'yes', $featured );?> />
					<span class="checkbox-title"><?php _e('Featured Item', 'featured-items-metabox');?></span>
				</label>

			<br>
		</div>


			</div>
		</fieldset>
		<?php
	}

	/**
	 * Quick edit scripts
	 *
	 * @since 1.0
	 */
    public function admin_script(){

    	$screen = get_current_screen();
    	$options = get_option('featured_items_metabox_options', false);

    	if ( $screen->base != "edit" ||  ! isset( $options['types'] ) || ! in_array( $screen->post_type, $options ) ) 
    		return;

      wp_enqueue_script( 'featured-item', plugins_url( 'js/featureditem.js', dirname(__FILE__) ), array( 'jquery' ), null, true );

    }


} //end class - do NOT remove or else
endif;
