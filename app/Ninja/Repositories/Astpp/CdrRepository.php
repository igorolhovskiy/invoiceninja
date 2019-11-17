<?php

namespace App\Ninja\Repositories\Astpp;

use App\Models\Astpp\Cdr;

class CdrRepository
{
  public function getCdr($accountId, $start, $end) {
    return Cdr::where('accountid', $accountId)
      ->whereBetween('callstart', [$start, $end])
      ->get();
  }
}