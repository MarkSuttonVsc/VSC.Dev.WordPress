<?php
/*
Plugin Name: PCC Admin Menu 
Text Identifier: pcc-admin-menu
Custom Post Type: None
Plugin URI: 
Description: Creates a custom admin menu for PCC 
Version: 1.3
Version Notes: Updated to pass PCP checks
Requires at least: 7.0.2
Requires PHP: 8.0
Author: Mark D Sutton
Author URI: https://visual-software.co.uk
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

// Updates:       2024-06-05 MDS - Added wp_kses_post() to echo statements to prevent XSS vulnerabilities.

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

require 'pcc-admin-user-ajax.php';
require 'pcc-admin-pms-data.php';
require 'pcc-admin-date-functions.php';

function init_pcc_admin_menu()
{
    //TO DO add the assets (with dynamic file modification time to prevent caching issues)

    wp_register_style( 'pcc-admin-styles', plugins_url('/assets/pcc-admin-styles.css', __FILE__),[],'1.3.99');
    wp_register_script( 'pcc-admin-script', plugins_url('/assets/pcc-admin-script.js', __FILE__), [], '1.3.99', array('in_footer' => true));

}
function enqueue_pcc_admin_styles()
{
	wp_enqueue_style( 'pcc-admin-styles' );
}

function enqueue_pcc_admin_script()
{
	wp_enqueue_script( 'pcc-admin-script' );
}

add_action('init', 'init_pcc_admin_menu');
add_action('admin_enqueue_scripts', 'enqueue_pcc_admin_styles');
add_action('admin_enqueue_scripts', 'enqueue_pcc_admin_script');

add_action('admin_menu', 'create_pcc_admin_menu');

add_action( 'wp_ajax_set_member_plan_ajax', 'set_member_plan_handler' );
add_action( 'wp_ajax_get_member_ajax', 'get_member_handler' );
add_action( 'wp_ajax_search_members_ajax', 'find_member_handler' );

add_action( 'wp_ajax_update_discount_ajax', 'update_discount_ajax_handler');
add_action( 'wp_ajax_update_user_sub_ajax','update_user_sub_handler');

add_action( 'wp_ajax_refresh_discounts_table_ajax', 'refresh_discounts_table_handler');
add_action( 'wp_ajax_refresh_subs_table_ajax', 'refresh_subs_table_handler');

function create_pcc_admin_menu()
{
    add_menu_page('PCC Administration', 
        'PCC Admin', 
        'manage_options', 
        'pcc-admin-main-menu',
        'pcc_subs_admin_page');
        
    add_submenu_page(
        'pcc-admin-main-menu',
        'Membership Plan Corrections', 'Corrections',
        'manage_options',
        'pcc-subs-plan-corrections',
        'pcc_subs_corrections_page'
    );
    add_submenu_page(
        'pcc-admin-main-menu',
        'Subscription Quarters', 'Quarters',
        'manage_options',
        'pcc-subs-quarters',
        'pcc_subs_quarters_page'
    );
}

function pcc_subs_admin_page()
{
    $pennant = plugins_url('/images/pennant.png', __FILE__);

    $admin_corrections =  admin_url('admin.php?page=pcc-subs-plan-corrections');
    $admin_quarters =  admin_url('admin.php?page=pcc-subs-quarters');
    $content = '<h2><img src="'.$pennant.'"/> PCC Subscription Administration</h2><hr/>';   
    $content .= '<p>This is the PCC subscription administration area.</p>';
    $content .= '<p class="alert alert-danger">Please do not operate these functions unless you know what you are doing!</p>'; 
    $content .= '<div class="form-row"><a class="pcc-button indent-100" href="'.$admin_corrections.'">Membership Plan Corrections</a>';
    $content .= '<a class="pcc-button" href="'.$admin_quarters.'">Subscription Quarters Management</a></div>';
    echo wp_kses_post($content);
}

function pcc_subs_corrections_page()
{
    $content = '<h2>Membership Plan Corrections</h2><hr/>';
    $content .= '<p>This page allows you to correct the membership plan for a member.</p>';
    $content .= '<p>You can use this page to change the subscription plan of a member, when the member has been assigned the wrong plan by mistake.</p>';
    $content .= '<p>Note: this will not change any payment record - you will have to change these using the Paid Member Subscriptions - Payments page.</p>';
    $content .= '<p>Search by first name or last name.</p>';
    $content .= '<div class="form-row"><label for="input_search">Find Member:</label><input id="input_search" placeholder="First or Last Name"/> <button id="button_search" class="pcc-button disabled">Go</button></div>';
    $content .= '<div class="form-row"><label for="list_names">Choose one:</label><select style="width:200px" size="7" id="list_names"></select></div>';
    $content .= '<div class="form-row"><label for="first_name">First Name:</label><input readonly id="first_name"/></div>';
    $content .= '<div class="form-row"><label for="last_name">Last Name:</label><input readonly id="last_name"/></div>';
    $content .= '<div class="form-row"><label for="current_plan">Current Plan:</label><input readonly id="current_plan"/></div>';
    $content .= '<input type="hidden" id="current_plan_id"/>';
    $content .= '<div class="form-row"><label for="list_plans">Change to Plan:</label><select style="width:200px" id="list_plans">';    
    $content .= '</select></div>';
    $content .= '<div class="form-row"><button class="pcc-button indent-100 disabled" id="button_change_plan" >Make Change</button>';
    $content .= '<button class="pcc-button" id="button_reset">Reset</button></div>';
    $content .= '<div class="form-row"><p id="status_bar">Ready...</p></div>';
    echo wp_kses_post($content);

    $user = wp_get_current_user() ;
    wp_nonce_field("pcc-subs-correction-ajax-call-nonce-key-".$user->ID, "pcc_subs_correction_nonce_field");
    
    
}

function pcc_subs_quarters_page()
{
    $content = '<h2>PCC Subscription Quarters Management</h2><hr/>';
    $content .= '<p>The PCC committee has required that subscriptions run from the 1st January every year, for one year.</p>';
    $content .= '<p>This is not how the Paid Member Subscriptions plugin works - so the following administrator interventions are required:</p>';    
    $content .= '<ul class="red-list" style="list-style-type:disc;margin-left:20px;">';
    $content .= "<li>Update discount dates so that the start and finish dates are correct relative to today's date.</li>";
    $content .= '<li>Update the start and finish dates of member subscriptions to align with a calendar year.</li>';    
    $content .= '</ul>';
    $content .= '<div class="form-row"><p id="status_bar">Ready...</p></div>';
    $content .= '<hr/><p></p>';
    $content .= '<h3>PCC Discount Codes</h3>';
    $content .= '<div id="discounts_table">';
    $content .= get_discounts_table();
    $content .= '</div>';
    $content .= '<div class="form-row"><a id="button_refresh_discounts" class="pcc-button">Refresh Table</a></div>';
    
    $content .= '<p></p><hr/>';
    $content .= '<h3>Subscription Date Alignment</h3>';
    $content .= '<div id="subs_table">';
    $content .= get_user_subs_table();
    $content .= '</div>';
    $content .= '<div class="form-row"><a id="button_refresh" class="pcc-button">Refresh Table</a></div>';
    echo wp_kses_post($content);
    $user = wp_get_current_user() ;
    wp_nonce_field("pcc-quarters-nonce-field-ajax-call-nonce-key-".$user->ID, "pcc_quarters_nonce_field");
    wp_nonce_field("pcc-subs-date-nonce-field-ajax-call-nonce-key-".$user->ID, "pcc_subs_date_nonce_field");
    
}

