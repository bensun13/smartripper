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
		$this->ripper = new Smartripper();
	}

	public function testLogintoWMATA() {
		$res = $this->ripper->loginIntoWMATA();
		$this->assertEquals(true, $res);

	}
}
