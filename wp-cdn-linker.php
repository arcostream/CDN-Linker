<?php
/*
Plugin Name: Speed Cache
Plugin URI: https://github.com/arcostream
Description: Speeds up your Wordpress site by setting up and configuring a CDN for you.
Version: 1.3.1
*/

/**
 * URL of the CDN Signup Automator, w/o trailing slash.
 *
 * Because someone could spy on the customer knowing the token,
 * the connection must be encrypted ("https://").
 */
$arcostream_automator = 'http://leaf.hurrikane.de:8080';

if ( @include_once('cdn-linker-base.php') ) {
	add_action('template_redirect', 'do_ossdl_off_ob_start');
}

/********** WordPress Administrative ********/

function ossdl_off_activate() {
	add_option('ossdl_off_cdn_url', get_option('siteurl'));
	add_option('ossdl_off_include_dirs', 'wp-content,wp-includes');
	add_option('ossdl_off_exclude', '.php');
	add_option('ossdl_off_rootrelative', '');
	add_option('arcostream_token', random_string(4).'-'.random_string(4).'-'.random_string(4).'-'.random_string(4));
}
register_activation_hook( __FILE__, 'ossdl_off_activate');

function ossdl_off_deactivate() {
	delete_option('ossdl_off_cdn_url');
	delete_option('ossdl_off_include_dirs');
	delete_option('ossdl_off_exclude');
	delete_option('ossdl_off_rootrelative');
	// delete_option('arcostream_token');
}
register_deactivation_hook( __FILE__, 'ossdl_off_deactivate');

/********** WordPress Interface ********/
add_action('admin_menu', 'ossdl_off_menu');
add_action('admin_head', 'admin_register_head');

function ossdl_off_menu() {
	add_options_page('Speed Cache', 'Speed Cache', 8, __FILE__, 'ossdl_off_options');
}

function ossdl_off_get_basedir() {
	return get_option('siteurl') . '/wp-content/plugins/' . basename(dirname(__FILE__));
}

function admin_register_head() {
	$css_url = ossdl_off_get_basedir() . '/backend.css';
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css_url\" />\n";
}

function ossdl_off_options() {
	// handling of the 'advanced settings' input
	if ( isset($_POST['action']) && ( $_POST['action'] == 'advanced' )) {
		update_option('ossdl_off_include_dirs', $_POST['ossdl_off_include_dirs'] == '' ? 'wp-content,wp-includes' : $_POST['ossdl_off_include_dirs']);
		update_option('ossdl_off_exclude', $_POST['ossdl_off_exclude']);
		update_option('ossdl_off_rootrelative', !!$_POST['ossdl_off_rootrelative']);
	} else if ( isset($_POST['action']) && ( $_POST['action'] == 'token' )){
		update_option('arcostream_token', $_POST['arcostream_token']);
		// XXX: fetch configuration settings from mediator
		// XXX: set them here
	}

	global $arcostream_automator;
	?><div class="wrap">
		<h2>Speed Cache</h2>

		<div id="step1">
			<table border="0"><tbody><tr>
			<td valign="top">
				<iframe src="<?php echo($arcostream_automator); ?>/paypal/button?token=<?php echo(get_option('arcostream_token')) ?>">
					You cannot currently subscribe by Paypal.
					Our servers are undergoing maintenance now.
					Please try again later.
				</iframe>
			</td>
			<td valign="middle">
				OR
			</td>
			<td valign="top">
				<form method="post" action="">
				<label for="arcostream_custid">Already a Subscriber?</label><br />
				<input type="text" name="arcostream_custid" id="arcostream_token" value="your secret token" size="24" class="regular-text code" /><br />
				<input type="reset" class="button-secondary" value="<?php _e('Clear and Reset') ?>" /> or
				<input type="submit" class="button-primary" value="<?php _e('Configure') ?>" />
				<input type="hidden" name="action" value="token" />
				</form>
			</td>
			</tr></tbody></table>
		</div>
		<div id="step2">
			<ol class="checks">
				<li id="prereq_account" class="unknown">account created</li>
				<li id="prereq_payment" class="unknown">payment for the current period</li>
				<li id="prereq_cdn" class="unknown">CDN is configured</li>
				<li id="prereq_dns" class="unknown">DNS is configured</li>
				<li id="prereq_status" class="unknown">loading from CDN in effect</li>
			</ol>
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
		<input type="hidden" name="action" value="advanced" />
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
		</form></p>
	</div><?php
}
