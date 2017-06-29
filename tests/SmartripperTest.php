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
		$res = $this->ripper->logIntoWMATA();
		$this->assertTrue((strpos($res, 'WMATA') !== false));
	}

	public function testSetUpWMATACookie() {
		$res = $this->ripper->setupWMATACookie();
		$this->assertEquals(200, $res);
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
