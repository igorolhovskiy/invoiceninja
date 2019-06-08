<?php

namespace Modules\Telcorates\Models;

use Illuminate\Database\Eloquent\Model;

class TelcorateCode extends Model
{
    /**
     * @var string
     */
    protected $fillable = ['code', 'init_seconds', 'increment_seconds', 'rate', 'description'];

    /**
     * @var string
     */
    protected $table = 'telcorate_codes';

}
