<?php
/*
Plugin Name: PCC Subscription Totals Report
Text Identifier: subscription-totals-report
Custom Post Type: None
Plugin URI: 
Description: A short code (subscription-totals-report) to display subscription totals table by status
Version: 1.3 
Version Notes: includes a cancelled column and totals row. Added table class.
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

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function subscription_totals_output()
{
    global $wpdb;
    //query not repeated, no need to cache the results, so we can use get_results() directly
    //no need to prepare - no parameters to bind, and no user input

    $results = $wpdb->get_results(
        "SELECT post_title, 
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending_count, 
            SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) active_count, 
            SUM(CASE WHEN status='canceled' THEN 1 ELSE 0 END) cancelled_count, 
            SUM(CASE WHEN status='expired' THEN 1 ELSE 0 END) expired_count, 
            COUNT(*) total_count 
      FROM {$wpdb->prefix}posts  
      LEFT JOIN {$wpdb->prefix}pms_member_subscriptions 
         ON ({$wpdb->prefix}posts.id = {$wpdb->prefix}pms_member_subscriptions.subscription_plan_id) 
      WHERE {$wpdb->prefix}posts.post_type = 'pms-subscription' 
      GROUP BY post_title");

    $content = "";    
    $content .= "<table class='pcc-report-table'>";
    $content .= '<thead><tr><th>Plan</th><th>Pending</th><th>Expired</th><th>Cancelled</th><th>Active</th><th>Total</th></thead>';

    $totals = array("pending"=>0, "expired"=>0, "cancelled"=>0, "active"=>0, "total"=>0);

    for($i=0; $i< count($results); $i++)
    {
        $totals["pending"] += $results[$i]->pending_count;
        $totals["expired"] += $results[$i]->expired_count;
        $totals["cancelled"] += $results[$i]->cancelled_count;
        $totals["active"] += $results[$i]->active_count;
        $totals["total"] += $results[$i]->total_count;

        $content .= '<tr>';
        $content .= '<td>'.$results[$i]->post_title.'</td>';
        $content .= '<td>'.$results[$i]->pending_count.'</td>';
        $content .= '<td>'.$results[$i]->expired_count.'</td>';
        $content .= '<td>'.$results[$i]->cancelled_count.'</td>';
        $content .= '<td>'.$results[$i]->active_count.'</td>';        
        $content .= '<td>'.$results[$i]->total_count.'</td>';
        $content .= '</tr>';
    }
    $content .= '</tbody>';
    $content .= '<tfoot><tr><th>Total</th>';
    $content .= '<th>'.$totals["pending"].'</th>';
    $content .= '<th>'.$totals["expired"].'</th>';
    $content .= '<th>'.$totals["cancelled"].'</th>';
    $content .= '<th>'.$totals["active"].'</th>';
    $content .= '<th>'.$totals["total"].'</th>';
    $content .= '</tr></tfoot>';
    $content .= '</table>';
  
    return $content;
}

add_shortcode('subscription-totals-report', 'subscription_totals_output');


