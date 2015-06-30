<?php

/***************************************************************************

 * Plugin Name: Refersion for WooCommerce
 * Plugin URI: https://www.refersion.com
 * Description: Integrates <a href="https://www.refersion.com">Refersion</a> tracking with your WooCommerce store.
 * Version: 2.0.0
 * Author: Refersion, Inc.
 * Author URI: https://www.refersion.com
 * Text Domain: Refersion.com
 * License: GPL3

***************************************************************************/

/*

Copyright 2015 Refersion, Inc. (email : helpme@refersion.com)

This file is part of Refersion for WooCommerce.

Refersion for WooCommerce is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Refersion for WooCommerce is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Refersion for WooCommerce. If not, see <http://www.gnu.org/licenses/>.

*/


defined('ABSPATH') or die("No direct access allowed!");

define( 'REFERSION_VERSION', '2.0.0' );
define( 'REFERSION__MINIMUM_WP_VERSION', '1.0' );
define( 'REFERSION__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'REFERSION__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

if ( !function_exists( 'add_action' ) ) {
	echo 'No direct access allowed!';
	exit;
}

require_once( REFERSION__PLUGIN_DIR . 'class.refersion.php' );

// Hook implemented to remove refersion identifier html file as the time od deactivation of hook
register_deactivation_hook( __FILE__, array( 'Refersion', 'refersion_remove_file' ) );
if ( is_admin() ) {
	require_once( REFERSION__PLUGIN_DIR . 'class.refersion-admin.php' );
	$my_settings_page = new Refersion_Admin();	
}

// Hook implemented to check if Woocomerce already installed or not
register_activation_hook( __FILE__, array( 'Refersion', 'check_woocomerce' ) );

// Hook implemented to set session
add_action( 'init', array( 'Refersion','refersion_set_session' ), 1);

// Hook implemented to call Refersion js code on Wocommerce checkout page
add_action( 'woocommerce_proceed_to_checkout', array( 'Refersion','refersion_set_pixel' ), 100 );

// Hook implemented to call Refersion webhook curl call
add_action( 'woocommerce_checkout_update_order_meta', array( 'Refersion','refersion_process_order' ), 10, 1 );

// Hook implemented to create Refersion identifier html file if not created
add_action('wp_loaded', array( 'Refersion','refersion_create_file' ), 10 );

// Hook implemented to call Refersion js code for affiliation click track
add_action('wp_footer', array( 'Refersion','refersion_print_global_script' ), 100 );

// Hook implemented to check if Refersion configurations has been set or not
add_action( 'admin_notices', array( 'Refersion_Admin', 'activation_message' ), 1000 ) ;