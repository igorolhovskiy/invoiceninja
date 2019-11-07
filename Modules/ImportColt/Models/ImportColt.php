<?php

namespace Modules\ImportColt\Models;

use App\Models\EntityModel;
use Laracasts\Presenter\PresentableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportColt extends EntityModel
{
    use PresentableTrait;
    use SoftDeletes;

    /**
     * @var string
     */
    protected $presenter = 'Modules\ImportColt\Presenters\ImportColtPresenter';

    /**
     * @var string
     */
    protected $fillable = ["name", "file_path", "invoice_date", "status"];

    /**
     * @var string
     */
    protected $table = 'import_colts';

    public function getEntityType()
    {
        return 'importcolt';
    }

    public function cdrs()
    {
        return $this->hasMany('\App\Models\Cdr');
    }
}
