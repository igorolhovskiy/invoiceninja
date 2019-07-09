<?php

namespace Modules\Telcopackages\Models;

use App\Models\EntityModel;
use Laracasts\Presenter\PresentableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Telcopackages extends EntityModel
{
    use PresentableTrait;
    use SoftDeletes;

    /**
     * @var string
     */
    protected $presenter = 'Modules\Telcopackages\Presenters\TelcopackagesPresenter';

    /**
     * @var string
     */
    protected $fillable = ["name","amount_of_minutes","price"];

    /**
     * @var string
     */
    protected $table = 'telcopackages';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['product_type', 'product_key'];

    public function getEntityType()
    {
        return 'telcopackages';
    }

    public function codes()
    {
        return $this->hasMany('Modules\Telcopackages\Models\TelcopackageCode', 'telcopackage_id');
    }

    public function getProductTypeAttribute() {
        return 'telcopackages';
    }
    
    public function getProductKeyAttribute() {
        return $this->attributes['name'];
    }
    
    public function setProductKeyAttribute($value) {
        $this->attributes['name'] = $value;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public static function findProductByKey($key)
    {
        return self::scope()->where('name', '=', $key)->first();
    }    
}
