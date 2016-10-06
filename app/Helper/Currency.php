<?php

namespace CartRabbit\Helper;

use CommerceGuys\Intl\Currency\CurrencyRepository;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepository;
use CommerceGuys\Intl\Formatter\NumberFormatter;
use CommerceGuys\Pricing\Price;

use Corcel\Post;
use Illuminate\Support\Facades\Session;
use CartRabbit\Helper;
use CartRabbit\Models\Settings;

/**
 * Class currency
 * @package CartRabbit\Helper
 */
class Currency
{

    /**
     * To set default exchange value
     * @var
     */
    protected static $defaultExchangeValue;

    /**
     * @var Currency
     */
    protected static $defaultCurreny;

    /**
     * To set default exchange value
     * @var
     */
    protected static $activeCurrency;

    /**
     * @var Exchange
     */
    protected static $activeExchangeValue;

    /**
     * To Set Segments for URL Operations
     * @var
     */
    protected $segments;

    /**
     * currency constructor to init configurations
     */
    public function __construct()
    {
        self::$defaultExchangeValue = 1;
        self::$defaultCurreny = $this->getDefaultCurrency();
        self::$activeCurrency = (Session()->has('currency') ? Session()->get('currency') : self::$defaultCurreny);
        $this->setExchangeValue();
        self::$activeExchangeValue = (Session()->has('exchange_value') ? Session()->get('exchange_value') : self::getExchangeValue());

    }

    /**
     * To Return the currency object
     * @param $countryCode
     * @return \CommerceGuys\Intl\Currency\Currency|\CommerceGuys\Intl\Currency\CurrencyInterface
     */
    public static function getCurrencyByCountryCode($countryCode)
    {
        $currency = $countryCode;
        $currencyRepository = new CurrencyRepository();
        if (!is_object($countryCode)) {
            $currency = $currencyRepository->get($countryCode);
        }
//        $decimal = self::getDecimals($countryCode);
//        $currency->setFractionDigits($decimal);
        return $currency;
    }

    /**
     * @return Currency
     */
    public static function getActiveCurrency()
    {
        return self::$activeCurrency;
    }

    /**
     * @return Currency
     */
    public static function getCurrency()
    {
        if (self::$defaultCurreny === self::$activeCurrency) {
            $currency = self::$defaultCurreny;
        } elseif (is_null(self::$activeCurrency)) {
            $currency = self::$defaultCurreny;
        } else {
            $currency = self::$activeCurrency;
        }
        return $currency;
    }

    /**
     * @param $http
     * @return Secondary
     */
    public function getSecondaryCurrenciesBySegment($http)
    {
        $segments = $http->segments();
        $this->setSegments($segments);
        return $this->getSecondaryCurrencies();
    }

    /**
     * @return mixed
     */
    public static function secondaryCurrencyIsAvail()
    {
        $secondary_currency = Post::where('post_type', 'cartrabbit_currency')->first();
        return $secondary_currency['ID'];
    }

    /**
     * To get separator of the currency type
     * For Separator
     */
    public function getSeparator()
    {

    }

    /**
     * To process the price and convert it into other currency types
     * @param $cost Integer price of an item
     * @return Formated Price Value
     */
    public function format($cost, $qty = null, $default_exchange = null, $currency_code = null, $with_symbol = true)
    {
        if ($cost == '' or !isset($cost) or is_null($cost)) return false;

        /** If Given Cost is not a "String", then it will convert automatically */
//        if (!is_string($cost)) $cost = number_format($cost, 0, '.', '');
        //type cast the number to a string
        $cost = (string)$cost;

        $currencyRepository = new CurrencyRepository;
        $default = $this->getDefaultCurrency();
        $currency = self::getCurrency();
        $exchange = self::getExchangeValue();

        if (!is_null($currency_code)) $currency = $currency_code;

        if (!is_null($default_exchange)) $exchange = $default_exchange;

        $currency = $this->getCurrencyByCountryCode($currency);

        $from = $currencyRepository->get($default);
        $price = new Price($cost, $from);

        /** To Get Total cost of a Product */
        if (isset($qty)) {
            $price = $price->multiply($qty);
        }

        /** To Set Exchange Value */
        Session()->set('exchange_value', $exchange);
        Session()->set('currency', $currency);

        $price = $price->convert($currency, $exchange);

        if ($with_symbol) {
            self::formatSymbol($price);
        }

        return $price;
    }

    public static function formatSymbol(&$price)
    {
        $numberFormatRepository = new NumberFormatRepository;
        $numberFormat = $numberFormatRepository->get('en-US');

        $currencyFormatter = new NumberFormatter($numberFormat, NumberFormatter::CURRENCY);
        $price = $currencyFormatter->formatCurrency($price->getAmount(), $price->getCurrency());
    }

    /**
     * To Return the post_id for currenct currency's post
     * @return mixed
     */
    public static function getDefaultCurrencyID()
    {
        $id = Post::where('post_type', 'cartrabbit_currency')->pluck('ID')->first();
        return $id;
    }


    /**
     * To Return currenct currency
     * @return Currency Code
     */
    public function getCurrencyCode()
    {
        return Session()->get('currency');
    }


    /**
     * To Set exchange value for the Currency, if "$exchange_value" is "True" then,
     * the Default Exchange value is going to Reset.
     *
     * @param bool $exchange_value
     * @return int
     */
    public function setExchangeValue($exchange_value = false)
    {
        $exchange = self::$defaultExchangeValue;

        if (self::$defaultCurreny != self::$activeCurrency) {
            $currency_id = self::secondaryCurrencyIsAvail();

            if (!$currency_id) return self::$activeExchangeValue = $exchange;

            if (is_object(self::$activeCurrency)) {
                $currency_code = self::$activeCurrency->getCurrencyCode();
            } else {
                $currency_code = self::$activeCurrency;
            }
            //To Get Exchange Value by Currency Code
            $exchange = self::getExchangeValueByCurrencyCode($currency_code);
        }

        //If $exchange_value is True, the Default exchange value is going to Reset.
        if ($exchange_value != false) {
            $exchange = $exchange_value;
            self::$defaultExchangeValue = $exchange_value;
        }

        //To Update Exchange Value to Session
        Session()->set('exchange_value', $exchange);

        //To Update Active Exchange value
        self::$activeExchangeValue = $exchange;
    }

    /**
     * To Return the Exchange value of the given currency type
     * @return Exchange value in string
     */
    public static function getExchangeValue()
    {
        $exchange = self::$defaultExchangeValue;
        if (self::$defaultCurreny != self::$activeCurrency) {
            $exchange = self::$activeExchangeValue;
        }
        return $exchange;
    }

    /**
     * Return Exchange value by Country Code
     * @param $code
     * @return Float Exchange Value
     */
    public static function getExchangeValueByCurrencyCode($code = false)
    {
        $exchange = 1;
        if ($code) {
            $currency = json_decode(self::getStoredCurrencyDataByCode($code));
            if (isset($currency->exchange)) {
                $exchange = $currency->exchange;
            }
        }
        return $exchange;
    }

    /**
     * It Returns the Stored Currency from DataBase
     */
    public static function getStoredCurrencyDataByCode($code)
    {
        $currency_id = self::getDefaultCurrencyID();
        $currency = Post::find($currency_id)->meta()->get()->pluck('meta_value', 'meta_key');
        if (isset($currency[$code])) {
            return $currency[$code];
        } else {
            return json_encode([]);
        }
    }

    /**
     * To Return the Default currency
     * @return Currency Code
     */
    public function getDefaultCurrency()
    {
        $id = Settings::getStoreConfigID();
        if (empty($id)) return array();
        $parenctCurrency = Post::find($id)->meta()->get()->where('meta_key', 'currency')->pluck('meta_value')->first();
        return $parenctCurrency;
    }

    /**
     * @param $segment
     */
    public function setSegments($segment)
    {
        $this->segments = $segment;
    }

    /**
     * @return mixed
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * To Return Secondary currency code.
     * @return Secondary code in html format
     */
    public function getSecondaryCurrencies()
    {
        $default = $this->getDefaultCurrency();
        $site_addr = Helper::get('site_addr');
        $secondaryCurrencyID = self::secondaryCurrencyIsAvail();
        if (!$secondaryCurrencyID) return $html = '';
        $currencies = Post::find($secondaryCurrencyID)->meta()->pluck('meta_value');
        foreach ($currencies as $currency) {
            $secCurrencies[] = json_decode($currency, true);
        }

        $html = '<a href="?currency=' . $default . '"><button class="btn btn-info">' . $default . '</button></a>';
        foreach ($secCurrencies as $secCurrency) {
            $html .= '<a href="?currency=' . $secCurrency['currency'] . '"><button class="btn btn-primary">' . $secCurrency['currency'] . '</button></a>';
        }
        return $html;
    }

    /**
     * To Set the get the decimals from currency post meta's
     * @param $countrycode
     */
    public function getDecimals($countrycode)
    {
//        $ids = Post::where('post_type', 'cartrabbit_Corrency')->select('ID')->get();
//        $decimal = Post::where('')
    }


    /**
     * To Get Total cost of the purchased product with currenct currency format
     * @return Total cost of purchased product
     */
    public function getCartTotal($costs, $qty)
    {
        $total = 0;
        for ($i = 0; $i < count($costs); $i++) {
            $total += ($costs[$i][0] * $qty[$i][0]);
        }
        return $this->format($total);
    }
}