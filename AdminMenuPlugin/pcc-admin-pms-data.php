<?php
/*
    File:           pcc-admin-pms-data.php
    Description:    Database (SQL) read/write functions for PMS data in the PCC website
    Include Status: Required
    Author:         Mark Sutton
*/


//


//update a specific subscription plan (old_plan_id) for a given user (user_id)
function update_member_plan($user_id, $old_plan_id, $new_plan_id)
{
    global $wpdb;
    $pfx = $wpdb->prefix;
    $SQL = "";
    $SQL .= "UPDATE ".$pfx."pms_member_subscriptions ";
    $SQL .= " SET subscription_plan_id = ".$new_plan_id ;
    $SQL .= " WHERE user_id = ".$user_id;
    $SQL .= " AND subscription_plan_id = ".$old_plan_id;
   
    return array("SQL"=>$SQL, "Result"=>$wpdb->query($SQL));
}

//update start date for a specific user subscription record (subs_id)
function update_user_sub_start_date($subs_id, $start_date)
{
    global $wpdb;
    $pfx = $wpdb->prefix;
    $SQL = "";
    $SQL .= "UPDATE ".$pfx."pms_member_subscriptions ";
    $SQL .= " SET start_date = '".$start_date."'";
    $SQL .= " WHERE id = ".$subs_id;
    return array("SQL"=>$SQL, "Result"=>$wpdb->query($SQL));
}

//update the expiration date for a specific user subscription record (subs_id)
function update_user_sub_expiry_date($subs_id, $expiration_date)
{
    global $wpdb;
    $pfx = $wpdb->prefix;
    $SQL = "";
    $SQL .= "UPDATE ".$pfx."pms_member_subscriptions ";
    $SQL .= " SET expiration_date = '".$expiration_date."'" ;
    $SQL .= " WHERE id = ".$subs_id;
    return array("SQL"=>$SQL, "Result"=>$wpdb->query($SQL));
}

//update the dates of the given discount to be in the future
function update_discount_dates($discount_id)
{
    global $wpdb;
    $pfx = $wpdb->prefix;

    //get the discount code
    $SQL = "";
    $SQL .= "SELECT meta_value ";
    $SQL .= " FROM ".$pfx."postmeta ";
    $SQL .= " WHERE post_id=".$discount_id ;
    $SQL .= " AND meta_key = 'pms_discount_code'";
    $results = $wpdb->get_results($SQL);
    if (count($results) > 0)
    {
        $code = $results[0]->meta_value;
        $start_date = "";
        $end_date = "";
        if ($code == 'PCCQ2')
        {
            $start_date = date("Y")."-04-01";
            $end_date = date("Y")."-06-30";
        }
        if ($code == 'PCCQ3')
        {
            $start_date = date("Y")."-07-01";
            $end_date = date("Y")."-09-30";
        }
        if ($code == 'PCCQ4')
        {
            $start_date = date("Y")."-10-01";
            $end_date = date("Y")."-12-31";
        }

        //only update if dates were set
        if ($start_date!="" && $end_date!=="")
        {
            $SQL = "";
            $SQL .= "UPDATE ".$pfx."postmeta ";
            $SQL .= " SET meta_value = '".$start_date."' ";
            $SQL .= " WHERE post_id=".$discount_id ;
            $SQL .= " AND meta_key = 'pms_discount_start_date'";
            $result1 = $wpdb->query($SQL);
            
            $SQL2 .= "UPDATE ".$pfx."postmeta ";
            $SQL2 .= " SET meta_value = '".$end_date."' ";
            $SQL2 .= " WHERE post_id=".$discount_id ;
            $SQL2 .= " AND meta_key = 'pms_discount_expiration_date'";
            $result2 = $wpdb->query($SQL2);
            
            return array(
                "SQL1"=>$SQL, 
                "Result1"=>$wpdb->query($SQL),
                "SQL2"=>$SQL2, 
                "Result2"=>$wpdb->query($SQL2)
            );
        }
    }
    return false; //unable to update
}

//get by id
function get_pms_subscription($subs_id)
{
    global $wpdb;
    $pfx = $wpdb->prefix;
    $SQL = "";
    $SQL .= "SELECT user_id, subscription_plan_id, start_date, expiration_date, status ";
    $SQL .= " FROM ".$pfx."pms_member_subscriptions ";
    $SQL .= " WHERE id=".$subs_id;
    $results = $wpdb->get_results($SQL);
    if (count($results) > 0)
    {
        return $results[0];
    }
    return array(
        "subscription_plan_id" => "-1",        
        "start_date"=> "",
        "expiration_date"=>"",
        "status" => ""
    );
}

//only gets the main subs - not Racing Fee
function get_user_pms_subscription($user_id)
{    
    global $wpdb;
    $pfx = $wpdb->prefix;
    $SQL = "";
    $SQL .= "SELECT user_id, vSubs.subscription_plan_id, subscription_title, start_date, expiration_date, status ";
    $SQL .= " FROM ".$pfx."pms_member_subscriptions ";
    $SQL .= " INNER JOIN (";
    $SQL .= "    SELECT id AS subscription_plan_id, post_title AS subscription_title ";
    $SQL .= "    FROM  ".$pfx."posts "; 
    $SQL .= "    WHERE post_type = 'pms-subscription'";
    $SQL .= "       AND post_title <> 'PCC Racing Fee' ";
    $SQL .= " ) AS vSubs ";
    $SQL .= " ON (vSubs.subscription_plan_id = ".$pfx."pms_member_subscriptions.subscription_plan_id) ";
   
    $SQL .= " WHERE user_id=".$user_id;
    $results = $wpdb->get_results($SQL);
    if (count($results) > 0)
    {
        return $results[0];
    }
    return array(
        "subscription_plan_id" => "-1",
        "subscription_title"=>"", 
        "start_date"=> "",
        "expiration_date"=>"",
        "status" => ""
    );
}

//get HTML options for a list of subscriptions
//do not get the not_subs (subscription title)
function get_plan_options($not_subs='')
{
    global $wpdb;
    $pfx = $wpdb->prefix;
    $SQL = "";
    $SQL .= "SELECT post_type, id, post_title, post_name, post_status";
    $SQL .= " FROM ".$pfx."posts ";
    $SQL .= " WHERE post_type = 'pms-subscription'";
    $SQL .= "       AND post_title <> 'PCC Racing Fee' ";
    $SQL .= " ORDER BY post_title ";
    $results = $wpdb->get_results($SQL);
    $content = '';
    for($i=0; $i< count($results); $i++)
    {
        if($results[$i]->post_title != $not_subs)
        {
            $content.='<option value="'.$results[$i]->id.'">'.$results[$i]->post_title.'</option>';
        }
    }
    return $content;
}

//get HTML table
function get_user_subs_table()
{
    global $wpdb;
    $pfx = $wpdb->prefix;
    $SQL = "";

    $SQL .= "SELECT vUserSubs.id, vUserSubs.user_id,  vFirstNames.meta_value first_name, vLastNames.meta_value last_name,  ";
    $SQL .= "vSubs.subscription_title,  vUserSubs.status,  vUserSubs.start_date, vUserSubs.expiration_date  ";
    $SQL .= "FROM ".$pfx."pms_member_subscriptions AS vUserSubs ";
    $SQL .= "INNER JOIN (SELECT id AS subscription_plan_id, post_title AS subscription_title ";
    $SQL .= "FROM ".$pfx."posts WHERE post_type = 'pms-subscription') AS vSubs ";
    $SQL .= "ON (vSubs.subscription_plan_id = vUserSubs.subscription_plan_id ) ";
    $SQL .= "INNER JOIN ".$pfx."users ON (".$pfx."users.ID = vUserSubs.user_id) ";
    $SQL .= "LEFT JOIN ".$pfx."usermeta vFirstNames ON (vFirstNames.user_id = vUserSubs.user_id AND vFirstNames.meta_key = 'first_name') ";
    $SQL .= "LEFT JOIN ".$pfx."usermeta vLastNames ON (vLastNames.user_id = vUserSubs.user_id AND vLastNames.meta_key = 'last_name') ";
    $SQL .= " ORDER BY last_name, first_name ";

    $results = $wpdb->get_results($SQL);
    $content = '';
    $content .= '<table class="pcc-table">';
    $content .= '<thead><tr><th>Name</th><th>Subscription</th><th>Status</th><th>Start Date</th><th>Start Quarter</th><th>Expires</th><th style="min-width:60px">Dates</th></thead>';
    
    for($i=0; $i< count($results); $i++)
    {
        //colours the row if the start or expiry date is not aligned
        if (!is_quarter_aligned($results[$i]->start_date) || !is_end_of_year_aligned($results[$i]->expiration_date)) 
        {
            $content .= '<tr class="pcc-table-row-highlight"';
        }
        else{
            $content .= '<tr';
        }
        $content .= ' data-id="'.$results[$i]->id.'">';
        $content .= '<td>'.$results[$i]->first_name.' '.$results[$i]->last_name.'</td>';
        $content .= '<td>'.$results[$i]->subscription_title.'</td>';
        $content .= '<td>'.$results[$i]->status.'</td>';
        $content .= '<td>'.$results[$i]->start_date.'</td>';
        $content .= '<td>'.get_quarter_label($results[$i]->start_date).'</td>';
        $content .= '<td>'.$results[$i]->expiration_date.'</td>';
        if (!is_quarter_aligned($results[$i]->start_date) || !is_end_of_year_aligned($results[$i]->expiration_date)) 
        {
            $content .= '<td style="text-align:center"><a class="button-update-subs pcc-button pcc-button-small" title="Update subscription dates">Update</a></td>';
        }
        else
        {
            $content .= '<td style="text-align:center">Aligned</td>';
        }
        $content .= '</tr>';
    }
    
    $content .= '</tbody>';
    
    $content .= '</table>';
  
    return $content;
}

//get HTML table
function get_discounts_table()
{
    global $wpdb;
    $pfx = $wpdb->prefix;
    $SQL = "";

    $SQL .= "SELECT id discount_id, post_title, post_name, post_status discount_status, ";
	$SQL .= " vDiscountCode.meta_value discount_code, 	vDiscountAmount.meta_value discount_percent, ";
	$SQL .= " vDiscountStartDate.meta_value discount_startdate, 	vDiscountEndDate.meta_value discount_expires ";
    $SQL .= " FROM ".$pfx."posts ";
    $SQL .= " LEFT JOIN ".$pfx."postmeta AS vDiscountCode ON ( ".$pfx."posts.id =  vDiscountCode.post_id AND vDiscountCode.meta_key='pms_discount_code') ";
    $SQL .= " LEFT JOIN ".$pfx."postmeta AS vDiscountAmount ON ( ".$pfx."posts.id =  vDiscountAmount.post_id AND vDiscountAmount.meta_key='pms_discount_amount') ";
    $SQL .= " LEFT JOIN ".$pfx."postmeta AS vDiscountStartDate ON ( ".$pfx."posts.id =  vDiscountStartDate.post_id AND vDiscountStartDate.meta_key='pms_discount_start_date') ";
    $SQL .= " LEFT JOIN ".$pfx."postmeta AS vDiscountEndDate ON ( ".$pfx."posts.id =  vDiscountEndDate.post_id AND vDiscountEndDate.meta_key='pms_discount_expiration_date') ";
    $SQL .= " WHERE post_type = 'pms-discount-codes' ORDER BY discount_startdate";

    $results = $wpdb->get_results($SQL);
    $content = '';
   
    $content .= '<table class="pcc-table">';
    $content .= '<thead><tr><th>Discount</th><th>Code</th><th>Percent</th><th>Status</th><th>Valid From</th><th>Expires</th><th style="min-width:60px">Dates</th></thead>';

    for($i=0; $i< count($results); $i++)
    {
        if (is_date_in_past($results[$i]->discount_startdate))
        {
            $content .= '<tr class="pcc-table-row-highlight"';
        }
        else
        {
            $content .= '<tr';
        }
        $content .= ' data-id="'.$results[$i]->discount_id.'">'; 

        $content .= '<td>'.$results[$i]->post_title.'</td>';
        $content .= '<td>'.$results[$i]->discount_code.'</td>';
        $content .= '<td>'.$results[$i]->discount_percent.'</td>';
        $content .= '<td>'.$results[$i]->discount_status.'</td>';
        $content .= '<td>'.$results[$i]->discount_startdate.'</td>';
        $content .= '<td>'.$results[$i]->discount_expires.'</td>';
        if (is_date_in_past($results[$i]->discount_startdate))
        {
            $content .= '<td style="text-align:center"><a class="button-update-discount pcc-button pcc-button-small" title="Update subscription dates">Update</a></td>';
        }
        else
        {
            $content .= '<td style="text-align:center">Aligned</td>';
        }
        
        $content .= '</tr>';
    }
    
    $content .= '</tbody>';
    
    $content .= '</table>';
  
    return $content;
    
}

//update any discount code dates that need changing
function update_quarter_discounts()
{
    //update quarter discounts start/expiry dates according to today's date
    $log = "";

    $year_no = date("Y");
    $month_no = 4; //date("m");
    
    $qtr = 1;
    if ($month_no > 3 ) $qtr++;
    if ($month_no > 6 ) $qtr++;
    if ($month_no > 9 ) $qtr++;

    for ($i=0; $i<4; $i++)
    {
    	
        if ($qtr> 4) 
        { 
        	$qtr=1;
            $year_no++;
        }
        if ($qtr!=1)
        {
            $log .= "</br>";
            $code = "PCCQ".$qtr;
            $log .= "Processing ".$code." ..." ;
            
            $start_month = 1;
            $end_month=4;
            $end_year=$year_no;
            if ($qtr==2) {$start_month=4; $end_month=7;}
            if ($qtr==3) {$start_month=7; $end_month=10;}
            if ($qtr==4) {
                $start_month=10;
                $end_month=1;
                $end_year = $year_no+1;
            }
            $log .= "</br>";
            $log .= " Year=".$year_no;
            $log .= "</br> Quarter=".$qtr;
            
            $log .= "</br>";
            
            $start_str = $year_no."-".$start_month."-01 00:00:00" ;
            
            
            $end_str = $end_year."-".$end_month."-01 00:00:00";
        
        
            $start =  date_create($start_str); //mktime(0,0,0,$startm, 1, $year_no);  
            
            $end =   date_create($end_str); //mktime(0,0,0,$startm+3, 1, $year_no);
            date_add($end,date_interval_create_from_date_string("-1 seconds"));
            
            $log .= " From ";
            $log .= date_format($start,"Y-m-d H:i:s");
            $log .= "</br>";
            $log .= " To ";
            $log .= date_format($end,"Y-m-d H:i:s");
            $log .= "</br>";
            
            //update
            $log .= update_quarter_discount($code, date_format($start,"Y-m-d H:i:s"), date_format($end,"Y-m-d H:i:s"));
        }
        $qtr++;
    }
    echo $log;
}

//update one discount code's dates
function update_quarter_discount($code, $start_date, $end_date)
{
    
    global $wpdb;
    $pfx = $wpdb->prefix;
    $SQL = "SELECT 1";
    
    return $wpdb->query($SQL);
}
   

?>