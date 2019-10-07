@extends('header')

@section('content')

    {!! Former::open($url)
            ->addClass('col-md-10 col-md-offset-1 warn-on-exit renumber-invoice')
            ->method($method)
            ->onsubmit('onFormSubmit(this); return false')            
            ->rules(array(
                'start_number' => 'required'
        	)) !!}

    @if ($importcolt)
      {!! Former::populate($importcolt) !!}
      <div style="display:none">
          {!! Former::text('public_id') !!}
      </div>
    @endif

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">
            <div class="panel-body">
              <div class="row">
                <div class="col-xs-12 col-md-6">
                  {!! Former::text('start_number')
                    ->label('Start number')
                    ->onchange('checkStartNumber()')
                    ->addGroupClass('start-number')
                    ->data_bind("value: start_number, valueUpdate: 'afterkeydown'") !!}                  
                </div>
              </div>
              <div class="row">
                <div class="col-xs-12 col-md-6">
                  {!! Former::text('number_invoices')
                    ->label('Number invoices')
                    ->disabled()
                    ->data_bind("value: number_invoices") !!}                  
                </div>
              </div>              
            </div>
            </div>

        </div>
    </div>

    <center class="buttons">

        {!! Button::normal(trans('texts.cancel'))
                ->large()
                ->asLinkTo(URL::to('/importcolt'))
                ->appendIcon(Icon::create('remove-circle')) !!}

        {!! Button::success(trans('texts.renumber_invoices'))
                ->submit()
                ->large()
                ->appendIcon(Icon::create('floppy-disk')) !!}

    </center>

    {!! Former::close() !!}


    <script type="text/javascript">
        var startNumber = '{!! $start_number !!}';
        var numberInvoices = {!! $number_invoices !!};

        function ViewModel() {
            this.start_number = ko.observable(startNumber);
            this.number_invoices = ko.observable(numberInvoices);
        }

        ko.applyBindings(new ViewModel());

        function checkInvoiceNumber(invoiceNumber) {
            return new Promise(function(resolve, reject) {
              var url = '{{ url('check_invoice_number') }}?invoice_number=' + encodeURIComponent(invoiceNumber);
              $.get(url, function(data) {
                var isValid = data == '{{ RESULT_SUCCESS }}' ? true : false;
                if (isValid) {
                    resolve(true);
                } else {
                    reject(invoiceNumber);
                }
              });
            })
        }

        function checkStartNumber() {
          var startNumber = $('#start_number').val();
          var match = startNumber.match(/(.*?)([1-9]+0*)$/);
          var start = match[2] ? match[2] : '0';
          var promises = [];
          for (var i = 0; i < numberInvoices; i++) {
            var invoiceNumber = match[1] + (Number(start) + i);
            promises.push(checkInvoiceNumber(invoiceNumber));
          }
          return Promise.all(promises)
            .then((result) => {
              $('.start-number')
                .removeClass('has-error')
                .find('span')
                .hide();
            })
            .catch((err) => {
              if ($('.start-number').hasClass('has-error')) {
                $('.start-number span.help-block').text('The Invoice Number ' + err + ' has already been taken.');
                return;
              }
              $('.start-number')
                  .addClass('has-error')
                  .find('div')
                  .append('<span class="help-block">The Invoice Number ' + err + ' has already been taken.</span>');

            });
        }

        function onFormSubmit(form) {
            // check invoice number is unique
            checkStartNumber()
              .then(() => {
                if ($('.start-number').hasClass('has-error')) {
                    return false;
                }
                form.submit();
              });
        }             
    </script>
@stop
