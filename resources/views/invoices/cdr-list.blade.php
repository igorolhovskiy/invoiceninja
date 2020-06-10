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
          var javascript = JSON.parse(NINJA.decodeJavascript(window.invoice, window.invoice.invoice_design.javascript), jsonCallBack);
          docDef.content.push(javascript.content[0]);
          const reportContent = JSON.parse('{!! $content !!}', jsonCallBack);
          docDef.content = docDef.content.concat(reportContent);
          docDef.defaultStyle = javascript.defaultStyle;
          docDef.styles = javascript.styles;
          docDef.footer = javascript.footer;
          docDef.pageMargins = javascript.pageMargins;
          docDef.background = javascript.background;
        } catch (err) {console.log('error create document', err)}
        return pdfMake.createPdf(docDef);
      }

      // Function from built.js for compatibility with content of report
      function jsonCallBack(key, val) {

        // handle custom functions
        if (typeof val === 'string') {
            if (val.indexOf('$firstAndLast') === 0) {
                var parts = val.split(':');
                return function (i, node) {
                    return (i === 0 || i === node.table.body.length) ? parseFloat(parts[1]) : 0;
                };
            } else if (val.indexOf('$none') === 0) {
                return function (i, node) {
                    return 0;
                };
            } else if (val.indexOf('$notFirstAndLastColumn') === 0) {
                var parts = val.split(':');
                return function (i, node) {
                    return (i === 0 || i === node.table.widths.length) ? 0 : parseFloat(parts[1]);
                };
            } else if (val.indexOf('$notFirst') === 0) {
                var parts = val.split(':');
                return function (i, node) {
                    return i === 0 ? 0 : parseFloat(parts[1]);
                };
            } else if (val.indexOf('$amount') === 0) {
                var parts = val.split(':');
                return function (i, node) {
                    return parseFloat(parts[1]);
                };
            } else if (val.indexOf('$primaryColor') === 0) {
                var parts = val.split(':');
                return NINJA.primaryColor || parts[1];
            } else if (val.indexOf('$secondaryColor') === 0) {
                var parts = val.split(':');
                return NINJA.secondaryColor || parts[1];
            }
        }

        // determine whether or not to show the header/footer
        if (invoice.features.customize_invoice_design) {
            if (key === 'header') {
                return function(page, pages) {
                    if (page === 1 || invoice.account.all_pages_header == '1') {
                        if (invoice.features.remove_created_by) {
                            return NINJA.updatePageCount(JSON.parse(JSON.stringify(val)), page, pages);
                        } else {
                            return val;
                        }
                    } else {
                        return '';
                    }
                }
            } else if (key === 'footer') {
                return function(page, pages) {
                    if (page === pages || invoice.account.all_pages_footer == '1') {
                        if (invoice.features.remove_created_by) {
                            return NINJA.updatePageCount(JSON.parse(JSON.stringify(val)), page, pages);
                        } else {
                            return val;
                        }
                    } else {
                        return '';
                    }
                }
            }
        }

        // check for markdown
        if (key === 'text') {
            val = NINJA.parseMarkdownText(val, true);
        }

        /*
        if (key === 'stack') {
            val = NINJA.parseMarkdownStack(val);
            val = NINJA.parseMarkdownText(val, false);
        }
        */

        return val;
      }      
    </script>
  </div>
@stop