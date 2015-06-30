<?php

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

class Refersion_Admin
{

	/**
	* Holds the values to be used in the fields callbacks
	*/
	private $options;
	private $menu_name = 'refersion-navigation';
	private $menu_id;

	/**
	* Start up
	*/
	public function __construct() { 

		// Show warning if Refersion Tracking isn't setup
		$options = get_option( 'refersion_option_name' );
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );   

	}

	/**
	* Display message upon plug-in activation
	*/
	function activation_message() {

		if ( !is_array( get_option('refersion_option_name') ) ) {

			$message = __( 'Refersion for WooCommerce is almost ready.', 'refersion-for-woocommerce' );
			$link = sprintf( __( '<a href="%1$s">Click here to configure the plugin</a>.', 'refersion-setting-admin' ), 'admin.php?page=refersion-setting-admin' );
			echo sprintf( '<div id="refersion-message-warning" class="updated fade"><p><strong>%1$s</strong> %2$s</p></div>', $message, $link );

		}

	}
  
	/**
	* Add options page
	*/
	public function add_plugin_page() {

		// This page will be under "Settings"
		add_options_page(
			'Settings Admin', 
			'Refersion', 
			'manage_options', 
			'refersion-setting-admin', 
			array( $this, 'create_admin_page' )
		);

	}

	/**
	* Options page callback
	*/
	public function create_admin_page() {

		// Set class property
		$this->options = get_option( 'refersion_option_name' );
?>
	  	<div class="wrap">

			<div id="icon-options-general" class="icon32"><br /></div>

			<!-- <h2>Refersion Settings</h2> -->  
			<div style="width: 772px">
			
				<div style="background: #eee"> <img src="<?php echo plugins_url( 'refersion_logo.png', __FILE__ ); ?>" alt="Refersion" />
				<p>
					<?php _e( 'In order to automatically setup Refersion tracking on your WooCommerce shop, the following settings must be filled out. For help, visit our <a href="https://refersion.uservoice.com/" target="_blank">Knowledge Base</a>.', 'refersion-for-woocommerce' ); ?>
				</p>
					<div style="font-size: 13px; line-height: 1.5em">
					<?php 
						if ( empty( $refersion_status ) || empty( $refersion_api_key ) ) {
							_e( 'This plugin requires a <a href="https://www.refersion.com" target="_blank">Refersion</a> account. If you do not already have an account, you can <a href="https://www.refersion.com/pricing" target="_blank">sign up</a> right now.', 'refersion-for-woocommerce' );
						}
					?>
					</div>
				</div>

				<div style="background: #eee">
					<form method="post" action="options.php">
					<?php
						// This prints out all hidden setting fields
						settings_fields( 'refersion_option_group' );   
						do_settings_sections( 'refersion-setting-admin' );
						submit_button(); 
					?>
					</form>
				</div>

			</div>
		</div>	

<?php
	}

	/**
	* Register and add settings
	*/
	public function page_init() {

		register_setting(
			'refersion_option_group', // Option group
			'refersion_option_name', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'setting_section_id', // ID
			'Configuration', // Title
			array( $this, 'print_section_info' ), // Callback
			'refersion-setting-admin' // Page
		);
		
		 

		add_settings_field(
			'refersion_public_api_key', // ID
			'Your Refersion Public API Key', // Title 
			array( $this, 'refersion_public_api_key_callback' ), // Callback
			'refersion-setting-admin', // Page
			'setting_section_id' // Section
		);
		
		add_settings_field(
			'refersion_secret_api_key', // ID
			'Your Refersion Secret API Key', // Title 
			array( $this, 'refersion_secret_api_key_callback' ), // Callback
			'refersion-setting-admin', // Page
			'setting_section_id' // Section
		);
		
		add_settings_field(
			'refersion_status', 
			'Tracking Status', 
			array( $this, 'refersion_status_callback' ), 
			'refersion-setting-admin', 
			'setting_section_id'
		);

	}

	/**
	* Sanitize each setting field as needed
	*
	* @param array $input Contains all settings fields as array keys
	*/
	public function sanitize( $input ) {

		$new_input = array();
		if( isset( $input['refersion_public_api_key'] ) ) {
			$new_input['refersion_public_api_key'] = $input['refersion_public_api_key'] ;
		}

		if( isset( $input['refersion_secret_api_key'] ) ) {
			$new_input['refersion_secret_api_key'] = $input['refersion_secret_api_key'] ;	
		}

		if( isset( $input['refersion_status'] ) ) {
			$new_input['refersion_status'] = $input['refersion_status'];
			return $new_input;
		}

	}

	/** 
	* Print the Section text
	*/
	public function print_section_info() {
		print 'Enter your settings below:';
	}

	/** 
	* Get the settings option array and print one of its values
	*/
	public function refersion_public_api_key_callback() {

		printf(
			'<input type="text" id="refersion_public_api_key" name="refersion_option_name[refersion_public_api_key]" value="%s" style="width:300px;" />',
			isset( $this->options['refersion_public_api_key'] ) ? esc_attr( $this->options['refersion_public_api_key']) : ''
		);

	}
  
	/** 
	* Get the settings option array and print one of its values
	*/
	public function refersion_secret_api_key_callback() {

		printf(
			'<input type="text" id="refersion_secret_api_key" name="refersion_option_name[refersion_secret_api_key]" value="%s" style="width:300px;" />',
			isset( $this->options['refersion_secret_api_key'] ) ? esc_attr( $this->options['refersion_secret_api_key']) : ''
		);

	}

	/** 
	* Get the settings option array and print one of its values
	*/
	public function refersion_status_callback()  {

		$a = 'selected=selected';
		$b = '';  
		if(isset( $this->options['refersion_status'] )){
		
			if($this->options['refersion_status']==1){
				$b = 'selected=selected';
				$a = ''; 
			}

		}

		echo '<select id="secret_api_key" name="refersion_option_name[refersion_status]"><option value="0" '.$a.'>Disabled</option><option value="1" '.$b.'>Enabled</option></select> '; 
	
	}

}