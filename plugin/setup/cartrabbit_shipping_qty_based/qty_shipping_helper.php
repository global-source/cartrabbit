<?php

/**
 * Created by PhpStorm.
 * User: flycart
 * Date: 21/7/16
 * Time: 6:35 PM
 */
class Helper
{
    static function processView($path, $data)
    {
        ob_start();
        $config = $data;
        include($path);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}