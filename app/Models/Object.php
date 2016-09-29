<?php

namespace CartRabbit\Models;

use Flycartinc\Inventory\Resource\ObjectRepository;
/**
 * Class objRepository
 * @package MyPlugin\Models
 */
class Object implements ObjectRepository
{
    public function find($a)
    {
        return true;
    }

    public function findAll()
    {
        return true;
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $inventoryUnit1 = (new orders())->getBackordereds();
        return $inventoryUnit1;
    }

    public function findOneBy(array $criteria)
    {
        return true;
    }

    public function getClassName()
    {
        return true;
    }
}
