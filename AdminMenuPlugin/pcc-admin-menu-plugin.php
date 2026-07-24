<?php
/*
Plugin Name: PCC Admin Menu Plugin
Text Identifier: pcc-admin-menu-plugin
Custom Post Type: None
Plugin URI: 
Description: Creates a custom admin menu for PCC 
Version: 1.2
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

require 'pcc-admin-user-ajax.php';
require 'pcc-admin-pms-data.php';
require 'pcc-admin-date-functions.php';

function init_pcc_admin_menu()
{
    //add the stylesheet asset
    wp_register_style( 'pcc-admin-styles', plugins_url('/assets/pcc-admin-styles.css', __FILE__));
    wp_register_script( 'pcc-admin-script', plugins_url('/assets/pcc-admin-script.js', __FILE__));

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
    echo '<h2><img src="'.$pennant.'"/> PCC Subscription Administration</h2><hr/>';   
    echo '<p>This is the PCC subscription administration area.</p>';
    echo '<p class="alert alert-danger">Please do not operate these functions unless you know what you are doing!</p>'; 
    echo '<div class="form-row"><a class="pcc-button indent-100" href="'.$admin_corrections.'">Membership Plan Corrections</a>';
    echo '<a class="pcc-button" href="'.$admin_quarters.'">Subscription Quarters Management</a></div>';
}

function pcc_subs_corrections_page()
{
    echo '<h2>PCC Membership Plan Corrections</h2><hr/>';
    echo '<p>You can use this page to change the subscription plan of a member, when the member has been assigned the wrong plan by mistake.</p>';
    echo '<p>Note: this will not change any payment record - you will have to change these using the Paid Member Subscriptions - Payments page.</p>';
    echo '<p>Search by first name or last name.</p>';
    echo '<div class="form-row"><label for="input_search">Find Member:</label><input id="input_search" placeholder="First or Last Name"/> <button id="button_search" class="pcc-button disabled">Go</button></div>';
    echo '<div class="form-row"><label for="list_names">Choose one:</label><select style="width:200px" size="7" id="list_names"></select></div>';
    echo '<div class="form-row"><label for="first_name">First Name:</label><input readonly id="first_name"/></div>';
    echo '<div class="form-row"><label for="last_name">Last Name:</label><input readonly id="last_name"/></div>';
    echo '<div class="form-row"><label for="current_plan">Current Plan:</label><input readonly id="current_plan"/></div>';
    echo '<input type="hidden" id="current_plan_id"/>';
    echo '<div class="form-row"><label for="list_plans">Change to Plan:</label><select style="width:200px" id="list_plans">';    
    echo '</select></div>';
    echo '<div class="form-row"><button class="pcc-button indent-100 disabled" id="button_change_plan" >Make Change</button>';
    echo '<button class="pcc-button" id="button_reset">Reset</button></div>';
    echo '<div class="form-row"><p id="status_bar">Ready...</p></div>';
    $user = wp_get_current_user() ;
    wp_nonce_field("pcc-subs-correction-ajax-call-nonce-key-".$user->ID, "pcc_subs_correction_nonce_field");

}

function pcc_subs_quarters_page()
{
    echo '<h2>PCC Subscription Quarters Management</h2><hr/>';
    echo '<p>The PCC committee has required that subscriptions run from the 1st January every year, for one year.</p>';
    echo '<p>This is not how the Paid Member Subscriptions plugin works - so the following administrator interventions are required:</p>';
    echo '<ul class="red-list" style="list-style-type:disc;margin-left:20px;">';
    echo "<li>Update discount dates so that the start and finish dates are correct relative to today's date.</li>";
    echo '<li>Update the start and finish dates of member subscriptions to align with a calendar year.</li>';    
    echo '</ul>';
    echo '<div class="form-row"><p id="status_bar">Ready...</p></div>';
    echo '<hr/><p></p>';
    echo '<h3>PCC Discount Codes</h3>';
    echo '<div id="discounts_table">';
    echo get_discounts_table();
    echo '</div>';
    echo '<div class="form-row"><a id="button_refresh_discounts" class="pcc-button">Refresh Table</a></div>';
    
    echo '<p></p><hr/>';
    echo '<h3>Subscription Date Alignment</h3>';
    echo '<div id="subs_table">';
    echo get_user_subs_table();
    echo '</div>';
    echo '<div class="form-row"><a id="button_refresh" class="pcc-button">Refresh Table</a></div>';

    $user = wp_get_current_user() ;
    wp_nonce_field("pcc-quarters-nonce-field-ajax-call-nonce-key-".$user->ID, "pcc_quarters_nonce_field");
    wp_nonce_field("pcc-subs-date-nonce-field-ajax-call-nonce-key-".$user->ID, "pcc_subs_date_nonce_field");
}

?>