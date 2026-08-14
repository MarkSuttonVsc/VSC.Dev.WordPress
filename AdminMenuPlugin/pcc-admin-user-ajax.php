<?php
/*
    File:           pcc-admin-user-ajax.php
    Description:    AJAX user functions
    Include Status: Required
    Author:         Mark Sutton
    Update:         2024-06-05 Updates for PCP checks
*/

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

//AJAX handler
function find_member_handler()
{
    $current_user = wp_get_current_user();
    if ((array_key_exists("_ajax_nonce", $_POST) && array_key_exists("search_criteria", $_POST)))
    {
        $nonce = sanitize_text_field(wp_unslash($_POST['_ajax_nonce']));
        $search_criteria = sanitize_text_field(wp_unslash($_POST['search_criteria']));
        if (wp_verify_nonce($nonce,"pcc-subs-correction-ajax-call-nonce-key-".$current_user->ID))
        {
            $results =  find_member_options($search_criteria);
            wp_send_json($results);
            return;
        }
    }
    die(); //returns nothing
}

//AJAX handler
function get_member_handler()
{
    $current_user = wp_get_current_user() ;
    
    if ((array_key_exists("_ajax_nonce", $_POST) && array_key_exists("user_id", $_POST)))
    {
        $nonce = sanitize_text_field(wp_unslash($_POST['_ajax_nonce']));
        if (wp_verify_nonce($nonce,"pcc-subs-correction-ajax-call-nonce-key-".$current_user->ID))
        {
            $user_id = sanitize_key($_POST["user_id"]);
            $return = get_user_subs_details($user_id);
            wp_send_json($return);
            return;
        }
    }
    die(); //returns nothing
}

//AJAX handler
function set_member_plan_handler()
{
    $user = wp_get_current_user();
    if (array_key_exists("_ajax_nonce", $_POST)
        && array_key_exists("user_id", $_POST)
        && array_key_exists("old_plan_id", $_POST)
        && array_key_exists("new_plan_id", $_POST)) 
    {
        $ID = sanitize_key(wp_unslash($_POST["user_id"]));
        $old_plan_id = sanitize_key(wp_unslash($_POST["old_plan_id"]));
        $new_plan_id = sanitize_key(wp_unslash($_POST["new_plan_id"]));
        $nonce = sanitize_text_field(wp_unslash($_POST['_ajax_nonce']));
        if (wp_verify_nonce($nonce, "pcc-subs-correction-ajax-call-nonce-key-" . $user->ID)) 
        {
            //update the member's subscription plan
            $result = update_member_plan($ID, $old_plan_id, $new_plan_id);
            $return = get_user_subs_details($ID); //array
            
            //set last terms of array
            $return["result_status"] = $result["Result"];
            $return["SQL"] = $result["SQL"];
            wp_send_json($return);
            return;
        }
    }
    die(); //returns nothing
}

//AJAX handler
function update_discount_ajax_handler()
{
    $user = wp_get_current_user();
    if (array_key_exists("_ajax_nonce", $_POST) && array_key_exists("discount_id", $_POST))
    {
        $discount_id = sanitize_key(wp_unslash($_POST["discount_id"]));
        $nonce = sanitize_text_field(wp_unslash($_POST['_ajax_nonce']));
        if (wp_verify_nonce($nonce, "pcc-subs-correction-ajax-call-nonce-key-" . $user->ID)) {
            if (is_numeric($discount_id)) {
                $msg = "";
                $result = update_discount_dates($discount_id);
                if (!empty($result) > 0) {
                    $msg = "Updated discount dates.";
                } else {
                    $msg = "Data problem!";
                }
                $return = array(
                    "discount_id" => $discount_id,
                    "message" => $msg,
                    "result" => $result
                );
                wp_send_json($return);
            }
        }
    }
    $return_error = array("message"=>"Cannot update - no valid id.");
    wp_send_json($return_error);  
}

//AJAX handler
function update_user_sub_handler()
{
    $user = wp_get_current_user();
    if (array_key_exists("_ajax_nonce", $_POST) && array_key_exists("subs_id", $_POST)) {
        $subs_id = sanitize_key(wp_unslash($_POST["subs_id"]));
        $nonce = sanitize_text_field(wp_unslash($_POST['_ajax_nonce']));
        if (wp_verify_nonce($nonce, "pcc-subs-correction-ajax-call-nonce-key-" . $user->ID)) {
            if (is_numeric($subs_id)) 
            {
                $msg = "";
                $result = update_user_sub_dates($subs_id);
                if (!empty($result) > 0) {
                    $msg = "Updated subscription dates.";
                } else {
                    $msg = "Data problem!";
                }

                $return = array(
                    "subs_id" => $subs_id,
                    "message" => $msg,
                    "result" => $result
                );
                wp_send_json($return);
            }
        }
    }    
    $return_error = array("message"=>"Cannot update - no valid id.");
    wp_send_json($return_error);      
}

//AJAX handler
function refresh_subs_table_handler()
{
    $user = wp_get_current_user() ;
    if (array_key_exists("_ajax_nonce", $_POST)) 
    {
        $nonce = sanitize_text_field(wp_unslash($_POST['_ajax_nonce']));
        if (wp_verify_nonce($nonce, "pcc-subs-date-nonce-field-ajax-call-nonce-key-" . $user->ID)) {
            $results = array(
                "table_html" => get_user_subs_table()
            );
            wp_send_json($results);
        }
    }
    die(); //returns nothing   
}

//AJAX handler
function refresh_discounts_table_handler()
{
    $user = wp_get_current_user() ;
    if (array_key_exists("_ajax_nonce", $_POST)) {
        $nonce = sanitize_text_field(wp_unslash($_POST['_ajax_nonce']));
        if (wp_verify_nonce($nonce, "pcc-quarters-nonce-field-ajax-call-nonce-key-" . $user->ID)) {
            $results = array(
                "table_html" => get_discounts_table()
            );
            wp_send_json($results);
        }
    }
    die(); //returns nothing    
}


//update the start and expiration dates on a specific subscription record
//used by: update_user_sub_handler - AJAX handler (in this file)
//calls SQL updates on start and expiration dates if needed
//returns a new copy of the subscriptions table HTML (if updates were done)
function update_user_sub_dates($subs_id)
{
    $msg="";
    $log="";
    $result = 0;

    $subs_data = get_pms_subscription($subs_id);

    if (true)//count($subs_data) > 0)
    {
        $old_start_date =  $subs_data->start_date ;
        $new_start_date = $old_start_date ;
        $old_expiration_date =  $subs_data->expiration_date;
        $new_expiration_date = $old_expiration_date;

        if (!is_quarter_aligned($old_start_date))
        {
            $log .= "Start date is not quarter aligned.";
            $msg .= "Start date updated.";
            $new_start_date = quarter_align($old_start_date);
            update_user_sub_start_date($subs_id, $new_start_date);
        }

        if (!is_end_of_year_aligned($old_expiration_date))
        {
            $log .= " Expiration date is not year aligned.";
            $msg .= " Expiration date updated.";
            $new_expiration_date = end_of_year_align($old_expiration_date);
            $res2 = update_user_sub_expiry_date($subs_id, $new_expiration_date);            
        }

        $return = array(
            "subs_id"=> $subs_id,
            "old_start_date" => $old_start_date,
            "old_expiration_date" => $old_expiration_date,
            "new_start_date" => $new_start_date,
            "new_expiration_date" => $new_expiration_date,
            "message"=>"$msg",
            "result_count" => $result,
            "table_html" => get_user_subs_table()
        );
        wp_send_json($return); 
    }
    
    else
    {
        $return2 = array(
            "subs_id"=> $subs_id,            
            "message"=>"No good",
            "result_count" => 0
        );
        wp_send_json($return2); 
    }
      
}

//get array of user's main subscription plan
//used by: get_member_handler - AJAX handler (in this file)
//requires: get_user_pms_subscription (pcc-admin-pms-data.php)
//          get_plan_options - HTML for a list (pcc-admin-pms-data.php)
function get_user_subs_details($user_id)
{
    $user =  get_userdata($user_id);             
    $first_name = get_user_meta($user_id, "first_name", true);
    $last_name = get_user_meta($user_id, "last_name", true);
    
    //this only gets the main subs - not the Racing Fee
    $user_subs = get_user_pms_subscription($user_id); 
    
    //get the revised change plan options list
    $options=get_plan_options($user_subs->subscription_title);

    return array(
        "first_name"=>$first_name, 
        "last_name"=>$last_name, 
        "email"=>$user->user_email,
        "plan_title"=>$user_subs->subscription_title,
        "plan_id"=>$user_subs->subscription_plan_id,
        "start_date"=> $user_subs->start_date,
        "expiration_date"=>$user_subs->expiration_date,
        "status" =>$user_subs->status,
        "options" => $options,
        "find_status" => ! empty( $first_name ),
        "result_status" => false,
        "SQL" => ""
    );
}

//HTML options for a list
//uses get_users() function not SQL
//used by: find_member_handler - AJAX handler (in this file)
function find_member_options($find)
{
    $meta_query_args = array(
        
        'relation' => 'OR', //defaults to "AND"
        array(
            'key'     => 'first_name',
            'value'   => $find,
            'compare' => 'LIKE'
        ),
        array(
            'key'     => 'last_name',
            'value'   => $find,
            'compare' => 'LIKE'
        ));

    $users = get_users( array( 
        'meta_query'=> $meta_query_args,        
        'role__in' => array( 'member', 'applicant' ) 
        ));

    $dropdown = '';
    
    foreach ( $users as $user ) 
    {
        $dropdown.= '<option value="'.$user->ID.'">'.$user->display_name.'</option>';
    }

    return array("options"=>$dropdown, "count"=>count($users));
}

?>