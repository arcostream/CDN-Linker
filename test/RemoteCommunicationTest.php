<?php
/*
 * These tests require that an Automator copy runs locally (on 127.0.0.1:8080).
 *
 * Run with: phpunit test-upstream.php
 */

include('cdn-linker-upstream.php');

class CDNUpstreamTest extends PHPUnit_Framework_TestCase
{
	/** Instance of TokenData - not set by default! */
	var $td = null;
	/** Address of the Automator mockup. */
	var $remote_url = 'http://127.0.0.1:8080';
	/** Token for testing. */
	var $test_token = 'TEST-TEST-TEST-TEST';

	public function testLoadingfromRemote() {
		foreach(array(true, false) as $force_fopen) {
			$str = get_from_remote($this->remote_url.'/status/'.$this->test_token, $force_fopen);
			$this->assertTrue(!!$str);
			$this->assertContains('status', $str);

			$str = get_from_remote($this->remote_url.'/will-yield-404', $force_fopen);
			$this->assertFalse(!!$str);
		}
	}

	/**
	 * @depends testLoadingfromRemote
	 */
	public function testTokenDataFetching() {
		$this->td = new TokenData($this->test_token, $this->remote_url);

		$this->assertEquals($this->remote_url, $this->td->upstream);
		$this->assertEquals($this->test_token, $this->td->token);
		$this->assertEquals('http://cdn.test.local', $this->td->cdn_url);
	}

	/**
	 * @depends testLoadingfromRemote
	 */
	public function testBadAdressReaction() {
		$this->td = new TokenData($this->test_token, str_replace('8080', '8081', $this->remote_url));

		$this->assertFalse($this->td->exists);
	}

}
