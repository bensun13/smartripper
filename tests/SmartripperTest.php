<?php

namespace BenSun\Smartripper\Tests;

use \BenSun\Smartripper;

/**
 * @coversDefaultClass \BenSun\Smartripper\Smartripper
 */
class SmartripperTest extends \PHPUnit_Framework_TestCase
{
	public $ripper;

	public function setUp() {
		$this->ripper = new Smartripper\Smartripper('test', 'testpassword');
	}

	public function testGetPassword() {
		$password = $this->ripper->getPassword();
		$this->assertEquals('testpassword', $password);
	}

	public function testLogIntoWMATA() {

		$this->ripper->setUpWMATACookie();

		$body = "<li class=\"cardInfo \"><a href=\"../Card/CardSummary.aspx?card_id=test_card_id\">Test Title (test_serial_number)</a></li>";

		$mock_client = $this->getMockBuilder('\GuzzleHttp\Client')
			->setMethods(['post'])
			->getMock();

		$response = new \GuzzleHttp\Psr7\Response('200', [], $body);

		$mock_client->method('post')->with($this->ripper::$login_url, $this->anything())->willReturn($response);

		$this->ripper->client = $mock_client;

		$res = $this->ripper->logIntoWMATA();

		$expected = [new Smartripper\SmartripCard('test_serial_number', 'Test Title', 'test_card_id')];

        $this->assertEquals($expected, $res);
	}

	public function testSetUpWMATACookie() {
		$this->assertTrue(is_null($this->ripper->getEventValidation()));
		$this->assertTrue(is_null($this->ripper->getViewState()));

		$res = $this->ripper->setupWMATACookie();
		$this->assertEquals(200, $res);

		$this->assertTrue(!is_null($this->ripper->getEventValidation()));
		$this->assertTrue(!is_null($this->ripper->getViewState()));
	}

	public function testGetUsername() {
		$username = $this->ripper->getUsername();
		$this->assertEquals('test', $username);
	}

	public function testSetUsername() {
		$username = $this->ripper->getUsername();
		$this->assertEquals('test', $username);

		$this->ripper->setUsername('nottestanymore');
		$username = $this->ripper->getUsername();
		$this->assertFalse($username == 'test');
		$this->assertEquals('nottestanymore', $username);
	}

	public function testSetPassword() {
		$password = $this->ripper->getPassword();
		$this->assertEquals('testpassword', $password);

		$this->ripper->setPassword('nottestanymore');
		$password = $this->ripper->getPassword();
		$this->assertFalse($password == 'testpassword');
		$this->assertEquals('nottestanymore', $password);
	}
}
