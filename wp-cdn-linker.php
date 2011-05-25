<?php
/*
Plugin Name: CDN Linker
Plugin URI: https://github.com/wmark/CDN-Linker
Description: Replaces the blog URL by another for all files under <code>wp-content</code> and <code>wp-includes</code>. That way static content can be handled by a CDN by origin pull - the origin being your blog address - or loaded from an other site.
Version: 1.3.1
Author: W-Mark Kubacki
Author URI: http://mark.ossdl.de/
License: RPL for non-commercial
*/

if ( @include_once('cdn-linker-base.php') ) {
	add_action('template_redirect', 'do_ossdl_off_ob_start');
}

/********** WordPress Administrative ********/

function ossdl_off_activate() {
	add_option('ossdl_off_cdn_url', get_option('siteurl'));
	add_option('ossdl_off_include_dirs', 'wp-content,wp-includes');
	add_option('ossdl_off_exclude', '.php');
	add_option('ossdl_off_rootrelative', '');
}
register_activation_hook( __FILE__, 'ossdl_off_activate');

function ossdl_off_deactivate() {
	delete_option('ossdl_off_cdn_url');
	delete_option('ossdl_off_include_dirs');
	delete_option('ossdl_off_exclude');
	delete_option('ossdl_off_rootrelative');
}
// register_deactivation_hook( __FILE__, 'ossdl_off_deactivate');
// Deactivated because: If the user activated this plugin again his previous settings would have been deleted by function.

/********** WordPress Interface ********/
add_action('admin_menu', 'ossdl_off_menu');

function ossdl_off_menu() {
	add_options_page('CDN Linker', 'CDN Linker', 8, __FILE__, 'ossdl_off_options');
}

function ossdl_off_options() {
	if ( isset($_POST['action']) && ( $_POST['action'] == 'update_ossdl_off' )){
		update_option('ossdl_off_cdn_url', $_POST['ossdl_off_cdn_url']);
		update_option('ossdl_off_include_dirs', $_POST['ossdl_off_include_dirs'] == '' ? 'wp-content,wp-includes' : $_POST['ossdl_off_include_dirs']);
		update_option('ossdl_off_exclude', $_POST['ossdl_off_exclude']);
		update_option('ossdl_off_rootrelative', !!$_POST['ossdl_off_rootrelative']);
	}
	$example_cdn_uri = str_replace('http://', 'http://cdn.', str_replace('www.', '', get_option('siteurl')));

	?><div class="wrap">
		<h2>Speed Cache</h2>

		<div id="step1" class="assistant">
			<div class="alternative side-by-side">
				Subscribe by Paypal
				<form name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_xclick-subscriptions">
					<input type="hidden" name="business" value="wordpress-cdn@arcostream.com">
					<input type="hidden" name="currency_code" value="USD">
					<input type="hidden" name="no_shipping" value="1">
					<input type="image" src="http://www.paypal.com/en_US/i/btn/btn_subscribe_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
					<input type="hidden" name="a3" value="2.99">
					<input type="hidden" name="p3" value="1">
					<input type="hidden" name="t3" value="M">
					<input type="hidden" name="src" value="1">
					<input type="hidden" name="sra" value="1">
				</form>
				2.99 USD per month &mdash;
				click here for details
			</div>
			<div class="delimiter side-by-side">
				OR
			</div>
			<div class="alternative side-by-side">
				<label for="arcostream_custid">Already a Subscriber?</label><br />
				<input type="text" name="arcostream_custid" id="arcostream_custid" value="your customer ID" size="24" class="regular-text code" /><br />
				<input type="reset" class="button-secondary" value="<?php _e('Clear and Reset') ?>" /> or
				<input type="submit" class="button-primary" value="<?php _e('Configure') ?>" />
			</div>
		</div>
		<div id="step2" class="assistant">
			<ol class="checks">
				<li id="prereq_account">account created</li>
				<li id="prereq_payment">we have received your payment for the current period</li>
				<li id="prereq_cdn">CDN is configured</li>
				<li id="prereq_dns">DNS is configured</li>
				<li id="prereq_status">loading from CDN in effect</li>
			</ol>
		</div>
		<div id="step3" class="assistant">
			Status [STATUS IMAGE]
		</div>

		<h3>Advanced Options</h3>
		<p><form method="post" action="">

		<table class="form-table"><tbod>
			<tr valign="top">
				<th scope="row"><label for="ossdl_off_rootrelative">rewrite root-relative refs</label></th>
				<td>
					<input type="checkbox" name="ossdl_off_rootrelative" <?php echo(!!get_option('ossdl_off_rootrelative') ? 'checked="1" ' : '') ?>value="true" class="regular-text code" />
					<span class="description">Check this if you want to have links like <code><em>/</em>wp-content/xyz.png</code> rewritten - i.e. without your blog's domain as prefix.</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="ossdl_off_include_dirs">include dirs</label></th>
				<td>
					<input type="text" name="ossdl_off_include_dirs" value="<?php echo(get_option('ossdl_off_include_dirs')); ?>" size="64" class="regular-text code" />
					<span class="description">Directories to include in static file matching. Use a comma as the delimiter. Default is <code>wp-content, wp-includes</code>, which will be enforced if this field is left empty.</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="ossdl_off_exclude">exclude if substring</label></th>
				<td>
					<input type="text" name="ossdl_off_exclude" value="<?php echo(get_option('ossdl_off_exclude')); ?>" size="64" class="regular-text code" />
					<span class="description">Excludes something from being rewritten if one of the above strings is found in the match. Use a comma as the delimiter. E.g. <code>.php, .flv, .do</code>, always include <code>.php</code> (default).</span>
				</td>
			</tr>
		</tbody></table>
		<input type="hidden" name="action" value="update_ossdl_off" />
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
		</form></p>
	</div><?php
}
