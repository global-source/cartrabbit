<?php

namespace CartRabbit\Helper;

use CommerceGuys\Tax\TaxableInterface;

class Taxable implements TaxableInterface
{
   public function isPhysical()
   {
      return true;
   }

}

