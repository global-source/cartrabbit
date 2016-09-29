<?php

namespace CartRabbit\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class order_items
 * @package CartRabbit\Models
 */
class OrderItems extends Eloquent
{
    /**
     * To Set Table Name
     * @var string
     */
    protected $table = 'cartrabbit_order_items';

    /**
     * To Set Fillable fields in the table
     * @var array
     */
    protected $fillable = [
        'order_item_type',
        'order_id'
    ];

    /**
     * To Set Primart Key
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $with = ['meta'];

    /**
     * To Set the timestamp for all records
     * @var bool
     */
    public $timestamps = true;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function meta()
    {
        return $this->hasMany('\CartRabbit\Models\OrderItemMeta', 'order_item_id');
    }
}