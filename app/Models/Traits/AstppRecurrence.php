<?php

namespace App\Models\Traits;

use Carbon;
use DateTime;
use Utils;

/**
 * Class AstppRecurrence
 */
trait AstppRecurrence
{
  /**
   * @return Array
   */
  public function getAsttpPeriod() {
    $nextSendDate = $this->getNextSendDate();
    if (!$nextSendDate) {
      return null;
    }
    $endDate = Carbon::instance($nextSendDate)->subDay();
    $startDate = Carbon::instance($nextSendDate)->subDay()
      ->sub(\DateInterval::createFromDateString($this->getPeriodString()));
    return ['start' => $startDate->toDateString(), 'end' => $endDate->toDateString()];
  }

  public function getPeriodString() {
    switch ($this->frequency_id) {
      case FREQUENCY_WEEKLY:
          return '1 week';
      case FREQUENCY_TWO_WEEKS:
          return '2 week' ;
      case FREQUENCY_FOUR_WEEKS:
          return '4 week';
      case FREQUENCY_MONTHLY:
          return '1 month';
      case FREQUENCY_TWO_MONTHS:
          return '2 month';
      case FREQUENCY_THREE_MONTHS:
          return '3 month';
      case FREQUENCY_FOUR_MONTHS:
          return '4 month';
      case FREQUENCY_SIX_MONTHS:
          return '6 month';
      case FREQUENCY_ANNUALLY:
          return '1 year';
      case FREQUENCY_TWO_YEARS:
          return '2 year';
      default:
          return false;
    }    
  }
}