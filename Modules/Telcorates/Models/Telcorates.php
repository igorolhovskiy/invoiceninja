<?php

namespace Modules\Telcorates\Models;

use App\Models\EntityModel;
use Laracasts\Presenter\PresentableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Telcorates extends EntityModel
{
    use PresentableTrait;
    use SoftDeletes;

    /**
     * @var string
     */
    protected $presenter = 'Modules\Telcorates\Presenters\TelcoratesPresenter';

    /**
     * @var string
     */
    protected $fillable = ["name", "description"];

    /**
     * @var string
     */
    protected $table = 'telcorates';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['product_type', 'product_key', 'notes'];

    public function getEntityType()
    {
        return 'telcorates';
    }

    public function codes()
    {
        return $this->hasMany('Modules\Telcorates\Models\TelcorateCode', 'telcorate_id');
    }

    public function getProductTypeAttribute() {
        return 'telcorates';
    }
    
    public function getProductKeyAttribute() {
        return $this->attributes['name'];
    }
    
    public function getNotesAttribute() {
        return $this->attributes['description'];
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
