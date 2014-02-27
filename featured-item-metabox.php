<?php
/*
Plugin Name: Featured Item Metabox
Plugin URI: http://wordpress.org/extend/plugins/featured-item-metabox/
Description: Quickly add a metabox to any post type for marking a post as featured.
Version: 1.2.1
Author: Kathy Darling
Author URI: http://www.kathyisawesome.com
License: GPL2

    Copyright 2013  Kathy Darling  (email: kathy.darling@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! class_exists( 'Featured_Item_Metabox_Plugin' ) ) :

  final class Featured_Item_Metabox_Plugin {

    /**
     * @var WooCommerce The single instance of the class
     * @since 1.2
     */
    protected static $_instance = null;

    /**
     * Main Featured_Item_Metabox_Plugin Instance
     *
     * Ensures only one instance of Featured_Item_Metabox_Plugin is loaded or can be loaded.
     *
     * @since 1.2
     * @static
     * @see Featured_Item_Metabox()
     * @return Featured_Item_Metabox_Plugin - Main instance
     */
    public static function instance() {
      if ( is_null( self::$_instance ) ) {
        self::$_instance = new self();
      }
      return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.2
     */
    public function __clone() {
      _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '2.1' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.2
     */
    public function __wakeup() {
      _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '2.1' );
    }


    /**
     * Class Constructor
     * 
     * @return void
     * @since  1.0
     */

  	public function __construct(){

	    // Include required files
	    include_once( 'inc/class.Featured_Item_Metabox.php' );

	    // Set-up Action and Filter Hooks
	    register_uninstall_hook( __FILE__, array( __CLASS__,'delete_plugin_options' ) );

	    //load plugin text domain for translations
	    add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

      //create a class property for each taxonomy that we are converting to radio buttons
      //for example: $this->categories
      $options = get_option('featured_items_metabox_options', false );

      if( isset( $options['types'] ) ) foreach( $options['types'] as $type ) {
         $this->{$type} = new Featured_Item_Metabox( $type );
      }

	    //register settings
	    add_action( 'admin_init', array( $this,'admin_init' ) );

	    //add plugin options page
	    add_action( 'admin_menu', array( $this,'add_options_page' ) );

	    //add settings link to plugins page
	    add_filter( 'plugin_action_links', array( $this,'add_action_links' ), 10, 2 );

    }


  /**
   * Uninstall Hook - possibily delete plugin options
   * @return void
   * @since  1.0
   */

  public function delete_plugin_options() {
    $options = get_option( 'Featured_Items', true );
    if( isset( $options['delete'] ) && $options['delete'] ) delete_option( 'featured_items_metabox_options' );
  }

  /**
   * Make Plugin translation ready
   * @return void
   * @since  1.0
   */

  public function load_text_domain() {
      load_plugin_textdomain( 'featured-items-metabox', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

  /**
   * White-list our plugin's options
   * @return void
   * @since  1.0
   */
  
  public function admin_init(){
    register_setting( 'featured_items_metabox_options', 'featured_items_metabox_options', array( $this,'validate_options' ) );
  }

  /**
   * Add options page
   * @return void
   * @since  1.0
   */

  public function add_options_page() {
    add_options_page(__( 'Featured Items Metabox Options Page', 'featured-items-metabox' ), __( 'Featured Items Metabox', 'featured-items-metabox' ), 'manage_options', 'featured-items-metabox', array( $this,'render_form' ) );
  }


  /**
   * Render the Plugin options form
   * @return void
   * @since  1.0
   */

  public function render_form(){
    include( 'inc/plugin-options.php' );
  }

  /**
   * Sanitize and validate input
   * @param  array $input
   * @return array
   * @since  1.0
   */

  public function validate_options( $input ){

    $clean = array();

    //probably overkill, but make sure that the post type exists
    $types = get_post_types( false, 'objects' );

    if( isset( $input['types'] ) ) foreach ( $input['types'] as $type ){
    	if( array_key_exists( $type, $types ) ) $clean['types'][] = $type;
    }

    $clean['delete'] =  isset( $input['delete'] ) && $input['delete'] ? 1 : 0 ;  //checkbox

    return $clean;
  }

  /**
   * Display a Settings link on the main Plugins page
   * @param  array $links
   * @param  string $file
   * @return array
   * @since  1.0
   */

  public function add_action_links( $links, $file ) {

    if ( $file == plugin_basename( __FILE__ ) ) {
      $plugin_link = '<a href="'.admin_url( 'options-general.php?page=featured-items-metabox' ) . '">' . __( 'Settings' ) . '</a>';
      // make the 'Settings' link appear first
      array_unshift( $links, $plugin_link );
    }

    return $links;
  }

} // end class
endif;


/**
 * Returns the main instance of Featured_Item_Metabox_Plugin to prevent the need to use globals.
 *
 * @since  1.2
 * @return Featured_Item_Metabox
 */
function Featured_Item_Metabox() {
  return Featured_Item_Metabox_Plugin::instance();
}

// Global for backwards compatibility.
$GLOBALS['Featured_Items'] = Featured_Item_Metabox();
