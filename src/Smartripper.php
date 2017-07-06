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

	public static $username_field_name = 'ctl00$ctl00$MainContent$MainContent$txtUsername';
	public static $password_field_name = 'ctl00$ctl00$MainContent$MainContent$txtPassword';

	public static $agent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.98 Safari/534.13';

	public $client;

	private $cookie = null;
	private $parser;

	private $view_state = null;
	private $event_validation = null;
	private $csrf_token = null;
	private $view_state_generator = null;

	public function __construct($username, $password) {
		$this->setUsername($username);
		$this->setPassword($password);

		$this->client = new \GuzzleHttp\Client(['cookies' => true]);
		$this->cookie = new \GuzzleHttp\Cookie\CookieJar();
		$this->parser = new \Sunra\PhpSimple\HtmlDomParser;
	}

	public function setUpWMATACookie()	{
		$res = $this->client->request('GET', self::$login_url, ['cookies' => $this->cookie]);

		$html = $res->getBody()->getContents();

        $doc = new \DOMDocument;
        libxml_use_internal_errors(true);
		$doc->loadHTML($html);

		$this->setViewState($doc->getElementById('__VIEWSTATE')->getAttribute('value'));
		$this->setEventValidation($doc->getElementById('__EVENTVALIDATION')->getAttribute('value'));
		$this->setCsrfToken($doc->getElementById('__CSRFTOKEN')->getAttribute('value'));
		$this->setViewStateGenerator($doc->getElementById('__VIEWSTATEGENERATOR')->getAttribute('value'));
		return $res->getStatusCode();
	}

	public function logIntoWMATA() {

	    if (is_null($this->cookie)) {
            $this->setUpWMATACookie();
        }

		$headers = [
			'referer' => self::$login_url,
			'User-Agent' => self::$agent
		];

		$data = [
			'__CSRFTOKEN'=>$this->getCsrfToken(),
			'__EVENTTARGET'=>'',
			'__EVENTARGUMENT'=>'',
			'__VIEWSTATE' => $this->getViewState(),
			'__VIEWSTATEGENERATOR' => $this->getViewStateGenerator(),
			'__EVENTVALIDATION' => $this->getEventValidation(),
			self::$username_field_name => $this->getUsername(),
			self::$password_field_name => $this->getPassword(),
			'ctl00$ctl00$MainContent$MainContent$btnSubmit.x'=>'73',
			'ctl00$ctl00$MainContent$MainContent$btnSubmit.y'=>'18'
		];

		$args = [
            'headers' => $headers,
			'referer' => true,
			'verify' => false,
			'allow_redirects' => true,
			'form_params' => $data,
			'cookies' => $this->cookie
        ];

		$res = $this->client->post(self::$login_url, $args);
		$html = $res->getBody()->getContents();

        if (strpos($res, 'Application Error') == false) {
            //return an error as login has failed
        }

        $doc = new \DOMDocument;
        $doc->loadHTML($html);
        $finder = new \DomXPath($doc);
        $classname="cardInfo";
        $nodes = $finder->query("//li[contains(@class, '$classname')]/a");

        $cards = [];

        if ($nodes->length) {
            foreach ($nodes as $elem) {

                $href = $elem->getAttribute('href');
                $result = parse_url($href, PHP_URL_QUERY);
                parse_str($result, $result);
                $card_link_id = $result['card_id'];

                $context = $elem->textContent;
                $bracket_pos = strrpos($context, '(');
                $card_serial_number = substr($context, ($bracket_pos + 1), (strlen($context) - $bracket_pos - 2));

                $card_title = trim(substr($context, 0, $bracket_pos));

                $cards[] = new SmartripCard($card_serial_number, $card_title, $card_link_id);
            }

        }
        return $cards;
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

	/**
	 * @return null
	 */
	public function getViewStateGenerator()
	{
		return $this->view_state_generator;
	}

	/**
	 * @param null $view_state_generator
	 */
	public function setViewStateGenerator($view_state_generator)
	{
		$this->view_state_generator = $view_state_generator;
	}


}