<?php
/*
Plugin Name: Featured Item Metabox
Plugin URI: http://wordpress.org/extend/plugins/featured-item-metabox/
Description: Add featured meta to any post type
Version: 1.0.1
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


if ( ! class_exists( "Featured_Items" ) ) :

  class Featured_Items {

  	function __construct(){

	    // Include required files
	    include_once( 'inc/class.Featured_Items_Metabox.php' );

	    // Set-up Action and Filter Hooks
	    register_uninstall_hook( __FILE__, array( __CLASS__,'delete_plugin_options' ) );

	    //load plugin text domain for translations
	    add_action( 'plugins_loaded', array( $this,'load_text_domain' ) );

      //create a class property for each taxonomy that we are converting to radio buttons
      //for example: $this->categories
      $options = get_option('featured_items_metabox_options', false );

      if( isset( $options['types'] ) ) foreach( $options['types'] as $type ) {
         $this->{$type} = new Featured_Items_Metabox( $type );
      }

	    //register settings
	    add_action( 'admin_init', array( $this,'admin_init' ) );

	    //add plugin options page
	    add_action( 'admin_menu', array( $this,'add_options_page' ) );

	    //add settings link to plugins page
	    add_filter( 'plugin_action_links', array( $this,'add_action_links' ), 10, 2 );

    }


  // --------------------------------------------------------------------------------------
  // CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__,  array($this,'delete_plugin_options'))
  // --------------------------------------------------------------------------------------

  // Delete options table entries ONLY when plugin deactivated AND deleted
  public static function delete_plugin_options() {
    $options = get_option( 'Featured_Items', true );
    if( isset( $options['delete'] ) && $options['delete'] ) delete_option( 'featured_items_metabox_options' );
  }

  // ------------------------------------------------------------------------------
  // CALLBACK FUNCTION FOR: add_action('plugins_loaded', array($this,'load_text_domain' ))
  // ------------------------------------------------------------------------------

    function load_text_domain() {
      load_plugin_textdomain( "featured-items-metabox", false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

  // ------------------------------------------------------------------------------
  // CALLBACK FUNCTION FOR: add_action('admin_init', 'admin_init' )
  // ------------------------------------------------------------------------------

  // Init plugin options to white list our options
  function admin_init(){
    register_setting( 'featured_items_metabox_options', 'featured_items_metabox_options', array( $this,'validate_options' ) );
  }


  // ------------------------------------------------------------------------------
  // CALLBACK FUNCTION FOR: add_action('admin_menu', 'add_options_page');
  // ------------------------------------------------------------------------------

  // Add menu page
  function add_options_page() {
    add_options_page(__( 'Featured Items Metabox Options Page',"featured-items-metabox" ), __( 'Featured Items Metabox', "featured-items-metabox" ), 'manage_options', 'featured-items-metabox', array( $this,'render_form' ) );
  }


  // ------------------------------------------------------------------------------
  // CALLBACK FUNCTION SPECIFIED IN: add_options_page()
  // ------------------------------------------------------------------------------

  // Render the Plugin options form
  function render_form(){
    include( 'inc/plugin-options.php' );
  }

  // Sanitize and validate input. Accepts an array, return a sanitized array.
  function validate_options( $input ){

    $clean = array();

    //probably overkill, but make sure that the post type exists
    $types = get_post_types( false, 'objects' );

    if( isset( $input['types'] ) ) foreach ( $input['types'] as $type ){
    	if( array_key_exists( $type, $types ) ) $clean['types'][] = $type;
    }

    $clean['delete'] =  isset( $input['delete'] ) && $input['delete'] ? 1 : 0 ;  //checkbox

    return $clean;
  }


  // Display a Settings link on the main Plugins page
  function add_action_links( $links, $file ) {

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
* Launch the whole plugin
*/
global $Featured_Items;
$Featured_Items = new Featured_Items();