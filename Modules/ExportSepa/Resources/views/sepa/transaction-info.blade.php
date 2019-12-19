<DrctDbtTxInf>
  <PmtId>
    <EndToEndId>{{ $transaction['endtoendid'] }}</EndToEndId>
  </PmtId>
  <InstdAmt Ccy="EUR">{{ $transaction['amount'] }}</InstdAmt>
  <DrctDbtTx>
    <MndtRltdInf>
      <MndtId>{{ $transaction['sepa'] }}</MndtId>
      <DtOfSgntr>{{ $transaction['sepaDate'] }}</DtOfSgntr>
    </MndtRltdInf>
  </DrctDbtTx>
  <DbtrAgt>
    <FinInstnId>
      <BIC>{{ $transaction['bic'] }}</BIC>
    </FinInstnId>
  </DbtrAgt>
  <Dbtr>
    <Nm>{{ $transaction['clientName'] }}</Nm>
  </Dbtr>
  <DbtrAcct>
    <Id>
      <IBAN>{{ $transaction['iban'] }}</IBAN>
    </Id>
  </DbtrAcct>
  <RmtInf>
    <Ustrd>{{ $transaction['ustrd'] }}</Ustrd>
  </RmtInf>  
</DrctDbtTxInf>
