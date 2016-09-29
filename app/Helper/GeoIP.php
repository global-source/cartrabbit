<?php

namespace CartRabbit\Helper;

use GeoIp2\Database\Reader;

class GeoIP
{
    public static function getLocation($ip = null)
    {
        if (is_null($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $result = new \stdClass();
        try {
            if (file_exists(__DIR__ . '/../../resources/assets/mmdb/GeoLite2-City.mmdb')) {
                $reader = new Reader(__DIR__ . '/../../resources/assets/mmdb/GeoLite2-City.mmdb');

                $record = $reader->city($ip);
                $result->city = $record->city->name;
                $result->country = $record->country->isoCode;
                $result->postalCode = $record->postal->code;
                $result->state = $record->mostSpecificSubdivision->isoCode;
                return $result;


            }
        } catch (\Exception $e) {

        }
        return $result;
    }
}