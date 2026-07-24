<?php
//if uninstall/delete not called from WordPress exit
if( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit ();

// Delete options array from options table
//no options to remove in this plugin
//delete_option( 'pluginname_options' );
