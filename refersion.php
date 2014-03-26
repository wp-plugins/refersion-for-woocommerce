<?php

/***************************************************************************

Plugin Name:  Refersion for WooCommerce
Plugin URI:   https://www.refersion.com
Description:  Integrates <a href="https://www.refersion.com">Refersion</a> tracking with your WooCommerce store.
Version:      1.0
Author:       Refersion
Author URI:   https://www.refersion.com

**************************************************************************/

/*  Copyright 2014  Refersion  (email : helpme@refersion.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// WP Admin
add_action('admin_init', 'refersion_admin_init');
add_action('admin_menu', 'refersion_pages');

// Site tracking
add_action('wp_footer', 'refersion_init');
add_action('woocommerce_thankyou', 'refersion_thankyou');

// Run checks (WP Admin)
function refersion_admin_init() {

	// Show warning if Refersion Tracking isn't setup
	$refersion_status = get_option('refersion_status');
	if (empty($refersion_status)) add_action('admin_notices', 'refersion_warning');

}

// Show message to continue Refersion setup
function refersion_warning() {
	echo "<div id='refersion-message-warning' class='updated fade'><p><strong>Refersion for WooCommerce is almost ready.</strong> ".sprintf('<a href="%1$s">Click here to configure the plugin</a>.', "admin.php?page=refersion-config")."</p></div>";
}

// Display Refersion settings in WP Admin menu
function refersion_pages() {   

	if (function_exists('add_submenu_page')) {
		add_submenu_page('options-general.php', 'Refersion', 'Refersion', 'manage_options', 'refersion-config', 'refersion_config');
	}

}

// Refersion click tracking
function refersion_init() {
	
	$refersion_status = get_option('refersion_status');
	$refersion_api_key = get_option('refersion_api_key');
	if ($refersion_status == 'ENABLED' && isset($refersion_api_key)) {
?>
	<script type="text/javascript">
		function refersion_getQS(e){e=e.replace(/[\[]/,"\\[").replace(/[\]]/,"\\]");var t=new RegExp("[\\?&]"+e+"=([^&#]*)"),n=t.exec(location.search);return n==null?"":decodeURIComponent(n[1].replace(/\+/g," "))}
		if (refersion_getQS('rfsn') != '') {
			var imageClick = new Image(); document.body.appendChild(imageClick); imageClick.src = "https://www.refersion.com/p"+location.search; imageClick.setAttribute('style', 'float: right;');
			console.log("Refersion custom tracking loaded @ https://www.refersion.com/p"+location.search);
		}
	</script>
<?php 
	}

}

// Refersion conversion pixel tracking
function refersion_thankyou($o) {
	
	$refersion_status = get_option('refersion_status');
	$refersion_api_key = get_option('refersion_api_key');

	// REFERSION TRACKING PIXEL
	if ($refersion_status == 'ENABLED' && isset($refersion_api_key)) {
		$order = new WC_Order($o);
		echo "<script type=\"text/javascript\">var image = new Image(); document.body.appendChild(image); image.src = \"https://www.refersion.com/tracker/woocommerce/?k=" . $refersion_api_key . "&ci=" . $order->id . "\"; image.setAttribute('style', 'float: right;');</script>";
	}

}

// Refersion config page
function refersion_config() {

	if (!empty($_POST)) {
		update_option('refersion_status', $_POST['refersion_status']);
		update_option('refersion_api_key', $_POST['refersion_api_key']);
	}
	$refersion_status = get_option('refersion_status');
	$refersion_api_key = get_option('refersion_api_key');
?>

	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>

		<!-- <h2>Refersion Settings</h2> -->

		<div style="width: 772px">		

			<div style="background: #eee">
				<img src="<?php echo plugins_url('refersion_logo.png', __FILE__); ?>" alt="Refersion" />

				<p>
					In order to automatically setup Refersion tracking on your WooCommerce shop, the following settings must be filled out. For help, visit our <a href="https://refersion.uservoice.com/" target="_blank">Knowledge Base</a>.
				</p>

				<div style="font-size: 13px; line-height: 1.5em">
					<?php if (empty($refersion_status) || empty($refersion_api_key)): ?>
						This plugin requires a <a href="https://www.refersion.com" target="_blank">Refersion</a> account. If you do not already have an account, you can <a href="https://www.refersion.com/pricing" target="_blank">sign up</a> right now. 
					<?php endif; ?>
				</div>
			</div>

			<div style="background: #eee">
				<div style="background: #ccc; padding: 10px; font-weight: bold">Configuration</div>

				<form method="post" action="admin.php?page=refersion-config">

				<table class="form-table" style="margin: 10px">
					<tr>
						<th scope="row" style="white-space: nowrap"><label>Tracking Status:</label></th>
						<td style="width: 100%">
							<select name="refersion_status">
								<option value="ENABLED" <?php if ($refersion_status == "ENABLED") {?>selected="selected"<?php } ?>>Enabled</option>
								<option value="DISABLED" <?php if ($refersion_status == "DISABLED") {?>selected="selected"<?php } ?>>Disabled</option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" style="white-space: nowrap"><label>Your Refersion Public API Key:</label></th>
						<td style="width: 100%">
							<input type="text" name="refersion_api_key" style="width: 250px;" value="<?php echo $refersion_api_key; ?>" />
							<a href="https://refersion.uservoice.com/knowledgebase/articles/337317-where-can-i-find-my-refersion-public-api-key" target="_blank">Where do I find this?</a>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" style="white-space: nowrap"></th>
						<td style="width: 100%">
							<input type="submit" value="Save Changes" class="button" />
						</td>
					</tr>
				</table>

				</form>

			</div>

		</div>

	</div>

<?php } ?>