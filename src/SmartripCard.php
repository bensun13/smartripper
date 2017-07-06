<?php

namespace BenSun\Smartripper;


class SmartripCard
{
    private $link_id;
    private $title;
    private $serial_number;
    private $stored_value;

	public static $smartrip_url = 'https://smartrip.wmata.com/Card/';

    public static $card_summary_url = 'CardSummary.aspx?';

    public static $card_usage_url = 'CardUsageReport.aspx?';

    public function __construct($serial_number, $title, $link_id)
    {
        $this->link_id = $link_id;
        $this->title = $title;
        $this->serial_number = $serial_number;
    }

    public function getSummaryUrl() {
    	return self::$smartrip_url . self::$card_summary_url . http_build_query(['card_id' => $this->link_id]);
	}

	public function getUsageUrl() {
		return self::$smartrip_url . self::$card_usage_url . http_build_query(['card_id' => $this->link_id]);
	}

	/**
	 * @return mixed
	 */
	public function getLinkId()
	{
		return $this->link_id;
	}

	/**
	 * @param mixed $link_id
	 */
	public function setLinkId($link_id)
	{
		$this->link_id = $link_id;
	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param mixed $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return mixed
	 */
	public function getSerialNumber()
	{
		return $this->serial_number;
	}

	/**
	 * @param mixed $serial_number
	 */
	public function setSerialNumber($serial_number)
	{
		$this->serial_number = $serial_number;
	}

}