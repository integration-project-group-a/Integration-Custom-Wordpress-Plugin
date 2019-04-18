<?php

/*
Plugin Name: PMA Custom Plugin
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Custom plugin Groep front-end. Aanmaken database tabellen en custom endpoints voor API
Version: 1.0
Author: Fernando Andrade
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

//include necessary files
require_once plugin_dir_path( __FILE__).'includes/createdb-function.php';
require_once plugin_dir_path( __FILE__).'includes/Visitor-WP-Controller.php';


//function to register custom routes for visitor
function visitor_register_endpoints() {
	$visitor_route = new Visitor_REST_Controller();
	$visitor_route->register_routes();
}

//Do functions '..._install(_data)' when plugin activated
register_activation_hook(__FILE__, 'visitordb_install' );
register_activation_hook( __FILE__, 'visitordb_install_data' );

//Do these functions every time plugin is loaded
//add_action( 'plugins_loaded', 'visitordb_upgrade_check');

//Do these functions to make routes available
add_action('rest_api_init', 'visitor_register_endpoints');