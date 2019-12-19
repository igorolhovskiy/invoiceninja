<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL; ?>
<Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02 pain.008.001.02.xsd">
  <CstmrDrctDbtInitn>
    @include('exportsepa::sepa.group-header')
    <PmtInf>
      @include('exportsepa::sepa.payment-info')
    </PmtInf>
  </CstmrDrctDbtInitn>  
</Document>
