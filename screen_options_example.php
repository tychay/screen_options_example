<?php
/**
 * Plugin Name: Screen Options Examples
 * Plugin URI: http://terrychay.com/wordpress-plugins/screen-options-example
 * Description: Example code on how to manipulate help and screen options
 * Version: 1.0
 * Author: tychay
 * Author URI: http://terrychay.com/
 * Text Domain: scroptex
 * Domain Path: /languages/
 * License: GPLv2
 */

/**
 * Copyright 2013  tychay  (email: tychay@php.net)
 *
 * Credit: Settings API from mattyza & pmgarman
 * https://twitter.com/mattyza
 * https://twitter.com/pmgarman
 *
 * Built using the Plugin Jump Starter!
 * https://github.com/pmgarman/plugin-jump-starter
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !class_exists( 'screen_options_example' ) ) {
	require 'classes/class-screen-options-example-settings-api.php';
	require 'classes/class-screen-options-example-settings-screen.php';
	require 'classes/class-screen-options-example-settings.php';
	require 'classes/class-screen-options-example.php';

	global $screen_options_example;
	$screen_options_example = new screen_options_example( __FILE__ );

	load_plugin_textdomain( 'scroptex', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}