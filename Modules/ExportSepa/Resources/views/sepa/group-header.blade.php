<GrpHdr>
  <MsgId>{{ $groupHeader['messageIdentification'] }}</MsgId>
  <CreDtTm>{{ $groupHeader['creationDateTime'] }}</CreDtTm>
  <NbOfTxs>{{ $groupHeader['numberOfTransaction'] }}</NbOfTxs>
  <CtrlSum>{{ $groupHeader['controlSum'] }}</CtrlSum>
  <InitgPty>
    <Nm>{{ $groupHeader['nameOfInitiatingParty'] }}</Nm>
  </InitgPty>
</GrpHdr>
