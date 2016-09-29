<?php

namespace CartRabbit\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class order_items
 * @package CartRabbit\Models
 */
class OrderItemMeta extends Eloquent
{
    /**
     * To Set Table Name
     * @var string
     */
    protected $table = 'cartrabbit_order_itemmeta';

    /**
     * To Set Fillable fields in the table
     * @var array
     */
    protected $fillable = [
        'order_item_id',
        'meta_key',
        'meta_value'
    ];

    /**
     * To Set Primart Key
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * To Set the timestamp for all records
     * @var bool
     */
    public $timestamps = true;

	/**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function meta(){
        return $this->hasMany('\CartRabbit\Models\OrderItemMetas','order_item_id');
    }
}