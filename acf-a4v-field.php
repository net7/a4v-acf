<?php

/*
Plugin Name: Advanced Custom Fields: FIELD_LABEL
Plugin URI: PLUGIN_URL
Description: SHORT_DESCRIPTION
Version: 1.0.0
Author: AUTHOR_NAME
Author URI: AUTHOR_URL
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

define( 'COLLECTION_ITEM_FIELD_ID',  "a4v_field_id" );
define( 'COLLECTION_ITEM_FIELD_IMAGE',  "a4v_field_image" );
define( 'COLLECTION_ITEM_FIELD_LABEL',  "a4v_field_label" );

// check if class already exists
if( !class_exists('a4v_acf_plugin_a4v_field') ) :

class a4v_acf_plugin_a4v_field {
	
	// vars
	var $settings;
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	void
	*  @return	void
	*/
	
	function __construct() {
		
		// settings
		// - these will be passed into the field class.
		$this->settings = array(
			'version'	=> '1.0.0',
			'url'		=> plugin_dir_url( __FILE__ ),
			'path'		=> plugin_dir_path( __FILE__ )
		);
		
		
		// include field
		add_action('acf/include_field_types', 	array($this, 'include_field')); // v5
		add_action('acf/register_fields', 		array($this, 'include_field')); // v4
	}
	
	
	/*
	*  include_field
	*
	*  This function will include the field type class
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	$version (int) major ACF version. Defaults to false
	*  @return	void
	*/
	
	function include_field( $version = false ) {
		
		// support empty $version
		if( !$version ) $version = 4;
		
		
		// load textdomain
		load_plugin_textdomain( 'a4v_textdomain', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' ); 
		
		
		// include
		include_once('fields/class-a4v-acf-field-A4v-field-v' . $version . '.php');			
		include_once('includes/class-a4v-connector.php');
		include_once('includes/utils.php');
	
	}
	
}


// initialize
new a4v_acf_plugin_a4v_field();


// class_exists check
endif;
	
?>