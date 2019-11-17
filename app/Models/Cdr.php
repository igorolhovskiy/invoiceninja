<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cdr extends EntityModel
{
    /**
     * @var string
     */
    protected $fillable = [
        'client_id',
        'astpp_cdr_uniqueid',
        "import_colt_id",
        "did",
        "datetime",
        "dst",
        "dur",
        "cost",
        "done",
        "status"];

    /**
     * @var string
     */
    protected $table = 'cdrs';

    public function getEntityType()
    {
        return 'cdr';
    }

    public function invoice()
    {
        return $this->belongTo('App\Models\Invoice');
    }

    public function importColt()
    {
        return $this->belongTo('Modules\ImportColt\Models\ImportColt');
    }    
}
