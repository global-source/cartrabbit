<?php

namespace CartRabbit\Models;


use CommerceGuys\Tax\Resolver\TaxRate\CartrabbitTaxRateResolver;
use CommerceGuys\Tax\TaxableInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use CartRabbit\Helper\Address;

use CommerceGuys\Tax\Repository\TaxTypeRepository;
use CommerceGuys\Tax\Resolver\TaxType\ChainTaxTypeResolver;
use CommerceGuys\Tax\Resolver\TaxType\CanadaTaxTypeResolver;
use CommerceGuys\Tax\Resolver\TaxType\EuTaxTypeResolver;
use CommerceGuys\Tax\Resolver\TaxType\DefaultTaxTypeResolver;
use CommerceGuys\Tax\Resolver\TaxRate\ChainTaxRateResolver;
use CommerceGuys\Tax\Resolver\TaxRate\DefaultTaxRateResolver;
use CommerceGuys\Tax\Resolver\TaxResolver;
use CommerceGuys\Tax\Resolver\Context;

/**
 * Class products
 *
 * @package CartRabbit\Models
 */
class Tax extends Eloquent
{

    protected static $tax_repository;

    protected $taxResolver = null;

    protected $taxable;

    protected $taxContext;

    protected $storeAddress;

    protected $customerAddress;

    protected $taxRates;

    protected $taxAmounts;

    protected $taxTypes;

    protected $taxProfile;

    static $precision = 2;


    /**
     * To Process the Tax by Getting the Store address and the Customer Address.
     * Customer address is taken from Session, If Customer not select the Address
     * then the address of customer is consider as Store address
     *
     *
     * @return array
     */
    public function processTax($isStore = false)
    {
        $storeAddress = (new Settings())->getStoreAddress();
        $customerAddress = ((new Settings())->getCustomerAddress());

        if ($customerAddress == null) {
            $customerAddress = $storeAddress;
        }

        //if the tax is calculated in based on the Store,
        //then the customer address is act as same as the store address
        if ($isStore) {
            $customerAddress = $storeAddress;
        }

        $Address = new Address();
        $tax_address1 = $Address->format($storeAddress);
        $tax_address2 = $Address->format($customerAddress);
        $tax = $this->tax($tax_address1, $tax_address2);

        return $tax;
    }

    /**
     * To  Return the tax classes in based on Store's Tax profile classes
     *
     * @return array of Classes
     */
    public function getTaxClasses()
    {
        $Address = new Address();
        $defaultAddress = (new Settings())->getStoreAddress();
        $tax_address_default = $Address->format($defaultAddress);
        $tax = $this->tax($tax_address_default, $tax_address_default);

        foreach ($tax['rates'] as $rate) {
            $classes[] = $rate->getName();
        }

        return $classes;
    }

    /**
     * Tax Calculation based on the Addresses
     */
    /** For Temporary Usage[Getting tax id by country code] */

//	public function tax( $storeAddress, $customerAddress ) {
//		$result = array();
//
//		$resolver = self::getTaxInstance();
//		$context  = new Context( $storeAddress, $customerAddress );
//		$taxable  = new Taxable();
//		$taxable->isPhysical();
//
//		$result['amount'] = $resolver->resolveAmounts( $taxable, $context );
//		$result['rates']  = $resolver->resolveRates( $taxable, $context );
//		$result['types']  = $resolver->resolveTypes( $taxable, $context );
//
//		return ( ! empty( $result ) ) ? $result : array();
//	}

    /** Implement Singleton Instance to limit data access */
    public static function getTaxInstance()
    {
        if (is_null(self::$tax_repository)) {
            self::taxInstance();

            return self::$tax_repository;
        }

        return self::$tax_repository;
    }

    public function taxInstance()
    {
        $taxTypeRepository = new TaxTypeRepository();
        $chainTaxTypeResolver = new ChainTaxTypeResolver();
        $chainTaxTypeResolver->addResolver(new CanadaTaxTypeResolver($taxTypeRepository));
        $chainTaxTypeResolver->addResolver(new EuTaxTypeResolver($taxTypeRepository));
        $chainTaxTypeResolver->addResolver(new DefaultTaxTypeResolver($taxTypeRepository));
        $chainTaxRateResolver = new ChainTaxRateResolver();
        $chainTaxRateResolver->addResolver(new DefaultTaxRateResolver());
        $resolver = new TaxResolver($chainTaxTypeResolver, $chainTaxRateResolver);
        $this->tax_resolver = $resolver;
    }


    public function calculateTaxRates($forceAddress = false)
    {

        if (is_null($this->taxResolver)) {
            $this->initTaxResolver();
        }

        $this->initAddress($forceAddress);

        $this->setContext();

        return $this;
    }


    public function initTaxResolver()
    {
        $taxTypeRepository = new TaxTypeRepository();
        $chainTaxTypeResolver = new ChainTaxTypeResolver();
        $chainTaxTypeResolver->addResolver(new CanadaTaxTypeResolver($taxTypeRepository));
        $chainTaxTypeResolver->addResolver(new EuTaxTypeResolver($taxTypeRepository));
        $chainTaxTypeResolver->addResolver(new DefaultTaxTypeResolver($taxTypeRepository));
        $chainTaxRateResolver = new ChainTaxRateResolver();
        //	$chainTaxRateResolver->addResolver( new DefaultTaxRateResolver() );
        $chainTaxRateResolver->addResolver(new CartrabbitTaxRateResolver());

        $resolver = new TaxResolver($chainTaxTypeResolver, $chainTaxRateResolver);
        $this->taxResolver = $resolver;
    }


    public function initAddress($forceAddress = false)
    {
        $storeAddress = (new Settings())->getStoreAddress();
        $customerAddress = ((new Settings())->getCustomerAddress());

        //if customer address is null, set the store address as the customer's address
        //	if ( $customerAddress == null ) {
//			$customerAddress = $storeAddress;
        //	}

        //if the tax is calculated in based on the store, force customer address as the store address.
        //then the customer address is act as same as the store address
        if ($forceAddress) {
            $customerAddress = $storeAddress;
        }

        $this->setStoreAddress($storeAddress);
        $this->setCustomerAddress($customerAddress);
    }

    public function getStoreAddress()
    {
        return $this->storeAddress;
    }

    public function setStoreAddress($storeaddress)
    {
        $this->storeAddress = Address::format($storeaddress);

        return $this;
    }

    public function getCustomerAddress()
    {
        return $this->customerAddress;
    }

    //101 TMP
    public function setCustomerAddress($customerAddress, $isDirect = false)
    {
        if ($isDirect) {
            $this->customerAddress = $customerAddress;
        } else {
            $this->customerAddress = Address::format($customerAddress);
        }

        return $this;
    }

    public function setContext()
    {
        $this->taxContext = new Context($this->getStoreAddress(), $this->getCustomerAddress());
    }

    public function getContext()
    {
        return $this->taxContext;
    }

    public function getRates()
    {
        if (!$this->validateContext()) return array();
        $this->taxRates = $this->taxResolver->resolveRates($this->taxable, $this->taxContext);
        return $this->taxRates;
    }

    public function getAmounts()
    {
        if (!$this->validateContext()) return array();
        $this->taxAmounts = $this->taxResolver->resolveAmounts($this->taxable, $this->taxContext);
        return $this->taxAmounts;
    }

    public function getTypes()
    {
        if (!$this->validateContext()) return array();
        $this->taxTypes = $this->taxResolver->resolveTypes($this->taxable, $this->taxContext);
        return $this->taxTypes;
    }

    public function validateContext()
    {
        if (is_null($this->taxContext) || !$this->taxContext instanceof Context) return false;

        return true;
    }



    public function calculateTax($price, $rates, $includingTax = false)
    {

        if ($includingTax) {
            $taxes = $this->calculateInclusiveTax($price, $rates);
        } else {
            $taxes = $this->calculateExclusiveTax($price, $rates);
        }
        return $taxes;
    }

    public function getBaseTaxRates(TaxableInterface $taxableProduct)
    {

        $this->taxable = $taxableProduct;
        $rates = $this->calculateTaxRates(true)->getAmounts();
        return $rates;

    }

    public function getItemRates(TaxableInterface $taxableProduct)
    {
        $this->taxable = $taxableProduct;
        //should we force customer address
        $force = $this->forceCustomerAddress();
        $rates = $this->calculateTaxRates($force)->getAmounts();
        return $rates;

    }

    /**
     * Calc tax from inclusive price.
     *
     * @param  float $price
     * @param  array $rates
     * @return array
     */
    public static function calculateInclusiveTax($price, $rates)
    {
        $taxes = array();

        $regular_tax_rates = $compound_tax_rates = 0;

        foreach ($rates as $key => $rate) {

            $taxType = $rate->getRate()->getType();
            if ($taxType->isCompound()) {
                $compound_tax_rates = $compound_tax_rates + $rate->getAmount();
            } else {
                $regular_tax_rates = $regular_tax_rates + $rate->getAmount();
            }
        }
        $regular_tax_rate = 1 + ($regular_tax_rates);
        $compound_tax_rate = 1 + ($compound_tax_rates);
        $non_compound_price = $price / $compound_tax_rate;

        foreach ($rates as $key => $rate) {
            $taxType = $rate->getRate()->getType();

            if (!isset($taxes[$rate->getId()]['amount']))
                $taxes[$rate->getId()]['amount'] = 0;

            $the_rate = $rate->getAmount();

            if ($taxType->isCompound()) {
                $the_price = $price;
                $the_rate = $the_rate / $compound_tax_rate;
            } else {
                $the_price = $non_compound_price;
                $the_rate = $the_rate / $regular_tax_rate;
            }

            $net_price = $price - ($the_rate * $the_price);
            $tax_amount = $price - $net_price;
            $taxes[$rate->getId()]['amount'] += apply_filters('cartrabbit_price_inc_tax_amount', $tax_amount, $key, $rate, $price);
            $taxes[$rate->getId()]['rate'] = $rate;
        }
        return $taxes;
    }

    /**
     * Calc tax from exclusive price.
     *
     * @param  float $price
     * @param  array $rates
     * @return array
     */
    public static function calculateExclusiveTax($price, $rates)
    {
        $taxes = array();

        if ($rates) {
            // Multiple taxes
            foreach ($rates as $key => $rate) {
                $taxType = $rate->getRate()->getType();
                if ($taxType->isCompound())
                    continue;

                $tax_amount = $price * ($rate->getAmount());

                // ADVANCED: Allow third parties to modify this rate
                $tax_amount = apply_filters('cartrabbit_price_ex_tax_amount', $tax_amount, $key, $rate, $price);

                // Add rate
                if (!isset($taxes[$rate->getId()]['amount'])) {
                    $taxes[ $rate->getId() ]['amount'] = $tax_amount;
                } else {
                    $taxes[ $rate->getId() ]['amount'] += $tax_amount;
                }
                $taxes[ $rate->getId() ]['rate'] = $rate;
            }

            $pre_compound_total = array_sum($taxes);

            // Compound taxes
            foreach ($rates as $key => $rate) {
                $taxType = $rate->getRate()->getType();
                if (!$taxType->isCompound())
                    continue;

                $the_price_inc_tax = $price + ($pre_compound_total);

                $tax_amount = $the_price_inc_tax * ($rate->getAmount());

                // ADVANCED: Allow third parties to modify this rate
                $tax_amount = apply_filters('cartrabbit_price_ex_tax_amount', $tax_amount, $key, $rate, $price, $the_price_inc_tax, $pre_compound_total);

                // Add rate
                if (!isset($taxes[$rate->getId()]['amount']))
                    $taxes[$rate->getId()]['amount'] = $tax_amount;
                else
                    $taxes[$rate->getId()]['amount'] += $tax_amount;


                    $taxes[ $rate->getId() ]['rate'] = $rate;
            }
        }
        return $taxes;
    }

    public static function round($amount)
    {
        return apply_filters('cartrabbit_tax_round', round($amount, self::$precision));
    }

    public function precision()
    {
        return self::$precision;
    }

    public function getTaxTotal($taxes)
    {
        $total = 0;
        foreach($taxes as $tax) {
            $total += isset($tax['amount']) ? $tax['amount'] : 0;
        }

        return $total;
    }

    public function isCustomerVatExcepted()
    {
        //here is where we check if any tax exemption applies to a customer.
        //VIES check could also be performed here.
        return false;
    }

    public function enabled()
    {
        //TODO get it from the settings
        return true;
    }

    public function forceCustomerAddress()
    {
        $default_address = (new Settings())->defaultAddressforTax();
        $customerAddress = (new Settings())->getCustomerAddress();

        if ($customerAddress == null && $default_address == 'store_address') {
            return true;
        }
        return false;
    }


}