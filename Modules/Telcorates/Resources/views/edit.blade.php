@extends('header')

@section('head')
	@parent
    <script src="{{ asset('js/knockstrap.min.js') }}" type="text/javascript"></script>

@stop

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

                 {!! Former::text('description')
                    ->addGroupClass('rate-description')
                 !!}
                 <div class="row" data-bind="visible: codes().length">
                    <div class="col-12 col-md-4">
                        <input type="search" class="form-control"
                            data-bind="value: searchText, valueUpdate: 'afterkeydown'"
                            placeholder="Search...">
                    </div>
                </div>
                <div class="row" style="margin-top: 16px;" data-bind="visible: codes().length">
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
                <div data-bind="foreach: {data: filteredCodes, afterRender: handleAfterAllRender}" id="rowsContainer">
                    <div class="row"
                        data-bind="visible: $index() >= ($parent.page() - 1) * $parent.pageSize() && $index() < $parent.page() * $parent.pageSize()">
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
                                ->withAttributes(['data-bind' =>'click: $root.removeCode.bind($data)'])
                                ->appendIcon('<i class="fa fa-times" aria-hidden="true"></i>')
                            !!}     
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4" style="margin-top:20px;"
                        data-bind="text: showingText">
                    </div>
                    <div class="col-12 col-md-8 text-right" data-bind="pagination: { 
                        currentPage: page, totalCount: total, pageSize: pageSize, maxPages: maxPages, 
                        directions: directions, boundary: boundary, text: text }">
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

        <button type="submit" class="btn btn-success btn-lg" id="formSubmitButton" 
            data-bind="disable: isSubmitting">
            <span data-bind="text: isSubmitting() ? `Saving...` : `Save`"></span>
            <span class="glyphicon glyphicon-floppy-disk"></span>
        </button>
    </center>

    {!! Former::close() !!}


    <script type="text/javascript">
        const pageSize = 10;
        const codes = @if ($telcorates) {!! $telcorates->codes !!} @else null @endif;
        let model = null;

        function CodeViewModel() {
            this.codes = ko.observableArray([]);
            this.searchText = ko.observable('');
            this.isCsvUploading = ko.observable(false);
            this.isSubmitting = ko.observable(false);

            this.addCode = (code = null, checkValidity = true) => {
                if (this.lastPage() > 0) {
                    this.page(this.lastPage());
                }
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

            this.removeCode = (item) => {
                this.codes.remove(item);
                if (!this.codes().length) {
                    this.addCode();
                }
            }

            this.filteredCodes = ko.computed(() => {
                const searchText = this.searchText();
                if (!searchText) {
                    return this.codes();
                }
                return ko.utils.arrayFilter(this.codes(), item => 
                        item.code && item.code.indexOf(searchText) !== -1
                        || item.rate && item.rate.toString().indexOf(searchText) !== -1
                        || item.description && item.description.indexOf(searchText) !== -1
                        || (!item.id && !item.code && !item.init_seconds 
                            && !item.rate && !item.description) // added item
                    )
            });

            this.handleAfterAllRender = (node) => {
                if ($('#rowsContainer').children().length === this.filteredCodes().length) {
                    $( document ).trigger( "codesRenderingCompleted");
                }
            }

            this.initPaginator = () => {
                // Pagination model
                this.page = ko.observable(1);
                this.total = ko.computed(() => this.filteredCodes().length);
                this.maxPages = ko.observable(5);
                this.pageSize = ko.observable(pageSize);
                this.directions = ko.observable(true);
                this.boundary = ko.observable(true);
                this.lastPage = ko.computed(() => Math.ceil(this.total() / this.pageSize()));
                this.text = {
                    first: ko.observable('First'),
                    last: ko.observable('Last'),
                    back: ko.observable('«'),
                    forward: ko.observable('»')
                };

                this.showingText = ko.computed(() => {
                    let startNumber = (this.page() - 1) * this.pageSize() + 1;
                    let endNumber = this.page() * this.pageSize();
                    return `Showing ${startNumber} to ${endNumber > this.total() ? this.total() : endNumber} of ${this.total()} entries`;
                });

                this.searchText.subscribe(() => {
                    this.page(1);
                });
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

            this.initPaginator();
            if (codes && codes.length) {
                codes.forEach(code => this.addCode(code));
                this.page(1);
            } else {
                this.addCode();
            }
             
        }
        
        model = new CodeViewModel();
        ko.applyBindings(model);

        $(function() {
            $(".warn-on-exit input").first().focus();
        })

        $('form').submit(function(event) {
            console.log('submit');
            const form = this;
            event.preventDefault();
            model.isSubmitting(true);            
            // check name is unique
            checkName(true);
            if ($('.rate-name').hasClass('has-error')) {
                return false;
            }
            // If filter is not empty we remove filter and wait render all codes
            if (model.searchText()) {
                $( document ).one( "codesRenderingCompleted", function() {
                    form.submit();
                });            
                model.searchText('');
            } else {
                form.submit();
            }
            return true;
        });

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
