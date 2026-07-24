<?php
/*
Plugin Name: Example Form Plugin - Short Code
Text Identifier: example-form-plugin
Custom Post Type: None
Plugin URI: 
Description: Create a short code to display and process a form
Version: 1.0
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

function example_form_plugin()
{
    $content = '';
    $content .= 'HELLOOO';

    return $content;
}

add_shortcode('example_form', 'example_form_plugin');

?>