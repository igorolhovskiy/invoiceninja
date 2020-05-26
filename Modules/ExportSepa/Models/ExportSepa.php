<?php

namespace Modules\ExportSepa\Models;

use App\Models\EntityModel;
use Laracasts\Presenter\PresentableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExportSepa extends EntityModel
{
    use PresentableTrait;
    use SoftDeletes;

    /**
     * @var string
     */
    protected $presenter = 'Modules\ExportSepa\Presenters\ExportSepaPresenter';

    /**
     * @var string
     */
    protected $fillable = ["requested_collection_date"];

    /**
     * @var string
     */
    protected $table = 'exportsepa';

    public function getEntityType()
    {
        return 'exportsepa';
    }

    public function account()
    {
        return $this->belongsTo('App\Models\Account');
    }
    
    public function items() {
        return $this->hasMany('Modules\ExportSepa\Models\ExportSepaItem', 'exportsepa_id');
    }
}
