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

    public function getEntityType()
    {
        return 'telcopackages';
    }

    public function codes()
    {
        return $this->hasMany('Modules\Telcopackages\Models\TelcopackageCode', 'telcopackage_id');
    }
}
