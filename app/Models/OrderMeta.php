<?php

namespace CartRabbit\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class order_itemmetas
 * @package CartRabbit\Models
 */
class OrderMeta extends Eloquent
{
    /**
     * To Set Table Name
     * @var string
     */
    protected $table = 'cartrabbit_ordermeta';

    /**
     * To Set Fillable fields in the table
     * @var array
     */
    protected $fillable = [
        'order_id',
        'meta_key',
        'meta_value'
    ];


    /**
     * To Set Primart Key
     * @var string
     */
    protected $primaryKey = 'id';

    public $timestamps = true;

    /**
     * Make Relationship with items table
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo('Model\cartrabbit_ordermeta', 'order_id', 'id');
    }
}