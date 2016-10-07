<?php

namespace CartRabbit\Helper;

use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\Repository\AddressFormatRepository;
use CommerceGuys\Addressing\Repository\CountryRepository;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;

class Address
{
    public static function format($user_address, $isDisplay = false)
    {
        $address = [];
        try {
            self::generalAddressFormat($user_address);

            $addressFormatRepository = new AddressFormatRepository();
            $countryRepository = new CountryRepository();
            $subdivisionRepository = new SubdivisionRepository();
            $formatter = new DefaultFormatter($addressFormatRepository, $countryRepository, $subdivisionRepository);

            $address = new \CommerceGuys\Addressing\Model\Address();
            $address = $address
                ->withRecipient(array_get($user_address, 'name', ''))
                ->withCountryCode(array_get($user_address, 'country', ''))
                ->withOrganization(array_get($user_address, 'organization', ''))
                ->withAdministrativeArea(array_get($user_address, 'administrativeArea', ''))
                ->withPostalCode(array_get($user_address, 'postalCode', ''))
                ->withAddressLine1(array_get($user_address, 'address1', ''))
                ->withAddressLine2(array_get($user_address, 'address2', ''))
                ->withLocality(array_get($user_address, 'locale', ''));

            // Only for Display Purpose.
            if ($isDisplay) {
                $res = [];
                $res['formatted'] = $formatter->format($address);
                $res['raw'] = $user_address;
                return $res;
            }
        } catch (\Exception $e) {

        }
        return $address;
    }

    public static function generalAddressFormat(&$user_address)
    {
        if (is_string($user_address)) {
            $user_address = json_decode($user_address, true);
        }

        if (isset($user_address['zone'])) {
            $user_address['administrativeArea'] = $user_address['zone'];
        }

        if (isset($user_address['city'])) {
            $user_address['locale'] = $user_address['city'];
        }

        if (isset($user_address['fname']) and isset($user_address['lname'])) {
            $user_address['name'] = $user_address['fname'] . ' ' . $user_address['lname'];
        } elseif (isset($user_address['fname'])) {
            $user_address['name'] = $user_address['fname'];
        }
    }

}

