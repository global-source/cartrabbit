<?php

namespace CartRabbit\Helper;

use CommerceGuys\Tax\Repository\TaxTypeRepository;

/**
 * Class Tax For Performing Tax based Operations
 * @package CartRabbit\Helper
 */
class Tax
{

    /**
     * @var $tax_id integer for Manage Tax ID
     */
    private $tax_id;

    /**
     * @var $rate_id integer for Manage Rate ID of Tax Profile
     */
    private $rate_id;

    /**
     * @var $amount_id integer for Manage Tax ID of Rate Profile
     */
    private $amount_id;

    /**
     * @var $tax_file string for notify the current tax file to be used
     */
    private $tax_file;

    /**
     * @var $JSON_File_Path string to keep the path for the JSON files Directory
     */
    private $JSON_File_Path = __DIR__ . '/../../vendor/commerceguys/tax/resources/';

    /**
     * @var $file string for keep the complete path of the current tax/zone file to be used
     */
    private $file;

    /**
     * To Get All available tax profiles in the Tax Repository
     *
     * @return array of retrieved tax profiles
     */
    public function getTaxes()
    {
        $tax = new TaxTypeRepository();
        foreach ($tax->getAll() as $id => $tax) {
            $taxes[$id]['id'] = $tax->getID();
            $taxes[$id]['name'] = $tax->getName();
            $taxes[$id]['genericLabel'] = $tax->getGenericLabel();
            $taxes[$id]['roundingMode'] = $tax->getRoundingMode();
            $taxes[$id]['zone'] = count($tax->getZone());
            $taxes[$id]['tag'] = $tax->getTag();
        }
        return $taxes;
    }

    /**
     * To Get Tax Profile by it's ID
     *
     * @param integer $tax_id ID of the Tax or Zone Profile
     * @param bool $isZone to check the id belongs to Zone or Tax
     * @return array of Tax Profile or Zone Profile
     */
    public function getTaxesByID($tax_id, $isZone = false)
    {
        $type = 'tax_type';
        if ($isZone) $type = 'zone';
        $taxRepo = new TaxTypeRepository();
        $taxProfile = array();
        if ($tax_id) {
            if ((count($taxRepo->get($tax_id)))) {
                $taxProfile = $this->readTaxJSON($tax_id, $type);
            }
        }
        return $taxProfile;
    }

    /**
     * To Check the availablity of the Tax Classes
     *
     * @param array $tax of Tax Profile data
     * @return bool True | False [Available or Not]
     */
    public function checkAvailablity($tax, $rate_id)
    {
        $status = true;
        foreach ($tax['rates'] as $rate) {
            if ($rate['id'] == $rate_id) {
                foreach ($rate['amounts'] as $amount) {
                    if (strpos($amount['id'], date('Y')) !== false) {
                        $status = false;
                    }
                }
            }
        }
        return $status;
    }

    /**
     * To Read tax Profile by the Given Tax ID and the Type of Profile
     *
     * @param integer $tax_id ID of the Tax or Zone Profile
     * @param string $type Tax or Zone
     * @return array of retrieved Profile details
     */
    public function readTaxJSON($tax_id, $type)
    {
        $this->file = $this->JSON_File_Path . $type . '/' . $tax_id . '.json';
        $file = file_get_contents($this->file);
        $this->tax_file = (!empty($file)) ? json_decode($file, true) : array();
//        dd($this->tax_file);
        return $this->tax_file;
    }

    public function editTaxProfile($tax_id, $data)
    {
        $tax = $this->readTaxJSON($tax_id, 'tax_type');
        foreach ($data as $key => $value) {
            $tax[$key] = $value;
        }
        $this->updateProfile($tax);
    }

    /**
     * To Save tax Rates and re-arrange the Default status
     *
     * @param $tax_id
     * @param $data
     */
    public function saveTaxRate($tax_id, $data)
    {
        $newTaxRate['id'] = $tax_id . '_' . strtolower($data['name']);
        $newTaxRate['id'] = str_replace(' ', '_', $newTaxRate['id']);
        $newTaxRate['name'] = $data['name'];
        $newTaxRate['default'] = ($data['default'] == 'yes') ? true : false;
        $newTaxRate['amounts'] = array();
        $tax = $this->readTaxJSON($tax_id, 'tax_type');
        if ($newTaxRate['default']) {
            foreach ($tax['rates'] as &$rate) {
                if (isset($rate['default'])) {
                    if ($newTaxRate['name'] != $rate['name']) {
                        $rate['default'] = false;
                    }
                }
            }
        }
        array_push($tax['rates'], $newTaxRate);
        $this->updateProfile($tax);
    }

    /**
     * To update the Tax Rates
     *
     * @param $tax_id
     * @param $data
     */
    public function updateDefaultStatus($tax_id, $data)
    {
        $tax = $this->readTaxJSON($tax_id, 'tax_type');

        /** Directly Opposite to the Current State */
        $setState = !(bool)$data['default_status'];

        /** This Validation is used to eliminate all of the rates as "default" */
        if ($setState == true) $setState = false;

        foreach ($tax['rates'] as &$rate) {
            if ($rate['id'] == $data['rate_id']) {
                $rate['default'] = (bool)$data['default_status'];
            } else {
                $rate['default'] = $setState;
            }
        }
        $this->updateProfile($tax);
    }

    public function removeTaxRates($tax_id, $data)
    {
        $tax = $this->readTaxJSON($tax_id, 'tax_type');

        foreach ($tax['rates'] as $key => $rate) {
            if ($rate['id'] == $data['rate_id']) {
                unset($tax['rates'][$key]);
            }
        }
        $this->updateProfile($tax);
    }

    /**
     * @param $tax_id
     * @param $data
     */
    public function saveTaxZone($tax_id, $data)
    {
        $zone = $this->readTaxJSON($tax_id, 'zone');
        $tax_zone_id = count($zone['members']);
        $newTaxZone['type'] = $data['type'];
        $newTaxZone['id'] = $tax_id . '_' . $tax_zone_id;
        $newTaxZone['name'] = $data['name'];
        $newTaxZone['country_code'] = $data['country_code'];
        $newTaxZone['included_postal_codes'] = (isset($data['included_postal_codes'])) ? $data['included_postal_codes'] : '';
        $newTaxZone['excluded_postal_codes'] = (isset($data['excluded_postal_codes'])) ? $data['excluded_postal_codes'] : '';

        array_push($zone['members'], $newTaxZone);
        $this->updateProfile($zone);
    }

    public function editTaxZone($tax_id, $data)
    {
        $zone = $this->readTaxJSON($tax_id, 'zone');
        foreach ($zone['members'] as &$member) {
            if ($member['id'] == $data['zone_id']) {
                foreach ($data as $key => $value) {
                    $member[$key] = $value;
                }
            }
        }
        $this->updateProfile($zone);
    }

    public function removeTaxZone($tax_id, $data)
    {
        $zone = $this->readTaxJSON($tax_id, 'zone');
        foreach ($zone['members'] as $key => $member) {
            if ($member['id'] == $data['zone_id']) {
                unset($zone['members'][$key]);

            }
        }

        $this->updateProfile($zone);
    }

    /**
     * To Save Tax Profile's Rates
     *
     * @param array $data of modified tax rate data's
     */
    public function saveTaxAmount($data)
    {
        if (isset($data['amount'])) {

            $this->tax_id = $data['tax_id'];
            $this->rate_id = $data['rate_id'];
            $this->amount_id = $data['amount_id'];

            $newRate['id'] = $data['amount_id'];
            $newRate['amount'] = $data['amount'];
            $newRate['start_date'] = $data['start_date'];
            $newRate['end_date'] = $data['end_date'];

            $tax = $this->readTaxJSON($data['tax_id'], 'tax_type');
            $this->updateProfile($this->ProcessAmountProfile($tax, $newRate));
        }
    }

    /**
     * To Remove Tax Rate
     *
     * @param array $data to remove from Tax Rate Profile
     */
    public function removeTaxAmount($data)
    {
        $this->tax_id = $data['tax_id'];
        $this->rate_id = $data['rate_id'];
        $this->amount_id = $data['amount_id'];

        if (isset($data['amount_id'])) {
            $tax = $this->readTaxJSON($data['tax_id'], 'tax_type');
            $this->updateProfile($this->ProcessAmountProfile($tax, $data, $isDelete = true));
        }
    }

    /**
     * To Process the Amount Profile for fetching the amount profile from rate profile,
     * fetching rate profile from tax profile
     *
     * @param array $tax the raw content of tax profile
     * @param array $data the modified content of amount profile
     * @return array of processed tax profile data's
     */
    public function ProcessAmountProfile($tax, $data, $isDelete = false)
    {
        $isAvail = false;
        $amountProfile = null;
        if ($tax['zone'] == $this->tax_id) {
            foreach ($tax['rates'] as &$rate) {
                if ($rate['id'] == $this->rate_id) {
                    if (!$isDelete) {
                        foreach ($rate['amounts'] as &$amount) {
                            if ($amount['id'] == $this->amount_id) {
                                $amount['amount'] = (!empty($data['amount'])) ? (float)$data['amount'] : (float)$amount['amount'];
                                $amount['start_date'] = (!empty($data['start_date'])) ? $data['start_date'] : $amount['start_date'];
                                $amount['end_date'] = (!empty($data['end_date'])) ? $data['end_date'] : $amount['end_date'];
                                $isAvail = true;
                            }
                        }
                        if ($isAvail == false) {
                            array_push($rate['amounts'], $data);
                        }
                    } else {
                        foreach ($rate['amounts'] as $key => &$amount) {
                            if ($amount['id'] == $this->amount_id) {
                                unset($rate['amounts'][$key]);
                            }
                        }
                    }
                }
            }
        }
        return $tax;
    }

    /**
     * To Update the Amount Profile
     *
     * @param array $tax of processed amount profile data
     */
    protected function updateProfile($tax)
    {
        if (!empty($tax) or !is_null($tax) or isset($tax)) {
            file_put_contents($this->file, json_encode($tax));
        }
    }
}