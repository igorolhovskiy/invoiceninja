<?php

namespace App\Services;

use Exception;

use Utils;

use App\Ninja\Repositories\CdrRepository;

/**
 * Class LogService.
 */
class LogService
{
  public function __construct(CdrRepository $cdrRepository) {
    $this->cdrRepository = $cdrRepository;
  }

  public function logColtRateNotFound($importColtId) {
    // Log rate not found calls
    $cdrRateNotFound = $this->cdrRepository->findRateNotFoundImportColt($importColtId);
    Utils::logColtRateNotFound('info', 'Found ' . $cdrRateNotFound->count() . ' cdrs with RATE_NOT_FOUND status:');
    foreach($cdrRateNotFound as $cdr) {
        Utils::logColtRateNotFound('info', "DID: {$cdr->did}, DATETIME: {$cdr->datetime}, DST: {$cdr->dst}, DUR: {$cdr->dur}");
    } 
  }

  public function logAstppRateNotFound($clientId, $startDate, $endDate) {
    // Log rate not found calls
    $cdrRateNotFound = $this->cdrRepository->findRateNotFoundAstppClient($clientId, $startDate, $endDate);
    Utils::logAstppRateNotFound('info', 'Found ' . $cdrRateNotFound->count() . ' cdrs with RATE_NOT_FOUND status:');
    foreach($cdrRateNotFound as $cdr) {
        Utils::logAstppRateNotFound('info', "DID: {$cdr->did}, DATETIME: {$cdr->datetime}, DST: {$cdr->dst}, DUR: {$cdr->dur}");
    }    
  }

}