jQuery( function($) {


    //PCC Subscription Plan Corrections (AJAX) Form
    //=============================================

    $("#input_search").on('keyup', function (e)
    {
        if ($("#input_search").val() == undefined || $("#input_search").val().length < 2)
        {
            $('#button_search').addClass("disabled");
        }
        else
        {
            $('#button_search').removeClass("disabled");
            if (e.which == 13)
            {
                $('#button_search').trigger('click');
            }
        }
    })

    $('#button_search').on('click',function ()
    {
        if ($("#input_search").val() == undefined || $("#input_search").val().length < 2) return;
        $("#status_bar").text("Button clicked - making the AJAX call...");    
        $.post(ajaxurl,
            { //properties
                _ajax_nonce: $("#pcc_subs_correction_nonce_field").val(),
                search_criteria: $("#input_search").val(),
                action: "search_members_ajax",             
                
            }, 
            function(data)
            { //callback
                if (data.options != undefined && data.count > 0)
                {
                    $("#status_bar").text("Please choose one member...");    
                    $("#list_names").html(data.options);
                    $("#list_names").focus();
                    $("#list_names").val($("#list_names option:first").val()).trigger('change');                    
                }
                else
                {
                    $("#status_bar").text("Ready...");   
                    $("#list_names").html("");
                }
            }
        );
    })

    $('#list_names').on('change', function ()
    {
        if ($("#list_names").val() == undefined) {
            reset_form();
            return;
        }
        $("#status_bar").text("List clicked - making the AJAX call..." + $("#list_names").val());    
        $.post(ajaxurl,
            { //properties
                _ajax_nonce: $("#pcc_subs_correction_nonce_field").val(),
                user_id: $("#list_names").val(),
                action: "get_member_ajax",             
                
            }, 
            function(data)
            { //callback
                if (data != undefined && data.find_status)
                {
                    $("#status_bar").text("Choose a new plan...");      
                    $("#first_name").val(data.first_name); 
                    $("#last_name").val(data.last_name);  
                    $("#current_plan").val(data.plan_title);  
                    $("#current_plan_id").val(data.plan_id);
                    $("#list_plans").html(data.options);
                    $("#button_change_plan").removeClass("disabled");
                }
                else
                {
                    reset_form();
                }
            }
        );
    })

    $('#button_reset').on('click', function ()
    {
        reset_form();
    })

    function reset_form(msg)
    {
        if (msg!=undefined && msg!="")
        {
            $("#status_bar").text(msg);   
        }
        else
        {
            $("#status_bar").text("Ready...");
        }
        $("#input_search").val(""); 
        $("#first_name").val(""); 
        $("#last_name").val("");  
        $("#current_plan").val("");    
        $("#current_plan_id").val("");
        $("#list_names").html("");
        $("#list_plans").html("");
        $("#button_change_plan").addClass("disabled");
        $("#button_search").addClass("disabled");
    }

    $('#button_change_plan').on('click', function ()
    {
        if ($("#list_names").val() == undefined) return;
        $("#status_bar").text("Make Change clicked - making the AJAX call...");    
        $.post(ajaxurl,
            { //properties
                _ajax_nonce: $("#pcc_subs_correction_nonce_field").val(),
                user_id: $("#list_names").val(),
                old_plan_id: $("#current_plan_id").val(),
                new_plan_id: $("#list_plans").val(),
                action: "set_member_plan_ajax",             
                
            }, 
            function(data)
            { //callback
                if (data != undefined && data.result_status)
                {
                    $("#status_bar").text("Plan updated.");      
                    $("#first_name").val(data.first_name); 
                    $("#last_name").val(data.last_name);  
                    $("#current_plan").val(data.plan_title);  
                    $("#current_plan_id").val(data.plan_id);  
                    $("#list_plans").html(data.options);
                    $("#button_change_plan").removeClass("disabled");
                }
                else
                {
                    reset_form("There was a problem; please try again.");
                }
            }
        );

        
    })

    //PCC Subscription Quarters Management
    //====================================

    //PCC Discount Codes Management
    //-----------------------------

    $('a.button-update-discount').on('click', button_update_discount);

    function button_update_discount()
    {
        $("#status_bar").text("Update subscription dates button clicked - processing..."); 
        $.post(ajaxurl,
            { //properties
                _ajax_nonce: $("#pcc_quarters_nonce_field").val(),  
                discount_id: $(this).parents("tr").attr("data-id"),              
                action: "update_discount_ajax",                             
            }, 
            function(data)
            { //callback
                if (data != undefined)
                {
                    $("#status_bar").text(data.message);    
                    $("#discount_table").html(data.table_html);  
                    //must hook up the click again (to this function!)
                    $('a.button-update-discount').on('click', button_update_discount);                                    
                }
                else
                {
                    $("#status_bar").text("Ready...");                      
                }
            }
        );  
    }

    $("#button_refresh_discounts").on('click', function ()
    {
        $("#status_bar").text("Refresh table...");    
        $.post(ajaxurl,
            { //properties
                _ajax_nonce: $("#pcc_quarters_nonce_field").val(),
                action: "refresh_discounts_table_ajax",             
                
            }, 
            function(data)
            { //callback
                if (data != undefined)
                {
                    $("#status_bar").text("Ready...");    
                    $("#discounts_table").html(data.table_html);  
                    //must hook up the click again (to this function!)
                    $('a.button-update-discount').on('click', button_update_discount);                                      
                }
                else
                {
                    $("#status_bar").text("Problem refreshing table; please try again.");   
                    $('a.button-update-discount').on('click', button_update_discount); 
                }
            }
        );
    })

    //Subscription Date Management
    //----------------------------

    $('a.button-update-subs').on('click', button_update_subs);

    function button_update_subs()
    {
        $("#status_bar").text("Update subscription dates button clicked - processing..."); 
        $.post(ajaxurl,
            { //properties
                _ajax_nonce: $("#pcc_subs_date_nonce_field").val(),  
                subs_id: $(this).parents("tr").attr("data-id"),              
                action: "update_user_sub_ajax",                             
            }, 
            function(data)
            { //callback
                if (data != undefined)
                {
                    $("#status_bar").text(data.message);    
                    $("#subs_table").html(data.table_html);  
                    //must hook up the click again (to this function!)
                    $('a.button-update-subs').on('click', button_update_subs);                                    
                }
                else
                {
                    $("#status_bar").text("Ready...");                      
                }
            }
        );  
    }

    $("#button_refresh").on('click', function ()
    {
        $("#status_bar").text("Refresh table...");    
        $.post(ajaxurl,
            { //properties
                _ajax_nonce: $("#pcc_subs_date_nonce_field").val(),
                action: "refresh_subs_table_ajax",             
                
            }, 
            function(data)
            { //callback
                if (data != undefined)
                {
                    $("#status_bar").text("Ready...");    
                    $("#subs_table").html(data.table_html);  
                    //must hook up the click again (to this function!)
                    $('a.button-update-subs').on('click', button_update_subs);                                      
                }
                else
                {
                    $("#status_bar").text("Problem refreshing table; please try again.");   
                    $('a.button-update-subs').on('click', button_update_subs); 
                }
            }
        );
    })

})
