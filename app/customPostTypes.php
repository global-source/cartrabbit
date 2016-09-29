<?php

/** @var  \Herbert\Framework\Application $container */

use CartRabbit\customPostTypes\CartRabbit_Product;
use CartRabbit\customPostTypes\CartRabbit_Order;

//initialise product custom post type
(new CartRabbit_Product)->register();

//initialise order post type
(new CartRabbit_Order)->register();

