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
	private $parser;

	private $view_state = null;
	private $event_validation = null;
	private $csrf_token = null;

	public function __construct($username, $password) {
		$this->setUsername($username);
		$this->setPassword($password);

		$this->client = new \GuzzleHttp\Client(['cookies' => true]);
		$this->cookie = new \GuzzleHttp\Cookie\CookieJar;
		$this->parser = new \Sunra\PhpSimple\HtmlDomParser;
	}

	public function setUpWMATACookie()	{
		$res = $this->client->request('GET', self::$login_url, ['cookies', $this->cookie]);

		$doc = new \DOMDocument;
		$doc->loadHTML($res->getBody()->getContents());

		$this->setViewState($doc->getElementById('__VIEWSTATE')->getAttribute('value'));
		$this->setEventValidation($doc->getElementById('__EVENTVALIDATION')->getAttribute('value'));
		$this->setCsrfToken($doc->getElementById('__CSRFTOKEN')->getAttribute('value'));

		return $res->getStatusCode();
	}

	public function logIntoWMATA() {

		 $headers = ['referer' => self::$login_url];

		$data = ['__CSRFTOKEN'=>$this->getCsrfToken(),
			'__EVENTTARGET'=>'',
			'__EVENTARGUMENT'=>'',
			'__VIEWSTATE' => $this->getViewState(),
			'__EVENTVALIDATION' => $this->getEventValidation(),
			self::$username_field_name => $this->getUsername(),
			self::$password_field_name => $this->getPassword(),
			'ctl00$MainContent$btnSubmit.x'=>'74',
			'ctl00$MainContent$btnSubmit.y'=>'14'];

		$args = ['headers' => $headers,
			'body' => http_build_query($data,'','&'),
			'cookies' => $this->cookie];

		$res = $this->client->post(self::$login_url, $args);
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

	/**
	 * @return mixed
	 */
	public function getViewState()
	{
		return $this->view_state;
	}

	/**
	 * @param mixed $view_state
	 */
	public function setViewState($view_state)
	{
		$this->view_state = $view_state;
	}

	/**
	 * @return mixed
	 */
	public function getEventValidation()
	{
		return $this->event_validation;
	}

	/**
	 * @param mixed $event_validation
	 */
	public function setEventValidation($event_validation)
	{
		$this->event_validation = $event_validation;
	}

	/**
	 * @return null
	 */
	public function getCsrfToken()
	{
		return $this->csrf_token;
	}

	/**
	 * @param null $csrf_token
	 */
	public function setCsrfToken($csrf_token)
	{
		$this->csrf_token = $csrf_token;
	}


}