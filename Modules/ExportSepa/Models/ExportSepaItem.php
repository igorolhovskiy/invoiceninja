<?php

namespace Modules\ExportSepa\Models;

use Illuminate\Database\Eloquent\Model;

class ExportSepaItem extends Model
{

    /**
     * @var string
     */
    protected $fillable = ['exportsepa_id', 'invoice_id', 'endtoendid'];

    /**
     * @var string
     */
    protected $table = 'exportsepa_items';

    public function getEntityType()
    {
        return 'exportsepa_items';
    }

    public function invoice() {
        return $this->belongsTo('App\Models\Invoice');
    }
}
