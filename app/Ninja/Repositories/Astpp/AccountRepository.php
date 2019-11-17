<?php

namespace App\Ninja\Repositories\Astpp;

use App\Models\Astpp\Account;

class AccountRepository
{
  public function getAccountByNumber($number) {
    return Account::where('number', $number)->first();
  }
}