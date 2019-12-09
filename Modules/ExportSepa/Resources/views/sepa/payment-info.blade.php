<PmtInfId>{{ $paymentInformation['paymentInformationId'] }}</PmtInfId>
<PmtMtd>{{ $paymentInformation['paymentMethod'] }}</PmtMtd>
<BtchBookg>{{ $paymentInformation['batchBooking'] }}</BtchBookg>
<NbOfTxs>{{ $paymentInformation['numberOfTransaction'] }}</NbOfTxs>
<CtrlSum>{{ $paymentInformation['controlSum'] }}</CtrlSum>
<PmtTpInf>
  <SvcLvl>
    <Cd>{{ $paymentInformation['paymentTypeInformation']['serviceLevelCode'] }}</Cd>
  </SvcLvl>
  <LclInstrm>
    <Cd>{{ $paymentInformation['paymentTypeInformation']['localInstrumentCode'] }}</Cd>
  </LclInstrm>
  <SeqTp>{{ $paymentInformation['paymentTypeInformation']['sequenceType'] }}</SeqTp>
</PmtTpInf>
<ReqdColltnDt>{{ $paymentInformation['reqdColltnDt'] }}</ReqdColltnDt>
<Cdtr>
  <Nm>{{ $paymentInformation['creditorName'] }}</Nm>
</Cdtr>
<CdtrAcct>
  <Id>
    <IBAN>{{ $paymentInformation['creditorIban'] }}</IBAN>
  </Id>
</CdtrAcct>
<CdtrAgt>
  <FinInstnId>
    <BIC>{{ $paymentInformation['creditorBic'] }}</BIC>
  </FinInstnId>
</CdtrAgt>
<ChrgBr>{{ $paymentInformation['chrgBr'] }}</ChrgBr>
<CdtrSchmeId>
  <Id>
    <PrvtId>
      <Othr>
        <Id>{{ $paymentInformation['privateIdentificationId'] }}</Id>
        <SchmeNm>
          <Prtry>SEPA</Prtry>
        </SchmeNm>
      </Othr>
    </PrvtId>
  </Id>
</CdtrSchmeId>
@foreach ($paymentInformation['debitTransactionInfo'] as $transaction)
  @include('exportsepa::sepa.transaction-info', ['transaction' => $transaction])
@endforeach