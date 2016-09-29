<?php

namespace CartRabbit\Helper;

use CommerceGuys\Tax\Repository\TaxTypeRepository;

class TaxRepository extends TaxTypeRepository
{
    public function addTaxRepository($definitions = array()){

        $tax = $this->createTaxTypeFromDefinition($definitions);

    }
}

