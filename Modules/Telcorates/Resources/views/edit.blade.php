@extends('header')

@section('content')

    {!! Former::open($url)
            ->addClass('col-md-10 col-md-offset-1 warn-on-exit')
            ->method($method)
            ->setAttribute('id', 'telcorateForm')
            ->rules([]) !!}

    @if ($telcorates)
      {!! Former::populate($telcorates) !!}
      <div style="display:none">
          {!! Former::text('public_id') !!}
      </div>
    @endif

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">
            <div class="panel-body">

                {!! Former::text('name')
                    ->addGroupClass('rate-name')
                    ->onchange('checkName()')
                 !!}

                <div class="row" style="margin-top: 32px;" data-bind="visible: codes().length">
                    <div class="col-md-2">
                        Code
                    </div>
                    <div class="col-md-2">
                        Init Seconds
                    </div> 
                    <div class="col-md-2">
                        Increment Seconds
                    </div>  
                    <div class="col-md-2">
                        Rate
                    </div>                                                           
                    <div class="col-md-4">
                        Description
                    </div>
                </div>
                <div class="row" data-bind="foreach: codes">
                    {!! Former::hidden('public_id')
                        ->data_bind('value: id, attr: {name: \'codes[\' + $index() + \'][id]\'}')
                    !!}
                    <div class="col-md-2">
                        <div class="form-group required">
                            <div class="col-lg-12 col-sm-12">
                                <input class="form-control" 
                                    data-bind="value: code, attr: {name: 'codes[' + $index() + '][code]'}" 
                                    required type="text">
                            </div>
                        </div>                        
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <div class="col-lg-12 col-sm-12">
                                <input class="form-control" 
                                    data-bind="value: init_seconds, attr: {name: 'codes[' + $index() + '][init_seconds]'}" 
                                    type="text">
                            </div>
                        </div>                    
                    </div>  
                    <div class="col-md-2">
                        <div class="form-group">
                            <div class="col-lg-12 col-sm-12">
                                <input class="form-control" 
                                    data-bind="value: increment_seconds, attr: {name: 'codes[' + $index() + '][increment_seconds]'}" 
                                    type="text">
                            </div>
                        </div>                                           
                    </div>     
                    <div class="col-md-2">
                        <div class="form-group">
                            <div class="col-lg-12 col-sm-12">
                                <input class="form-control" 
                                    data-bind="value: rate, attr: {name: 'codes[' + $index() + '][rate]'}" 
                                    type="text">
                            </div>
                        </div>                                          
                    </div>                                                       
                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="col-lg-12 col-sm-12">
                                <input class="form-control" 
                                    data-bind="value: description, attr: {name: 'codes[' + $index() + '][description]'}" 
                                    type="text">
                            </div>
                        </div>                                          
                    </div>
                    <div class="col-md-1">
                        {!! Button::danger()
                            ->withAttributes(['data-bind' =>'click: $root.removeCode.bind($data, $index())'])
                            ->appendIcon('<i class="fa fa-times" aria-hidden="true"></i>')
                        !!}     
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        {!! 
                            Button::primary(trans('telcopackages::texts.add_code'))
                            ->withAttributes(['data-bind' =>'click: addCode'])
                            ->appendIcon(Icon::create('plus')) 
                        !!}
                        <button type="button" class="btn btn-info"
                            data-bind="disabled: isCsvUploading"
                            onClick="$('#uploadCsv').click()">
                            <span data-bind="ifnot: isCsvUploading">
                                Import CSV
                                <i class="fa fa-upload" aria-hidden="true"></i>
                            </span>
                            <span data-bind="if: isCsvUploading">
                                Uploading
                                <i class="fa fa-spinner fa-spin"></i>
                            </span>
                        </button>
                        <input type="file"
                            id="uploadCsv"
                            accept=".csv"
                            data-bind="event: { change: function() { uploadCsv($element.files[0]) } }"
                            name="csv_file" class="hidden">                        
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <center class="buttons">

        {!! Button::normal(trans('texts.cancel'))
                ->large()
                ->asLinkTo(URL::to('/telcorates'))
                ->appendIcon(Icon::create('remove-circle')) !!}

        {!! Button::success(trans('texts.save'))
                ->withAttributes(['id' =>'formSubmitButton'])
                ->submit()
                ->large()
                ->appendIcon(Icon::create('floppy-disk')) !!}

    </center>

    {!! Former::close() !!}


    <script type="text/javascript">

        $(function() {
            $(".warn-on-exit input").first().focus();
        })

        $('form').submit(function() {
            // check name is unique
            checkName(true);
            if ($('.rate-name').hasClass('has-error')) {
                return false;
            }            
            $('#formSubmitButton')
                .prop("disabled", true)
                .text('Saving...');
            return true;
        });

        const codes = @if ($telcorates) {!! $telcorates->codes !!} @else null @endif;

        function CodeViewModel() {

            this.codes = ko.observableArray([]);     

            this.isCsvUploading = ko.observable(false);

            this.addCode = (code = null, checkValidity = true) => {
                if (checkValidity && this.codes().length
                    && $.grep($('#telcorateForm [name^="codes"]'), item => !item.checkValidity()).length) {
                    $('#telcorateForm button[type=submit]')[0].click();
                    return true;
                }
                this.codes.push({
                    id: code ? code.id : null,
                    code: code ? code.code : '',
                    init_seconds: code ? code.init_seconds : '',
                    increment_seconds: code ? code.increment_seconds : '',
                    rate: code ? code.rate : '',
                    description: code ? code.description : '',
                });
            };

            this.removeCode = (code, index) => {
                this.codes.splice(index, 1);
                if (!this.codes().length) {
                    this.addCode();
                }
            }

            if (codes && codes.length) {
                codes.forEach(code => this.addCode(code));
            } else {
                this.addCode();
            }

            this.uploadCsv = (file) => {
                if (file) {
                    if (window.FileReader) {
                        this.isCsvUploading(true);                     
                        const fileExt = file.name.split('.').pop();
                        if (fileExt !== 'csv') {
                            console.log('upload file extension is wrong');
                            return false;
                        }
                        this.fileReader = new FileReader();
                        this.fileReader.onabort = () => {
                            $('#uploadCsv')[0].value = null;
                            this.uploadState = 'error';
                        };

                        this.fileReader.onload = (event) => {
                            const csv = event.target.result;
                            parseCsv(csv).forEach(code => this.addCode(code, false));
                            $('#uploadCsv')[0].value = null;
                            this.isCsvUploading(false);
                        };
                        this.fileReader.onerror = () => {
                            this.uploadState = 'error';
                        };
                        this.fileReader.readAsText(file);
                    } else {
                        console.error('FileReader is not supported in this browser.');
                    }
                }
                return true;
            } 
        }

        ko.applyBindings(new CodeViewModel());

        function parseCsv(data) {
            const codes = [];
            data.split('\n').forEach((item) => {
                const line = item.split(',');
                if (line.length && line[0]) {
                    codes.push({
                        code: line[0],
                        init_seconds: line[1] ? line[1] : '',
                        increment_seconds: line[2] ? line[2] : '',
                        rate: line[3] ? line[3] : '',
                        description: line[4] ? line[4] : '',
                    })
                }
            });
            return codes;
        }

        function checkName(syncMode = false) {
            var url = '/telcorates/check_name{{ $telcorates && $telcorates->id ? '/' . $telcorates->public_id : '' }}?name=' + encodeURIComponent($('#name').val());

            $.ajax({
                url: url, 
                success: function(data) {
                    var isValid = data == '{{ RESULT_SUCCESS }}' ? true : false;
                    if (isValid) {
                        $('.rate-name')
                            .removeClass('has-error')
                            .find('span')
                            .hide();
                    } else {
                        if ($('.rate-name').hasClass('has-error')) {
                            return;
                        }
                        $('.rate-name')
                            .addClass('has-error')
                            .find('div')
                            .append('<span class="help-block">{{ trans('validation.unique', ['attribute' => trans('texts.name')]) }}</span>');
                    }
                },
                async: !syncMode
            });
        }         
    </script>
    

@stop
