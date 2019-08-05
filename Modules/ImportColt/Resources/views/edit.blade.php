@extends('header')

@section('content')

    {!! Former::open($url)
            ->addClass('col-md-10 col-md-offset-1 warn-on-exit')
            ->method($method)
            ->rules(array(
                'invoice_date' => 'required'
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
                    <div class="col-xs-12 col-md-8">
                        {!! 
                            Former::file('name')
                                ->id('colt-file')
                                ->accept('.csv')
                                ->label('COLT file')
                                ->class('form-control')
                                ->data_bind("value: coltFileName, event: { change: function() { onFileChange(\$element.files[0]) } }")
                                ->help(' ');
                        !!}
                        <input type="hidden" name="file_path" data-bind="value: coltFilePath">
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <button type="button" class="btn btn-primary"
                            data-bind="enable: coltFileName() && !isProcess(), click: uploadCsv">
                            <span data-bind="text: (isProcess() ? 'Wait, Uploading...' : 'Upload')"></span>
                            <i class="fa fa-upload" aria-hidden="true" data-bind="visible: !isProcess()"></i>
                            <i class="fa fa-spinner fa-spin" aria-hidden="true" data-bind="visible: isProcess"></i>
                        </button>
                    </div>
                </div>
                <div class="table-responsive" data-bind="visible: isColtFileParsed">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>DID</th>
                                <th>Duration</th>
                                <th>Destination</th>
                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody data-bind="foreach: coltData">
                            <tr>
                                <td align="center" colspan=5 data-bind="text: ('CSV Line ' + ($index() + 1))">
                                </td>
                            </tr>
                            <tr>
                                <td data-bind="text: client ? client.name : '-'"></td>
                                <td data-bind="text: did"></td>
                                <td data-bind="text: dur"></td>
                                <td data-bind="text: dst"></td>
                                <td data-bind="text: cost"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="row" style="margin-top: 16px;" data-bind="visible: isColtFileParsed">
                    <div class="col-xs-12 col-md-8">
                        {!! Former::text('invoice_date')
                            ->data_bind("datePicker: invoice_date, valueUpdate: 'afterkeydown'")
                            ->label('Invoice date')
                            ->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT, DEFAULT_DATE_PICKER_FORMAT))->appendIcon('calendar')->addGroupClass('invoice_date') !!}

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

        {!! Button::success(trans('texts.process_invoice'))
                ->submit()
                ->large()
                ->withAttributes(['data-bind' => "visible: isColtFileParsed"])
                ->appendIcon(Icon::create('floppy-disk')) !!}

    </center>

    {!! Former::close() !!}


    <script type="text/javascript">

        $(function() {
            $(".warn-on-exit input").first().focus();
            $('#invoice_date').datepicker();
            $('.invoice_date .input-group-addon').click(function() {
                toggleDatePicker('invoice_date');
            });         
        })

        function ViewModel() {
            this.invoice_date = ko.observable('');
            this.coltFileName = ko.observable('');
            this.coltFilePath = ko.observable('');
            this.isColtFileParsed = ko.observable(false);
            this.isProcess = ko.observable(false);
            this.coltData = ko.observableArray();

            this.onFileChange = (file) => {
                this.isColtFileParsed(false);
                this.coltData.removeAll();
            }

            this.uploadCsv = () => {
                console.log('upload');
                this.isProcess(true);
                this.isColtFileParsed(false);
                var self = this;
                var file_data = $('#colt-file').prop('files')[0];   
                var form_data = new FormData();                  
                form_data.append('name', file_data);
                setValidationClassToField('name', '', 'has-error', '');         
                $.ajax({
                    url: '/importcolt/upload', // point to server-side PHP script 
                    dataType: 'json',  // what to expect back from the PHP script, if anything
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,                         
                    type: 'post',
                    success: function(response){
                       console.log('response: ', response);
                       self.isProcess(false);
                       self.isColtFileParsed(true);
                       self.coltData.splice(0);
                       response.data.forEach(item => {
                           self.coltData.push(item);
                       });
                       self.coltFilePath(response.coltFilePath);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        self.isProcess(false);
                        if (xhr.status === 422) {
                            const errors = JSON.parse(xhr.responseText);
                            console.log('errors:', errors);
                            for(fieldName in errors) {
                                setValidationClassToField(fieldName, 'has-error', '', errors[fieldName][0]);
                            }
                        }
                        console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }                    
                });                
            }
        }

        ko.applyBindings(new ViewModel());

        function setValidationClassToField (fieldName, addClass, removeClass, helpText) {
            const inputEl = $(`input[name=${fieldName}]`);
            if (addClass) {
                inputEl.closest('.form-group').addClass(addClass);
            }
            if (removeClass) {
                inputEl.closest('.form-group').removeClass(removeClass);
            }
            inputEl.siblings('.help-block').text(helpText);
        }
    </script>
    

@stop
