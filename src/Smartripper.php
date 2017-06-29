<?php
/**
 * Created by PhpStorm.
 * User: bsun
 * Date: 6/28/17
 * Time: 10:06 PM
 */

namespace BenSun\Smartripper;


class Smartripper
{
	private $username;
	private $password;

	public static $login_url = 'https://smartrip.wmata.com/Account/AccountLogin.aspx';

	public static $username_field_name = 'ctl00$MainContent$txtUsername';
	public static $username_field_id = 'ctl00_MainContent_txtUsername';

	public static $password_field_name = 'ctl00$MainContent$txtPassword';
	public static $password_field_id = 'ctl00_MainContent_txtPassword';

	public static $agent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.98 Safari/534.13';

	private $client;
	private $cookie;

	public function __construct($username, $password) {
		$this->setUsername($username);
		$this->setPassword($password);

		$this->client = new \GuzzleHttp\Client(['cookies' => true]);
		$this->cookie = new \GuzzleHttp\Cookie\CookieJar;
	}

	public function setUpWMATACookie() {
		$res = $this->client->request('GET', self::$login_url, ['cookies', $this->cookie]);
		return $res->getStatusCode();
	}

	public function logIntoWMATA() {
		$res = $this->client->get(self::$login_url);

		return $res->getBody()->getContents();
	}

	/**
	 * @return mixed
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param mixed $password
	 */
	public function setPassword($password)
	{
		$this->password = $password;
	}

	/**
	 * @return mixed
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * @param mixed $username
	 */
	public function setUsername($username)
	{
		$this->username = $username;
	}

}