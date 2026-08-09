<?php
/*
Plugin Name: VSC User Data Display 
Text Identifier: user-data-display-shortcode
Custom Post Type: None
Plugin URI: 
Description: A short code to display the string value of a given field for the current user data. E.g. [current-user-data fieldname="first_name"]
Version: 1.3  
Version Notes: Plugin Checked for WordPress 6.3.1
Author: Mark D Sutton
Author URI: https://visual-software.co.uk
License: GPLv2
Licence URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/*  Copyright 2026 Mark Sutton  (email : mark@visual-software.co.uk)

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

add_shortcode('current-user-data', 'shortcode_output');

function get_pms_field($fieldname, $user_id)
{
    global $wpdb;
    $obj = [];
    /*this custom query has been formatted as one line to pass PCP checks*/
    $results = $wpdb->wp_cache_get($wpdb->prepare("SELECT %s FROM {$wpdb->prefix}pms_member_subscriptions INNER JOIN {$wpdb->prefix}posts ON ({$wpdb->prefix}posts.id = {$wpdb->prefix}pms_member_subscriptions.subscription_plan_id) WHERE {$wpdb->prefix}pms_member_subscriptions.user_id = %s", $fieldname, $user_id), ARRAY_N);
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
    if ($user_id == 0) {
        return "*** User is not signed in ***";
    }

    $fieldname = $atts['fieldname'];
    if ($fieldname == "") {
        return "*** No field name specified ***";
    }

    if ($fieldname == "first_name" || $fieldname == "last_name" || $fieldname == "boat_name") {
        $meta = get_user_meta($user_id, $fieldname, true);
        if (!is_array($meta)) {
            return esc_html($meta);
        } else {
            $comma_list = implode(', ', $meta);
            return $comma_list;
        }
    } else {
        if ($fieldname == "status" || $fieldname == "expiration_date" || $fieldname == "post_title") {
            $pms = get_pms_field($fieldname, $user_id);
            if ($fieldname == "expiration_date") {
                //format date
                $datetime = date_create($pms);
                return date_format($datetime, "d M Y");
            }
            return esc_html($pms);
        } else {
            return "*** Field name is not supported ***" . $fieldname;
        }
    }
}



//no closing tag in plugin file!