<?php
/*
Plugin Name: Empty Demo Plugin
Text Identifier: empty-demo-plugin
Custom Post Type: None
Plugin URI: 
Description: This plugin does not do anything !
Version: 1.0
Author: Mark D Sutton
Author URI: visual-software.co.uk
License: GPLv2
*/


/*  Copyright 2024  Mark Sutton  (email : mark@visual-software.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



// Call function when plugin is activated
register_activation_hook( __FILE__, 'vsc_plugin_install' );

function vsc_plugin_install() {

    //setup default option values
    
    //save default option values
    
    //there are no options for this plugin
    //update_option( '[pluginname]_options', $hween_options_arr );

}


// Action hook to initialize the plugin
add_action( 'init', 'vsc_plugin_initialise' );

//Initialize the VSC Plugin
function vsc_plugin_initialise() {

	//register a custom post type
	$labels = array(
        'name'               => __( 'Demo', 'vsc-emptydemo-plugin' ),                
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => false,
        'show_in_menu'       => false,
        'query_var'          => true,
        'rewrite'            => true,
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' )
    );

    //nothing to reguister here
	//register_post_type( 'vsc-empty-demo-plugin', $args );

}

// No menus

// No plugin settings page

// No plugin option settings

// Action hook to create the shortcode
add_shortcode( 'vsc_hello', 'vsc_demo_shortcode' );
add_shortcode( 'vsc_html', 'vsc_html_shortcode' );

//create demo shortcode
function vsc_demo_shortcode( $atts, $content = null ) {
    global $post;

    //there are no attributes to extract 
     
	//return the shortcode value to display
    return "Hello World!";
}

//create demo shortcode
function vsc_html_shortcode( $atts, $content = null ) {
    global $post;

    // attributes
    extract( shortcode_atts( array(
        "lang" => 'EN'
    ), $atts ) );
    
    $html = "<table><thead><tr><th>Fruit</th><th>Count</th></thead><tbody>";

    if ($lang=="FR") {
        $html = $html."<tr><td>Pommes</td><td>35</td></tr>"; 
        $html = $html."<tr><td>Poivres</td><td>66</td></tr>";
    }
    else{
        $html = $html."<tr><td>Apples</td><td>35</td></tr>"; 
        $html = $html."<tr><td>Pears</td><td>66</td></tr>";
    }
    $html = $html."</tbody></table><hr/>";
    $html = $html."<button>Do This</button>";

	//return the shortcode value to display
    return "Doing some HTML in - " . $lang ."<br/>".$html;
}