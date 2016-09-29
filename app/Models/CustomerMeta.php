<?php

namespace CartRabbit\Models;

use Corcel\UserMetaCollection;
use Illuminate\Database\Eloquent\Model;


/**
 * Class products
 * @package CartRabbit\Models
 */
class CustomerMeta extends Model
{
    protected $table = 'usermeta';
    protected $primaryKey = 'umeta_id';
    public $timestamps = false;
    protected $fillable = array('meta_key', 'meta_value', 'user_id');

    /**
     * User relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('CartRabbit\Models\Customer');
    }

    /**
     * Override newCollection() to return a custom collection
     *
     * @param array $models
     * @return \Corcel\UserMetaCollection
     */
    public function newCollection(array $models = array())
    {
        return new UserMetaCollection($models);
    }

}