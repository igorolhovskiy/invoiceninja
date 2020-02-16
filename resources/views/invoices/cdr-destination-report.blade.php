@extends('public.header')

@section('head')
	@parent
      @include('money_script')
			@foreach ($invoice->client->account->getFontFolders() as $font)
        	<script src="{{ asset('js/vfs_fonts/'.$font.'.js') }}" type="text/javascript"></script>
    	@endforeach

      <script src="{{ asset('pdf.built.js') }}?no_cache={{ NINJA_VERSION }}" type="text/javascript"></script>

		<style type="text/css">
			body {
				background-color: #f8f8f8;
			}

            .dropdown-menu li a{
                overflow:hidden;
                margin-top:5px;
                margin-bottom:5px;
            }

			#signature {
		        border: 2px dotted black;
		        background-color:lightgrey;
		    }
		</style>

@stop

@section('content')

	<div class="container" id="pdfContainer">
    <script type="text/javascript">
      window.invoice = {!! $invoice !!};

      @if ($account->hasLogo())
        window.accountLogo = "{{ Form::image_data($account->getLogoRaw(), true) }}";
      @endif
      window.NINJA = NINJA || {};
      @if ($account->hasFeature(FEATURE_CUSTOMIZE_INVOICE_DESIGN))
          NINJA.primaryColor = "{{ $account->primary_color }}";
          NINJA.secondaryColor = "{{ $account->secondary_color }}";
          NINJA.fontSize = {{ $account->font_size }};
          NINJA.headerFont = {!! json_encode($account->getHeaderFontName()) !!};
          NINJA.bodyFont = {!! json_encode($account->getBodyFontName()) !!};
      @else
          NINJA.primaryColor = "";
          NINJA.secondaryColor = "";
          NINJA.fontSize = 9;
          NINJA.headerFont = "Roboto";
          NINJA.bodyFont = "Roboto";
      @endif
      var invoiceLabels = {!! json_encode($account->getInvoiceLabels()) !!};    

      pdfMake.fonts = {}
      var fonts = window.invoiceFonts || invoice.invoice_fonts;

      // Add only the loaded fonts
      $.each(fonts, function(i,font){
          addFont(font);
      });
      
      function addFont(font){
          if(window.ninjaFontVfs[font.folder]){
              var folder = 'fonts/'+font.folder;
              pdfMake.fonts[font.name] = {
                  normal: folder+'/'+font.normal,
                  italics: folder+'/'+font.italics,
                  bold: folder+'/'+font.bold,
                  bolditalics: folder+'/'+font.bolditalics
              }
          }
      }

      $(function() {
        @if (Input::has('phantomjs'))
          writePdfAsString();
        @else
          var doc = getPDF();
          doc.getDataUrl(function(dataUrl) {
            var targetElement = document.querySelector('#pdfContainer');
            var iframe = document.createElement('iframe');
            iframe.src = dataUrl;
            iframe.width = '100%';
            iframe.height = '550px';
            targetElement.appendChild(iframe);
          });
        @endif
      });

      function writePdfAsString() {
        var doc = getPDF();
        doc.getDataUrl(function(pdfString) {
          document.write(pdfString);
          document.close();
          if (window.hasOwnProperty('pjsc_meta')) {
            window['pjsc_meta'].remainingTasks--;
          }
        });
      }

      function getPDF() {
        var docDef = {
          content: []
        };
        try {
          var javascript = JSON.parse(NINJA.decodeJavascript(window.invoice, window.invoice.invoice_design.javascript));
          docDef.content.push(javascript.content[0]);
          docDef.content = docDef.content.concat(buildReport());
          docDef.defaultStyle = javascript.defaultStyle;
          docDef.styles = javascript.styles;
          docDef.footer = javascript.footer;
          docDef.pageMargins = javascript.pageMargins;
          docDef.background = javascript.background;
        } catch (err) {console.log('error create document', err)}
        return pdfMake.createPdf(docDef);
      }

      function buildReport() {
        var data = {!! $report !!};
        var reportRows = [];
        data.forEach(function(item) {
          reportRows.push([
            {text: item.destination_name, alignment: 'right'},
            {text: item.cnt, alignment: 'center'},
            {text: item.formattedDuration, alignment: 'center'},
            {text: '€ ' + item.cost, alignment: 'right'},            
          ]);
        });
        report = [
            { text: '', margin: [0, 8] },
            {
              layout: {
                hLineWidth: function() { return 1; },
                vLineWidth: function() { return 0; },
                paddingTop: function() { return 8; },
                paddingBottom: function() { return 8; },
              },
              table: {
                // headers are automatically repeated if the table spans over multiple pages
                // you can declare how many rows should be treated as headers
                headerRows: 1,
                widths: ['auto', '*'],

                body: [
                  [ 
                    [
                    {text: 'Summary of Calls', bold: true},
                    {text: 'of ' + invoice.client.name, bold: true},
                    {text: 'from {{$period['period_from']}} to {{$period['period_to']}} (inclusive)', bold: true}
                    ], 
                  '']
                ]
              }
            },
            { text: 'Note: all costs are VAT excluded.', margin: [0, 8], italics: true },
            {
              layout: {
                fillColor: function (rowIndex, node, columnIndex) {
                  return (rowIndex === 0) ? '#CCCCCC' : null;
                },
                hLineWidth: function (i, node) {
                  return i === 0 || i === 2 ? 0 : 1;
                },
                vLineWidth: function() { return 0; },
                hLineColor: function (i) {
                  return i === 1 ? 'black' : 'lightgrey';
                },
              },
              table: {
                headerRows: 1,
                widths: [ 275, 'auto', '*', 'auto' ],
                body: [
                  [ 
                    [
                      {text: invoice.client.name, bold:true, alignment: 'right'},
                      {text: ' ', alignment: 'right'},
                      {text: 'Totals', bold: true, alignment: 'right'}
                    ],
                    [
                      {text: ' '},
                      {text: 'Nr', bold: true, alignment: 'center'},
                      {text: '{{$totalCount}}', bold: true, alignment: 'center'}
                    ],
                    [
                      {text: ' '},
                      {text: 'Duration', bold: true, alignment: 'center'},
                      {text: '{{$totalDuration}}', bold: true, alignment: 'center'}
                    ],
                    [
                      {text: ' '},
                      {text: 'Cost', bold: true, alignment: 'right'},
                      {text: '€ {{$totalCost}}', bold: true, alignment: 'right'}
                    ],                                    
                  ],
                  [ {text: 'Locations', alignment: 'right', bold: true}, ' ', ' ', ' ' ],
                ].concat(reportRows)
              }
            }     
          ];
        return report;
      }
    </script>
  </div>
@stop