<?php
/*
Plugin Name: PCC Racing Fleet Report 
Text Identifier: pcc-racing-fleet-report
Custom Post Type: None
Plugin URI:  
Description: A short code to display a report table [racing-fleet-report]
Version: 1.2 
Version Notes: Plugin Checked for WordPress 7.0.3
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

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function report_table_output()
{
    global $wpdb;

    //query not repeated, no need to cache the results, so we can use get_results() directly
    //no need to prepare - no parameters to bind, and no user input
    $results = $wpdb->get_results(
    "SELECT {$wpdb->prefix}users.ID user_id,{$wpdb->prefix}users.user_login,
        v_first_names.meta_value first_name,    v_last_names.meta_value last_name,    v_boat_names.meta_value boat_name,  v_boat_roles.meta_value boat_role,
        v_boat_types.meta_value boat_type, v_sail_ids.meta_value sail_id, {$wpdb->prefix}pms_member_subscriptions.status, {$wpdb->prefix}pms_member_subscriptions.expiration_date
    FROM {$wpdb->prefix}pms_member_subscriptions 
    INNER JOIN {$wpdb->prefix}users ON ({$wpdb->prefix}users.id = {$wpdb->prefix}pms_member_subscriptions.user_id) 
    INNER JOIN {$wpdb->prefix}posts ON ({$wpdb->prefix}posts.id = {$wpdb->prefix}pms_member_subscriptions.subscription_plan_id) 
    LEFT JOIN {$wpdb->prefix}usermeta v_first_names ON (v_first_names.user_id = {$wpdb->prefix}users.id AND v_first_names.meta_key='first_name') 
    LEFT JOIN {$wpdb->prefix}usermeta v_last_names ON (v_last_names.user_id = {$wpdb->prefix}users.id AND v_last_names.meta_key='last_name') 
    LEFT JOIN {$wpdb->prefix}usermeta v_boat_names ON (v_boat_names.user_id = {$wpdb->prefix}users.id AND v_boat_names.meta_key='boat_name') 
    LEFT JOIN {$wpdb->prefix}usermeta v_boat_roles ON (v_boat_roles.user_id = {$wpdb->prefix}users.id AND v_boat_roles.meta_key='boat_role') 
    LEFT JOIN {$wpdb->prefix}usermeta v_boat_types ON (v_boat_types.user_id = {$wpdb->prefix}users.id AND v_boat_types.meta_key='boat_type') 
    LEFT JOIN {$wpdb->prefix}usermeta v_sail_ids ON (v_sail_ids.user_id = {$wpdb->prefix}users.id AND v_sail_ids.meta_key='sail_id') 
    WHERE {$wpdb->prefix}posts.post_type = 'pms-subscription' AND {$wpdb->prefix}posts.post_name='pcc-racing-fee' 
    ORDER BY v_boat_names.meta_value" 
    );

    $content = '';
    $content .= "<table class='pcc-report-table'>";
    $content .= '<thead><tr><th>Boat Name</th><th>Type</th><th>Sail No.</th><th>Name</th><th>Role</th><th>Fee Status</th><th>Expires</th></thead>';

    
    for($i=0; $i< count($results); $i++)
    {
        $content .= '<tr>';
        $content .= '<td>'. esc_html($results[$i]->boat_name) .'</td>';
        $content .= '<td>'. esc_html($results[$i]->boat_type) .'</td>';
        $content .= '<td>'. esc_html($results[$i]->sail_id) .'</td>';
        $content .= '<td>'. esc_html($results[$i]->first_name) .' '. esc_html($results[$i]->last_name) .'</td>';
        $content .= '<td>'. esc_html($results[$i]->boat_role) .'</td>';
        $content .= '<td>'. esc_html($results[$i]->status) .'</td>';
        $content .= '<td>'. esc_html($results[$i]->expiration_date) .'</td>';
        $content .= '</tr>';
    }
    
    $content .= '</tbody>';
    
    $content .= '</table>';
  
    return $content;
}

add_shortcode('pcc-racing-fleet-report', 'report_table_output');

