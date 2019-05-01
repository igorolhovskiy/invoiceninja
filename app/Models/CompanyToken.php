<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyToken extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    protected $guarded = [
        'id',
    ];

    protected $with = [
    //    'user',
    //    'company',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function company()
    {
    	return $this->belongsTo(Company::class);
    }
}
