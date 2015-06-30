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

class Refersion {

	/**
	* Check if Woocomerce already installed or not
	*/
	public function check_woocomerce() {

		// Require parent plugin
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) and current_user_can( 'activate_plugins' ) ) {
		
			// Stop activation redirect and show error
			wp_die('Sorry, but this plugin requires the Woocommerce to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
			
		}

	}

	/**
	* Create Refersion identifier html file if not created
	*/
	public function refersion_create_file(){

		$options = get_option( 'refersion_option_name' );
		if(isset($options['refersion_public_api_key'])) {

			if($options['refersion_public_api_key'] != '') {			

				$refersion_file = ABSPATH.'rfsn_' . $options['refersion_public_api_key'] . '.html';
				if(!file_exists($refersion_file)) {

					$ref_file = fopen($refersion_file, "w") or die("Unable to open file!");
					fwrite($ref_file, $options['refersion_public_api_key']);
					fclose($ref_file);

				}

			}

		}

	}

	/**
	* Remove Refersion identifier html file if created
	*/
	public function refersion_remove_file(){
		
		$options = get_option( 'refersion_option_name' );
		if(isset($options['refersion_public_api_key'])) {

			if($options['refersion_public_api_key']!='') {

				$refersion_file = ABSPATH . 'rfsn_' . $options['refersion_public_api_key'] . '.html';
				if(file_exists($refersion_file)){
					unlink($refersion_file);
				}

			}

		}

	}

	/**
	* Refersion webhook set variables
	*/
	public function refersion_process_order($order_id) {

		// Array to hold order value to be converted in json
		$order_json = array();
		$options = get_option( 'refersion_option_name' );

		// Populating values to repective indexes
		if($options['refersion_status']) {

			$order_json['refersion_public_key'] = $options['refersion_public_api_key'];
			$order_json['refersion_secret_key'] = $options['refersion_secret_api_key'];	
			
			// Get order object for payment
			$order = new WC_Order( $order_id );
			$order_json['cart_id'] = session_id();
			$order_json['order_id'] = $order_id;
			
			$orderGrandTotal = $order->get_total();
			$order_json['shipping'] = $order->get_total_shipping( );
	
			// Tax info
			$order_json['tax'] =  $order->get_total_tax();
			
			$coupon = '';
			if(count($order->get_used_coupons())){
				$coupon = implode(',',$order->get_used_coupons());
			}
			$order_json['discount'] = abs($order->get_total_discount());
			$order_json['discount_code'] = $coupon;
			
			$order_json['currency_code'] = $order-> get_order_currency();
			
			// Customer details
			$order_json['customer']['first_name'] = $order->billing_first_name;
			$order_json['customer']['last_name'] = $order->billing_last_name;
			$order_json['customer']['email'] = $order->billing_email;
			$order_json['customer']['ip_address'] = $_SERVER['REMOTE_ADDR'];

			// Get all the items for the order to fetch individual details
			$items = $order->get_items();
			$itemcount=$order->get_item_count();
			
			// Line items
			foreach ($items as $item) {
				$product = new WC_Product($item['product_id']);
				$order_json['items'][$item['product_id']]['sku'] = $product->get_sku();
				$order_json['items'][$item['product_id']]['quantity'] = $item['qty'];
				$order_json['items'][$item['product_id']]['price'] = $product->get_price();		   
			}
			
			// Sending value via curl to refersion
			$resp = Refersion::curl_refersion_post($order_json);	

		}

	}
	
	/**
	* Refersion webhook curl call
	*/
	function curl_refersion_post($order_data) {
		
		$json_data = json_encode($order_data);
		
		// The URL that you are posting to
		$url = 'https://www.refersion.com/tracker/v3/webhook';
		
		// Start cURL
		$curl = curl_init($url);
		
		// Verify that our SSL is active (for added security)
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
		
		// Send as a POST
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		
		// The JSON data that you have already compiled
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
		
		// Return the response
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		
		// Set headers to be JSON-friendly
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    'Content-Length: ' . strlen($json_data))
		);
		
		// Seconds (5) before giving up
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
		
		// Execute post, capture response (if any) and status code
		$result = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		
		// Close connection
		curl_close($curl);

	} //end function curl_refersion_post
	
	/**
	* Refersion js code
	*/
	public function refersion_print_pixel() {

		global $gb_order_id;
		$options = get_option( 'refersion_option_name' );

		echo '<!-- REFERSION TRACKING: BEGIN --><script src="//www.refersion.com/tracker/v3/' . $options['refersion_public_api_key'] . '.js"></script><script>_refersion(function(){ _rfsn._addCart("' . session_id() . '"); });</script><!-- REFERSION TRACKING: END -->';

	}

	/**
	* Refersion js code on Wocommerce thank you page
	*/
	public function refersion_set_pixel($order_id) {

		global $gb_order_id;
		$gb_order_id = $order_id;

		$options = get_option( 'refersion_option_name' );
		if($options['refersion_status']){
			add_action('wp_footer', array('Refersion','refersion_print_pixel'), 100);	
		}

	}
	
	/**
	* Refersion js code for affiliation click tracking
	*/
	public function refersion_print_global_script() {

		global $woocommerce,$gb_order_id;
		$options = get_option( 'refersion_option_name' );

		if($options['refersion_status']){
			
			if (!is_page( woocommerce_get_page_id( 'thanks' ) )) {
				echo '<!-- REFERSION TRACKING: BEGIN --><script src="//www.refersion.com/tracker/v3/' . $options['refersion_public_api_key'] . '.js"></script><script>_refersion();</script><!-- REFERSION TRACKING: END -->';
			}

		}

	}
  
	/**
	* Strt session if not started
	*/
	public function refersion_set_session() {

		if(!session_id()) {
			session_start();
		}

	}

}