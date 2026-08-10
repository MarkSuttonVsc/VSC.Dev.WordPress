<?php
/*
    File:           pcc-admin-date-functions.php
    Description:    PHP date functions for the PCC website (admin)
    Include Status: Required
    Author:         Mark Sutton
*/

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

function get_quarter_label($datestr)
{
    $date = strtotime($datestr);
    $month = gmdate('m',$date);
    if ($month == 1 || $month == 2 || $month == 3)
    {
        return "Q1 ".gmdate('Y',$date);
    }
    if ($month == 4 || $month == 5 || $month == 6)
    {
        return "Q2 ".gmdate('Y',$date);
    }
    if ($month == 7 || $month == 8 || $month == 9)
    {
        return "Q3 ".gmdate('Y',$date);
    }
    if ($month == 10 || $month == 11 || $month == 12)
    {
        return "Q4 ".gmdate('Y',$date);
    }
    return "";
}

function is_end_of_year_aligned($datestr)
{
    //it doesnt matter what the year is
    $date = strtotime($datestr);
    if (gmdate('d',$date) !=31)     return false;
    if (gmdate('m',$date) !=12)     return false;
    if (gmdate('H',$date) !=23)     return false;
    if (gmdate('i',$date) !=59)     return false;
    if (gmdate('s',$date) !=59)     return false;

    return true;
}

function end_of_year_align($datestr)
{
    //if the given year is smaller than this year - use that,
    //otherwise use this year
    $this_year = gmdate('Y',time()); 
    $date = strtotime($datestr);
    $date_year = gmdate('Y', $date);
    if ($date_year < $this_year)
    {
        return $date_year."-12-31 23:59:59";
    }
    return $this_year."-12-31 23:59:59";
}

function is_quarter_aligned($datestr)
{
    $date = strtotime($datestr);
    //must be day 1 (all quarters)
    if (gmdate('d',$date) !=1)     return false;
    //must be midnight (all quarters)
    if (gmdate('H',$date) !=0)     return false;
    if (gmdate('i',$date) !=0)     return false;
    if (gmdate('s',$date) !=0)     return false;

    $month = gmdate('m',$date);
    //Q1 must be Jan
    if ($month == 1 || $month == 2 || $month == 3)
    {
        if (gmdate('m',$date) !=1)     return false;
    }
    //Q2 must be Apr
    if ($month == 4 || $month == 5 || $month == 6)
    {
        if (gmdate('m',$date) !=4)     return false;
    }
    //Q2 must be Jul
    if ($month == 7 || $month == 8 || $month == 9)
    {
        if (gmdate('m',$date) !=7)     return false;
    }
    //Q4 must be Oct
    if ($month == 10 || $month == 11 || $month == 12)
    {
        if (gmdate('m',$date) !=10)     return false;
    }
    
    return true;
}

//returns date string in YYYY-mm-dd HH:mm:ss format
function quarter_align($datestr)
{
    $date = strtotime($datestr);
    $month = gmdate('m',$date);
    $new_month = $month;
    
    //Q1 must be Jan
    if ($month == 2 || $month == 3)
    {
        $new_month = 1;
    }
    //Q2 must be Apr
    if ($month == 5 || $month == 6)
    {
        $new_month = 4;
    }
    //Q2 must be Jul
    if ( $month == 8 || $month == 9)
    {
        $new_month = 7;
    }
    //Q4 must be Oct
    if ( $month == 11 || $month == 12)
    {
        $new_month = 10;
    }

    $year = gmdate('Y',$date);
    
    return $year."-".$new_month."-01 00:00:00";

}

function is_date_in_past($datestr)
{
    $date = strtotime($datestr);
    if ($date < time()) return true;
    return false;
}