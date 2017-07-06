<?php
/**
 * Created by PhpStorm.
 * User: bsun
 * Date: 7/5/17
 * Time: 11:33 PM
 */

namespace BenSun\Smartripper;

/**
 * @coversDefaultClass \BenSun\Smartripper\SmartripCard
 */
class SmartripCardTest extends \PHPUnit_Framework_TestCase
{
	public $card;

	public function setUp() {
		$this->card = new SmartripCard('test_serial_number', 'Test Title', 'test_link_id');
	}

	public function testGetSerialNumber() {
		$serial_number = $this->card->getSerialNumber();
		$this->assertEquals('test_serial_number', $serial_number);
	}

	public function testGetTitle() {
		$title = $this->card->getTitle();
		$this->assertEquals('Test Title', $title);
	}

	public function testGetLinkId() {
		$link_id = $this->card->getLinkId();
		$this->assertEquals('test_link_id', $link_id);
	}

	public function testSetSerialNumber() {
		$serial_number = $this->card->getSerialNumber();
		$this->assertEquals('test_serial_number', $serial_number);

		$this->card->setSerialNumber('nottestanymore');
		$serial_number = $this->card->getSerialNumber();
		$this->assertFalse($serial_number == 'test');
		$this->assertEquals('nottestanymore', $serial_number);
	}

	public function testSetTitle() {
		$title = $this->card->getTitle();
		$this->assertEquals('Test Title', $title);

		$this->card->setTitle('nottestanymore');
		$title = $this->card->getTitle();
		$this->assertFalse($title == 'test');
		$this->assertEquals('nottestanymore', $title);
	}

	public function testSetLinkId() {
		$link_id = $this->card->getLinkId();
		$this->assertEquals('test_link_id', $link_id);

		$this->card->setLinkId('nottestanymore');
		$link_id = $this->card->getLinkId();
		$this->assertFalse($link_id == 'test');
		$this->assertEquals('nottestanymore', $link_id);
	}

}
