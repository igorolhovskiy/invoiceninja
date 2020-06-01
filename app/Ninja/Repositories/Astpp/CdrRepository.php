<?php

namespace App\Ninja\Repositories\Astpp;

use App\Models\Astpp\Cdr;

class CdrRepository
{
  public function getCdr($accountId, $start, $end) {
    return Cdr::where('accountid', $accountId)
      ->where('calltype', 'STANDARD')
      ->where('billseconds', '>', 0)
      ->whereBetween('callstart', [$start, $end])
      ->get();
  }
}