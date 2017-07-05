<?php

namespace BenSun\Smartripper;


class SmartripCard
{
    public $link_id;

    public $title;

    public $serial_number;

    public function __construct($serial_number, $title, $link_id)
    {
        $this->link_id = $link_id;
        $this->title = $title;
        $this->serial_number = $serial_number;
    }


}