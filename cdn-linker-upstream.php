<?php

if (!function_exists('json_decode')) {
	// Using JSON from WP's TinyMCE we don't have to rely on the existence of PHP's "json_decode".
	include_once(ABSPATH."/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php");
}

/**
 * Represents a customer/token combination.
 *
 * Use this to get account validity and settings from the CDN Signup Automator.
 */
class TokenData
{
	/** String: the customer's token for this installation */
	var $token		= null;
	/** String: URL of the Automator, including protocoll prefix (such as 'http://') w/o trailing slash*/
	var $upstream		= null;
	/** Boolean: true if the token has data on the Automator's side. */
	var $exists		= null;

	// settings, if set by the Automator (else NULL)

	/** Boolean: true if a CDN bucket has been create for this customer/token */
	var status_cdn		= null;
	/** Boolean: true if DNS CNAME has been created */
	var status_dns		= null;
	/** String: status of the account - 'ok', 'cancelled' */
	var status_account	= null;
	/** String: the DNS CNAME - also known as $cdn_url */
	var cdn_url		= null;
	/** ISO 8859-1 formatted datetime */
	var paid_including	= null;
	/** timezone of 'paid_including' */
	var paid_timezone	= null;

	function __construct($token, $automator_url) {
		$this->token = $token;
		$this->upstream = $automator_url;
		$this->exists = false;
		$this->populate();
	}

	// XXX: raise exception if something goes wrong or if the token is invalid
	protected function get_data_from_upstream() {
		$raw = file_get_contents($upstream);

		if (function_exists('json_decode')) {
			return json_decode($raw);
		} else {
			$json_obj = new Moxiecode_JSON();
			return $json_obj->decode($raw);
		}
	}

	// XXX: handle exceptions, set data accordingly
	protected function populate() {
		$j = $this->get_data_from_upstream();

		$this->exists = true;
		if ( isset($j['cdn_url']) ) {
			$this->cdn_url = $j['cdn_url'];
		}

		if ( isset($j['status']) ) {
			$this->status_cdn = $j['status']['cdn'];
			$this->status_dns = $j['status']['dns'];
			$this->status_dns = $j['status']['account'];
		} else {
			$this->status_cdn = false;
			$this->status_dns = false;
			$this->status_account = 'unknown';
		}

		if ( isset($j['paid']) ) {
			$this->paid_including = $j['paid']['including'];
			$this->paid_timezone = $j['paid']['timezone'];
		} else {
			$this->paid_including = false;
		}
	}

}

