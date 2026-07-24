<?php
/*
Plugin Name: Racing Fleet Report Plugin - HTML Table
Text Identifier: racing-fleet-report-plugin
Custom Post Type: None
Plugin URI: 
Description: A short code to display a report table
Version: 1.0 
Version Notes: 
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

function report_table_output()
{
    global $wpdb;
    $pfx = $wpdb->prefix;
    $SQL = "";
    $SQL .= "SELECT " .$pfx."users.ID user_id," .$pfx."users.user_login,";
    $SQL .= "v_first_names.meta_value first_name,    v_last_names.meta_value last_name,    v_boat_names.meta_value boat_name,   v_boat_roles.meta_value boat_role,";
    $SQL .= "v_boat_types.meta_value boat_type, v_sail_ids.meta_value sail_id, ";
    $SQL .= $pfx."pms_member_subscriptions.status,";
    $SQL .= $pfx."pms_member_subscriptions.expiration_date";
    $SQL .= " FROM ".$pfx."pms_member_subscriptions ";
    $SQL .= " INNER JOIN ".$pfx."users ON (".$pfx."users.id = ".$pfx."pms_member_subscriptions.user_id)";
    $SQL .= " INNER JOIN ".$pfx."posts ON (".$pfx."posts.id = ".$pfx."pms_member_subscriptions.subscription_plan_id)";

    $SQL .= " LEFT JOIN ".$pfx."usermeta v_first_names ON (v_first_names.user_id = ".$pfx."users.id AND v_first_names.meta_key='first_name')";
    $SQL .= " LEFT JOIN ".$pfx."usermeta v_last_names ON (v_last_names.user_id = ".$pfx."users.id AND v_last_names.meta_key='last_name')";
    $SQL .= " LEFT JOIN ".$pfx."usermeta v_boat_names ON (v_boat_names.user_id = ".$pfx."users.id AND v_boat_names.meta_key='boat_name')";
    $SQL .= " LEFT JOIN ".$pfx."usermeta v_boat_roles ON (v_boat_roles.user_id = ".$pfx."users.id AND v_boat_roles.meta_key='boat_role')";
    $SQL .= " LEFT JOIN ".$pfx."usermeta v_boat_types ON (v_boat_types.user_id = ".$pfx."users.id AND v_boat_types.meta_key='boat_type')";
    $SQL .= " LEFT JOIN ".$pfx."usermeta v_sail_ids ON (v_sail_ids.user_id = ".$pfx."users.id AND v_sail_ids.meta_key='sail_id')";
    $SQL .= " WHERE ".$pfx."posts.post_type = 'pms-subscription' AND ".$pfx."posts.post_name='pcc-racing-fee'";
    $SQL .= " ORDER BY v_boat_names.meta_value ";
    $results = $wpdb->get_results($SQL);


    $content = '';
    $content .= "<table class='pcc-report-table'>";
    $content .= '<thead><tr><th>Boat Name</th><th>Type</th><th>Sail No.</th><th>Name</th><th>Role</th><th>Fee Status</th><th>Expires</th></thead>';

    
    for($i=0; $i< count($results); $i++)
    {
        $content .= '<tr>';
        $content .= '<td>'.$results[$i]->boat_name.'</td>';
        $content .= '<td>'.$results[$i]->boat_type.'</td>';
        $content .= '<td>'.$results[$i]->sail_id.'</td>';
        $content .= '<td>'.$results[$i]->first_name.' '.$results[$i]->last_name.'</td>';
        $content .= '<td>'.$results[$i]->boat_role.'</td>';
        $content .= '<td>'.$results[$i]->status.'</td>';
        $content .= '<td>'.$results[$i]->expiration_date.'</td>';
        
        $content .= '</tr>';
    }
    
    $content .= '</tbody>';
    
    $content .= '</table>';
  
    return $content;
}

add_shortcode('racing_fleet_report', 'report_table_output');

?>