<?php

namespace Modules\ExportSepa\Services;

use Carbon;

class ExportSepaService
{

  public function getSepaData($exportSepa)
  {
    $account = $exportSepa->account;
    $createDate = Carbon::parse($exportSepa->created_at);
    $numberOfTransaction = $exportSepa->items->count();
    $controlSum = $exportSepa->items->sum(function($item) {
      return $item->invoice->amount;
    });
    $groupHeader = [
      'messageIdentification' => 'SEPA-' . $createDate->format('YmdHis') . '-' . Carbon::now()->format('u'),
      'creationDateTime' => $createDate->toIso8601ZuluString(),
      'numberOfTransaction' => $numberOfTransaction,
      'controlSum' => number_format($controlSum, 2, '.', ''),
      'nameOfInitiatingParty' => $account->sepa_initiating_party_name,
    ];
    $paymentInformation = [
      'paymentInformationId' => $groupHeader['messageIdentification'] . '-P1',
      'paymentMethod' => $account->sepa_payment_method,
      'batchBooking' => 'false',
      'numberOfTransaction' => $numberOfTransaction,
      'controlSum' => number_format($controlSum, 2, '.', ''),
      'paymentTypeInformation' => [
        'serviceLevelCode' => 'SEPA',
        'localInstrumentCode' => 'CORE',
        'sequenceType' => $account->sepa_payment_sequence_type
      ],
      'reqdColltnDt' => Carbon::parse($exportSepa->requested_collection_date)->toDateString(),
      'creditorName' => $account->sepa_initiating_party_name,
      'creditorIban' => $account->sepa_payment_iban,
      'creditorBic' => $account->sepa_payment_bic,
      'chrgBr' => 'SLEV',
      'privateIdentificationId' => $account->sepa_payment_creditor_id,
      'debitTransactionInfo' => $exportSepa->items->map(function($item) use($account) {
        return [
          'endtoendid' => $item->endtoendid,
          'amount' => number_format($item->invoice->amount, 2, '.', ''),
          'invoiceNumber' => $item->invoice->invoice_number,
          'invoiceDate' => Carbon::parse($item->invoice->invoice_date)->toDateString(),
          'sepa' => $item->invoice->client->sepa,
          'sepaDate' => Carbon::parse($item->invoice->client->sepa_date)->toDateString(),
          'bic' => $item->invoice->client->bic,
          'iban' => $item->invoice->client->iban,
          'clientName' => $item->invoice->client->name,
          'ustrd' => $account->sepa_invoice_purpose_prefix . '-' . $item->invoice->invoice_number
        ];
      })->all(),
    ];
    return [
      'groupHeader' => $groupHeader,
      'paymentInformation' => $paymentInformation,
      'account' => $account,
      'exportSepa' => $exportSepa
    ];
  }
}