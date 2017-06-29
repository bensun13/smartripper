<?php
/**
 * Created by PhpStorm.
 * User: bsun
 * Date: 6/28/17
 * Time: 10:08 PM
 */

namespace BenSun\Smartripper\Tests;

use \BenSun\Smartripper\Smartripper;

/**
 * @coversDefaultClass \BenSun\Smartripper\Smartripper
 */
class SmartripperTest extends \PHPUnit_Framework_TestCase
{
	public $ripper;

	public function setUp() {
		$this->ripper = new Smartripper('test', 'testpassword');
	}

	public function testGetPassword() {
		$password = $this->ripper->getPassword();
		$this->assertEquals('testpassword', $password);
	}

	public function testLogIntoWMATA() {
		$this->ripper->setUpWMATACookie();
		$res = $this->ripper->logIntoWMATA();
		$this->assertFalse((strpos($res, 'Application Error') !== false));
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
