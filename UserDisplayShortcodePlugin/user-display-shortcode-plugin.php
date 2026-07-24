<?php
/*
Plugin Name: Current User Data Field Display Plugin - String Value
Text Identifier: user-display-shortcode-plugin
Custom Post Type: None
Plugin URI: 
Description: A short code to display current user data [current-user fieldname="first_name"]
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


function get_pms_field($fieldname)
{
    $user_id = get_current_user_id();

    global $wpdb;
    $pfx = $wpdb->prefix;
    $SQL = "";

    $SQL .= "SELECT " . $fieldname;

    $SQL .= " FROM " . $pfx . "pms_member_subscriptions ";
    $SQL .= " INNER JOIN " . $pfx . "posts ON (" . $pfx . "posts.id = " . $pfx . "pms_member_subscriptions.subscription_plan_id)";

    $SQL .= " WHERE " . $pfx . "pms_member_subscriptions.user_id = " . $user_id;

    $obj = [];
    $results = $wpdb->get_results($SQL, ARRAY_N);
    //returns an array (rows, cols)
    foreach ($results as $row) {
        $obj = $row;
        break;
    }
    foreach ($obj as $col_value) {
        return $col_value;
    }
    return "*** No value found ***";

}

function shortcode_output($atts, $content = NULL)
{


    $user_id = get_current_user_id();

    $fieldname = $atts['fieldname'];

    if ($fieldname == "first_name" || $fieldname == "last_name") {
        $meta = get_user_meta(get_current_user_id(), $fieldname, true);
        return esc_html($meta);
    } else {
        if ($fieldname == "status" || $fieldname == "expiration_date" || $fieldname == "post_title") {
            $pms = get_pms_field($fieldname);
            return esc_html($pms);
        } else {
            return "*** Field name is not supported ***" . $fieldname;
        }
    }
}

add_shortcode('current-user-data', 'shortcode_output');

?>