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

	public static $base_uri = 'https://smartrip.wmata.com';
	public static $login_url = '/Account/AccountLogin.aspx';

	public static $username_field_name = 'ctl00$ctl00$MainContent$MainContent$txtUsername';
	public static $password_field_name = 'ctl00$ctl00$MainContent$MainContent$txtPassword';

	public static $agent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.98 Safari/534.13';

    public $is_logged_in = false;

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

		$this->client = new \GuzzleHttp\Client(['cookies' => true, 'base_uri' => self::$base_uri]);
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

	    if (!$this->cookie->count()) {
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

        if (strpos($html, 'Application Error') !== false) {
            //return an error as login has failed
            echo "Failed Login";
            return [];
        }

        $doc = new \DOMDocument;
        $doc->loadHTML($html);
        $finder = new \DomXPath($doc);
        $classname="cardInfo";
        $nodes = $finder->query("//li[contains(@class, '$classname')]/a");

        $cards = [];

        if ($nodes->length) {

            $this->is_logged_in = true;

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

	function fetchUsageData(SmartripCard $card, $month, $args) {

	    if (!$this->isLoggedIn()) {
	        $this->logIntoWMATA();
        }

        if ($this->isLoggedIn()) {

            $url_parts = parse_url($card->getSummaryUrl());
            $url = $card->getUsageUrl() . '&back_url=' . urlencode($url_parts['path'] . '?' . $url_parts['query']);

            $res = $this->client->get($url, ['cookies' => $this->cookie]);
            $html = $res->getBody()->getContents();

            $doc = new \DOMDocument;
            $doc->loadHTML($html);

            $headers = [
                'referer' => self::$base_uri . $url,
            ];

            $data = [
                '__CSRFTOKEN' => $this->getCsrfToken($doc),
                '__EVENTTARGET' => '',
                '__EVENTARGUMENT' => '',
                '__VIEWSTATE' => $this->getViewState($doc),
                '__VIEWSTATEGENERATOR' => $this->getViewStateGenerator($doc),
                '__EVENTVALIDATION' => $this->getEventValidation($doc),
                'ctl00$ctl00$MainContent$MainContent$grpPeriod' => 'rbByMonth',
                'ctl00$ctl00$MainContent$MainContent$ddlMonth' => $month,
                'ctl00$ctl00$MainContent$MainContent$btnSubmit.x' => 47,
                'ctl00$ctl00$MainContent$MainContent$btnSubmit.y' => 8
                ];

            $args = [
                'headers' => $headers,
                'verify' => true,
                'allow_redirects' => ['track_redirects' => true],
                'form_params' => $data,
                'cookies' => $this->cookie
            ];

            $res = $this->client->post($url, $args);
            $response_html = $res->getBody()->getContents();

            if (strpos($response_html, "Application Error") !== false) {
                echo "Failed to fetch token<br>";

            } else {
                echo $response_html;
            }

            var_dump($res->getHeader(\GuzzleHttp\RedirectMiddleware::HISTORY_HEADER));
        }

	}

    public function isLoggedIn() {
	    return $this->is_logged_in;
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
	public function getViewState($doc = null)
	{
        if (!is_null($doc) && $doc instanceof \DOMDocument) {
            $this->setCsrfToken($doc->getElementById('__VIEWSTATE')->getAttribute('value'));
        }
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
	public function getEventValidation($doc = null)
	{
        if (!is_null($doc) && $doc instanceof \DOMDocument) {
            $this->setCsrfToken($doc->getElementById('__EVENTVALIDATION')->getAttribute('value'));
        }
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
	public function getCsrfToken($doc = null)
	{
        if (!is_null($doc) && $doc instanceof \DOMDocument) {
            $this->setCsrfToken($doc->getElementById('__CSRFTOKEN')->getAttribute('value'));
        }
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
	public function getViewStateGenerator($doc = null)
	{
        if (!is_null($doc) && $doc instanceof \DOMDocument) {
            $this->setCsrfToken($doc->getElementById('__VIEWSTATEGENERATOR')->getAttribute('value'));
        }
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