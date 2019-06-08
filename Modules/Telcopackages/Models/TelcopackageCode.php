<?php

namespace Modules\Telcopackages\Models;

use Illuminate\Database\Eloquent\Model;

class TelcopackageCode extends Model
{

    /**
     * @var string
     */
    protected $fillable = ["code", "description"];

    /**
     * @var string
     */
    protected $table = 'telcopackage_codes';

}
