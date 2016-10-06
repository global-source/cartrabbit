<?php

namespace CartRabbit\Controllers;

use CommerceGuys\Intl\Country\CountryRepository;
use CommerceGuys\Intl\Currency\CurrencyRepository;
use Flycartinc\Cart\Cart;
use CartRabbit\Controllers\Admin\DashboardController;
use CartRabbit\Helper;
use CartRabbit\Helper\EventManager;
use CartRabbit\Models\Settings;
use CartRabbit\Models\Setup;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class BaseController
 * @package CartRabbit\Controllers
 */
class BaseController
{
    /**
     * Protected Event Manager Variable
     *
     * @var $event
     */
    protected $event;

    /**
     * BaseController constructor.
     */
    public function __construct()
    {
        //
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
        return $this->view('Admin.Setup.setupWizard', compact('countries', 'currency'));
    }

    public function loadDefaultContext(&$context)
    {
        $context['site_url'] = Helper::get('site_url');
        $context['site_addr'] = Helper::get('site_addr');
        $context['default'] = Helper\Util::getDefaultConfig();
        $context['links'] = (new Helper\Util())->getStaticLinks();
    }

    /**
     * To Redefine the View Path
     * @param $path Path to View File
     * @param array $context passing Variables
     * @param bool $defaultContext
     * @return display twig file
     */
    public function view($path, $context = array(), $defaultContext = true)
    {
        $path = $this->graphView($path);
        $original = str_replace('.', '/', $path);
        $name = $original . '.twig';

        if ($defaultContext) {
            $this->loadDefaultContext($context);
        }
        return view($name, $context);
    }

    /**
     * To Verify and Overwrite the Template
     *
     * @param $org_path Default Template Loading Path
     * @return string Template Loading path
     */
    public function graphView($org_path)
    {
        /** Active Wordpress Template */
        $template_dir = get_template_directory();

        /** TO Verify, the Directory is Exist or Not */
        if (!is_dir($template_dir . '/CartRabbit/view/')) return '@CartRabbit/' . (string)$org_path;
        $tmp_path = str_replace('.', '/', $org_path);
        $file = $template_dir . '/cartrabbit/view/' . $tmp_path . '.twig';
        /** To Verify, the File is Exist or Not */
        if (!file_exists($file)) return '@CartRabbit/' . (string)$org_path;
        $path = $template_dir . '/cartrabbit/view/' . (string)$org_path;

        return $path;
    }



    /**
     * This return the default pages for simple redirection
     * @param $page string Page Name with available only
     * @return string Return to Dynamic Link
     */
    public static function redirectTo($page, $isOnlyUrl = false)
    {
        $url = Helper\Util::getURLof($page);
        if ($isOnlyUrl) {
            return $url;
        } else {
            return self::redirect($url);
        }
    }


    /**
     * @param $name
     * @param $arguments
     */
    function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }

    /**
     * To Redefine the Redirect URL
     *
     * @param $path User Assigned Path
     * @return \Herbert\Framework\Response
     */
    public function redirect($path, $noRoot = true)
    {
        $url = $path;
        if (!$noRoot) {
            $url = Helper::get('site_addr') . $path;
        }
        return redirect_response($url);
    }

    /**
     * To Get Registered Events from Event Manager
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     */
    public function eventManager()
    {
        $this->event = new EventManager();
        $this->event = $this->event->getEvents();
        return $this->event;
    }

    /**
     * To Initiate Generic Event
     *
     * @return GenericEvent
     */
    public function genericEvent()
    {
        return new GenericEvent();
    }
}