<?php

namespace CartRabbit\Helper;

class EventManager
{
    /**
     * To Perform Apply Filter Event
     * @param $eventName
     * @param $arguments
     */
    function applyFilter($eventName, $arguments)
    {
        add_filter($eventName, $arguments);
    }
}

