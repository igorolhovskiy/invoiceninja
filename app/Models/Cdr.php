<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cdr extends EntityModel
{
    /**
     * @var string
     */
    protected $fillable = ["import_colt_id", "did", "datetime", "dst", "dur", "cost", "done", "status"];

    /**
     * @var string
     */
    protected $table = 'cdrs';

    public function getEntityType()
    {
        return 'cdr';
    }
}
