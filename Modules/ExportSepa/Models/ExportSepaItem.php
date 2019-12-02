<?php

namespace Modules\ExportSepa\Models;

use Illuminate\Database\Eloquent\Model;

class ExportSepaItem extends Model
{

    /**
     * @var string
     */
    protected $fillable = ['exportsepa_id', 'invoice_id'];

    /**
     * @var string
     */
    protected $table = 'exportsepa_items';

    public function getEntityType()
    {
        return 'exportsepa_items';
    }

    public function invoices() {
        return $this->hasMany('App\Models\Invoices', 'invoice_id');
    }
}
