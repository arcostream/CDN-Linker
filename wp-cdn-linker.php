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

include_once('cdn-linker-upstream.php');

/********** WordPress Administrative ********/

function ossdl_off_update_data_from_upstream() {
	global $arcostream_automator;
	$data = new TokenData(get_option('arcostream_token'), $arcostream_automator);
	if (!is_null($data->cdn_url)) {
		update_option('ossdl_off_cdn_url', $data->cdn_url);
		update_option('arcostream_account_status', $data->status_account);
	}
	return $data;
}

function ossdl_off_activate() {
	add_option('ossdl_off_cdn_url', get_option('siteurl'));
	add_option('ossdl_off_include_dirs', 'wp-content,wp-includes');
	add_option('ossdl_off_exclude', '.php');
	add_option('ossdl_off_rootrelative', '');
	add_option('arcostream_account_status', 'unknown');
	if (!get_option('arcostream_token')) {
		add_option('arcostream_token', generate_random_token());
	} else {
		ossdl_off_update_data_from_upstream();
	}
}
register_activation_hook( __FILE__, 'ossdl_off_activate');

function ossdl_off_deactivate() {
	delete_option('ossdl_off_cdn_url');
	delete_option('ossdl_off_include_dirs');
	delete_option('ossdl_off_exclude');
	delete_option('ossdl_off_rootrelative');
	if (get_option('arcostream_account_status') == 'unknown') {
		delete_option('arcostream_token');
	}
	delete_option('arcostream_account_status');
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

function ossdl_off_class_for_status($status) {
	if (is_null($status)) {
		return 'unknown';
	} else if ($status == true || $status == 'ok') {
		return 'success';
	} else {
		return 'failed';
	}
}

function ossdl_off_options() {
	$token_data = false;
	// handling of the 'advanced settings' input
	if ( isset($_POST['action']) ) switch ($_POST['action']) {
	case 'advanced':
		update_option('ossdl_off_include_dirs', $_POST['ossdl_off_include_dirs'] == '' ? 'wp-content,wp-includes' : $_POST['ossdl_off_include_dirs']);
		update_option('ossdl_off_exclude', $_POST['ossdl_off_exclude']);
		update_option('ossdl_off_rootrelative', !!$_POST['ossdl_off_rootrelative']);
		break;
	case 'clear token':
		update_option('arcostream_token', generate_random_token());
		update_option('arcostream_account_status', 'unknown');
		break;
	case 'new token':
		update_option('arcostream_token', $_POST['arcostream_token']);
		break;
	}

	if (!$token_data) {
		$token_data = ossdl_off_update_data_from_upstream();
	}
	global $arcostream_automator;
	?><div class="wrap">
		<h2>Speed Cache</h2>

		<div id="step1">
		<?php if (!$token_data->exists) { ?>
			<table border="0"><tbody><tr>
			<td valign="top">
				<iframe src="<?php echo($arcostream_automator); ?>/paypal/button?token=<?php echo(get_option('arcostream_token')) ?>&siteurl=<?php echo(urlencode(get_option('siteurl'))); ?>">
					You cannot currently subscribe by Paypal.
					Our servers are undergoing maintenance now.
					Please try again later.
				</iframe>
			</td>
			<td valign="middle">
				OR
			</td>
		<?php } ?>
			<td valign="top">
				<form method="post" action="">
				<?php if (get_option('arcostream_account_status') == 'ok') { ?>
					<label for="arcostream_custid">Your site identifier:</label><br />
					<input type="text" name="arcostream_custid" id="arcostream_token" value="<?php echo($token_data->token); ?>" disabled="1" size="24" class="regular-text code" /><br />
					<input type="hidden" name="action" value="clear token" />
					<input type="submit" class="button-secondary" value="<?php _e('Clear and Unconfigure') ?>" />
				<?php } else { ?>
					<label for="arcostream_custid">Already a Subscriber?</label><br />
					<input type="text" name="arcostream_token" id="arcostream_token" value="your site identifier" size="24" class="regular-text code" /><br />
					<input type="hidden" name="action" value="new token" />
					<input type="reset" class="button-secondary" value="<?php _e('Clear') ?>" /> &mdash;
					<input type="submit" class="button-primary" value="<?php _e('Configure') ?>" />
				<?php } ?><br />
				</form>
			</td>
			</tr></tbody></table>
		</div>
		<div id="step2">
			<ol class="checks">
			<?php if (!$token_data->exists) { ?>
				<li id="prereq_account" class="failed">Your account has been created.</li>
				<li id="prereq_payment" class="unknown">We have received payment for the current period.</li>
				<li id="prereq_cdn" class="unknown">CDN is configured.</li>
				<li id="prereq_dns" class="unknown">DNS is configured.</li>
				<li id="prereq_status" class="unknown">Loading of static data from CDN is in effect.</li>
			<?php } else /* account exists */ { ?>
				<li id="prereq_account" class="<?php echo(ossdl_off_class_for_status($token_data->exists)); ?>">
					Your account status is <code><q><?php echo(get_option('arcostream_account_status')); ?></q></code>.
				</li>
				<li id="prereq_payment" class="<?php echo(ossdl_off_class_for_status($token_data->status_account)); ?>">
					The subscription is paid up to <?php echo(substr($token_data->paid_including, 0, 10).' '.$token_data->paid_timezone); ?>.
					<?php if ($token_data->last_period) { ?>Your subscription will expire after that.<?php } ?>
				</li>
				<li id="prereq_cdn" class="<?php echo(ossdl_off_class_for_status($token_data->status_cdn)); ?>">CDN is configured.</li>
				<li id="prereq_dns" class="<?php echo(ossdl_off_class_for_status($token_data->status_dns)); ?>">
					DNS is configured and your CDN available through <code><?php echo(str_replace('http://', '', $token_data->cdn_url)); ?></code>.
				</li>
				<li id="prereq_status" class="<?php
					echo(ossdl_off_class_for_status(get_option('arcostream_account_status') == 'ok' && $token_data->status_cdn && $token_data->status_dns));
					?>">Loading of static data from CDN is in effect.</li>
			<?php } ?>
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
