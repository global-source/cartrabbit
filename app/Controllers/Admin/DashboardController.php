<?php

namespace CartRabbit\Controllers\Admin;

use Herbert\Framework\Http;
use CartRabbit\Controllers\BaseController;
use CartRabbit\Helper;
use CartRabbit\Helper\Currency;
use CartRabbit\Helper\Util;
use CartRabbit\Models\Dashboard;
use CartRabbit\Models\Settings;
use CartRabbit\Models\Setup;
use CommerceGuys\Intl\Country\CountryRepository;
use CommerceGuys\Intl\Currency\CurrencyRepository;

/**
 * Class Admin
 *
 * @package CartRabbit\Controllers
 */
class DashboardController extends BaseController
{

    /**
     * DashboardController constructor.
     *
     * To Initiate the On Load Schemas to ensure the stability of "CartRabbit"
     */
    public function __construct()
    {
        if ((new Setup())->isNewBoot() != false) {
            $html = $this->initSetupWizard();
            return $html;
        }

        //Call Migration to ensure that the Table's and its Structure are Up-to-Date
        Util::migration();
    }

    /**
     * To Display the Dashboard with Schema Checker Results
     *
     * @return \CartRabbit\Controllers\display
     */
    public function getDashboard()
    {
        if ((new Setup())->isNewBoot() != false) {
            $html = (new DashboardController())->initSetupWizard();
        } else {
            $pages = Settings::getDisplaySetup($isDashboard = true);
            $totalBuy = 0;
            $content = Dashboard::getDashboardContents();

            $currency = new Currency();
            $util = new Util();
            $content['notes'] = Dashboard::getUserNotes();
            $html = parent::view('Admin.Dashboard.showDashboard', compact('totalBuy', 'pages', 'currency', 'content', 'util'));
        }
        return $html;
    }

    public function saveNotes(Http $http)
    {
        Dashboard::saveNotes($http);
        return parent::redirect('/wp-admin/admin.php?page=dashboard');
    }

    /**
     * To Initialize the setup, on fresh installation of "CartRabbit"
     *
     * @return \CartRabbit\Controllers\display
     */
    public function initSetupWizard()
    {
        $countries = (new CountryRepository())->getList();
        $currency = (new CurrencyRepository())->getList();

        return parent::view('Admin.Setup.setupWizard', compact('countries', 'currency'));
    }

    public function downloadGeoIP()
    {
        $source = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz';
        $destination = Helper::get('site_path') . '/resources/assets/mmdb/GeoLite2-City.mmdb.gz';

        Util::downloadFile($source, $destination);
    }


}