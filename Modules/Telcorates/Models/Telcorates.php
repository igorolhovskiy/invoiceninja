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
    protected $fillable = ["name"];

    /**
     * @var string
     */
    protected $table = 'telcorates';

    public function getEntityType()
    {
        return 'telcorates';
    }

    public function codes()
    {
        return $this->hasMany('Modules\Telcorates\Models\TelcorateCode', 'telcorate_id');
    }    
}
