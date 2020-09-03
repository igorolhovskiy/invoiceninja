@extends('header')

@section('head')
	@parent
    <script src="{{ asset('js/knockstrap.min.js') }}" type="text/javascript"></script>
    <style>
        .row-list {
            border-top: 1px solid lightgrey;
            padding-top: 8px;
            padding-bottom: 8px;
            line-height: 40px;
        }
    </style>
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
                <div data-bind="foreach: {data: codes, afterRender: handleAfterAllRender}" id="rowsContainer">
                    <div class="row row-list">
                        <div class="col-md-2" data-bind="text: code">                     
                        </div>
                        <div class="col-md-2" data-bind="text: init_seconds">                 
                        </div>  
                        <div class="col-md-2" data-bind="text: increment_seconds">                                          
                        </div>     
                        <div class="col-md-2" data-bind="text: rate">                                      
                        </div>                                                       
                        <div class="col-md-2" data-bind="text: description">                                         
                        </div>
                        <div class="col-md-2">
                            {!! Button::primary()
                                ->withAttributes(['data-bind' =>'click: $root.editCode.bind($data)'])
                                ->appendIcon('<i class="fa fa-edit" aria-hidden="true"></i>')
                            !!}    
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
                            ->withAttributes(['data-bind' =>'click: onAddCodeClick'])
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


    <!-- Add code -->
    <div class="modal fade" id="addCodeModal" tabindex="-1" role="dialog" aria-labelledby="addCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Add Code</h4>
            </div>

            <div class="container" style="width: 100%; padding-bottom: 0px !important">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row" style="margin-top: 16px;">
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
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group required">
                                    <div class="col">
                                        <input class="form-control"
                                            name="code"
                                            data-bind="value: newItem.code" 
                                            required type="text">
                                    </div>
                                </div>                        
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="col">
                                        <input class="form-control" 
                                            data-bind="value: newItem.init_seconds" 
                                            type="text">
                                    </div>
                                </div>                    
                            </div>  
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="col">
                                        <input class="form-control" 
                                            data-bind="value: newItem.increment_seconds" 
                                            type="text">
                                    </div>
                                </div>                                           
                            </div>     
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="col">
                                        <input class="form-control" 
                                            data-bind="value: newItem.rate" 
                                            type="text">
                                    </div>
                                </div>                                          
                            </div>                                                       
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="col">
                                        <input class="form-control" 
                                            data-bind="value: newItem.description" 
                                            type="text">
                                    </div>
                                </div>                                          
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" 
                    data-bind="click: onAddItemClick, disable: isSubmitting()">
                    <span data-bind="text: isSubmitting() ? 'Adding...' : 'Add code'"></span>
                </button>
            </div>
            </div>
        </div>
    </div>

    <!-- Edit code -->
    <div class="modal fade" id="editCodeModal" tabindex="-1" role="dialog" aria-labelledby="editCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Edit Code</h4>
            </div>

            <div class="container" style="width: 100%; padding-bottom: 0px !important">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row" style="margin-top: 16px;">
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
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group required">
                                    <div class="col">
                                        <input class="form-control"
                                            name="code"
                                            data-bind="value: editableItem.code" 
                                            required type="text">
                                    </div>
                                </div>                        
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="col">
                                        <input class="form-control" 
                                            data-bind="value: editableItem.init_seconds" 
                                            type="text">
                                    </div>
                                </div>                    
                            </div>  
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="col">
                                        <input class="form-control" 
                                            data-bind="value: editableItem.increment_seconds" 
                                            type="text">
                                    </div>
                                </div>                                           
                            </div>     
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="col">
                                        <input class="form-control" 
                                            data-bind="value: editableItem.rate" 
                                            type="text">
                                    </div>
                                </div>                                          
                            </div>                                                       
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="col">
                                        <input class="form-control" 
                                            data-bind="value: editableItem.description" 
                                            type="text">
                                    </div>
                                </div>                                          
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" 
                    data-bind="click: onEditItemClick, disable: isSubmitting()">
                    <span data-bind="text: isSubmitting() ? 'Saving...' : 'Save'"></span>
                </button>
            </div>
            </div>
        </div>
    </div>

    <!-- Delete code -->
    <div class="modal fade" id="deleteCodeModal" tabindex="-1" role="dialog" aria-labelledby="deleteCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Delete Code</h4>
            </div>

            <div class="container" style="width: 100%; padding-bottom: 0px !important">
            <div class="panel panel-default">
            <div class="panel-body">
                <h3>Are you sure?</h3><br/>
                <p>Code <b data-bind="text: removableItem() ? removableItem().code : ''"></b> will be deleted.</p>
            </div>
            </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" 
                    data-bind="click: onDeleteItemClick, disable: isSubmitting">
                    <span data-bind="text: isSubmitting() ? 'Deleting...' : 'Delete'"></span>
                </button>
            </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        const telcoratePublicId = @if ($telcorates) {{ $telcorates->public_id }} @else null @endif;
        const codes = null;
        let model = null;
        const codesApiUrl = @if (!empty($telcoCodesUrl)) '{{ $telcoCodesUrl }}' @else null @endif;

        function CodeViewModel() {
            this.codes = ko.observableArray([]);
            this.searchText = ko.observable('').extend({ rateLimit: 500 });
            this.isCsvUploading = ko.observable(false);
            this.isSubmitting = ko.observable(false);
            this.newItem = {
              code: ko.observable(),
              init_seconds: ko.observable(),
              increment_seconds: ko.observable(),
              rate: ko.observable(),
              description: ko.observable(),
            };
            this.editableItem = {
                id: ko.observable(),
                code: ko.observable(),
                init_seconds: ko.observable(),
                increment_seconds: ko.observable(),
                rate: ko.observable(),
                description: ko.observable(),
            };
            this.removableItem = ko.observable();

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

            this.onAddCodeClick = () => {
                this.newItem.code('');
                this.newItem.init_seconds('');
                this.newItem.increment_seconds('');
                this.newItem.rate('');
                this.newItem.description('');
                $('#addCodeModal').modal('show');
            };

            this.onAddItemClick = () => {
                const codeInputEl = document.getElementById('addCodeModal').querySelector('input[name="code"]');
                if (codeInputEl.checkValidity()) {
                    this.isSubmitting(true);
                    $.ajax(
                        {   
                            url: codesApiUrl,
                            method: 'POST',
                            data: this.newItem,
                        }
                    )
                    .done((response) => {
                        this.loadPage();
                    })
                    .fail(( jqXHR, textStatus ) => {
                        console.log('error during request:', jqXHR.statusText);
                    })
                    .always(() => {
                        this.isSubmitting(false);
                        $('#addCodeModal').modal('hide');
                    });
                } else {
                    codeInputEl.reportValidity();
                }
            }

            this.editCode = (item) => {
                this.editableItem.id(item.id);
                this.editableItem.code(item.code);
                this.editableItem.init_seconds(item.init_seconds);
                this.editableItem.increment_seconds(item.increment_seconds);
                this.editableItem.rate(item.rate);
                this.editableItem.description(item.description);
                $('#editCodeModal').modal('show');
            };

            this.onEditItemClick = () => {
                const codeInputEl = document.getElementById('editCodeModal').querySelector('input[name="code"]');
                if (codeInputEl.checkValidity()) {
                    this.isSubmitting(true);
                    $.ajax(
                        {   
                            url: codesApiUrl,
                            method: 'PUT',
                            data: this.editableItem,
                        }
                    )
                    .done((response) => {
                        this.loadPage();
                    })
                    .fail(( jqXHR, textStatus ) => {
                        console.log('error during request:', jqXHR.statusText);
                    })
                    .always(() => {
                        this.isSubmitting(false);
                        $('#editCodeModal').modal('hide');
                    });
                } else {
                    codeInputEl.reportValidity();
                }
            }

            this.removeCode = (item) => {
                if (item.id) {
                    this.removableItem(item);
                    $('#deleteCodeModal').modal('show');
                } else {
                    this.codes.remove(item);
                    if (!this.codes().length) {
                        this.addCode();
                    }
                }
            }

            this.onDeleteItemClick = () => {
                this.isSubmitting(true);
                $.ajax(
                    {   
                        url: codesApiUrl,
                        method: 'DELETE',
                        data: {
                            id: this.removableItem().id
                        },
                    }
                )
                .done((response) => {
                    this.loadPage();
                })
                .fail(( jqXHR, textStatus ) => {
                    console.log('error during request:', jqXHR.statusText);
                })
                .always(() => {
                    this.isSubmitting(false);
                    $('#deleteCodeModal').modal('hide');
                });
            }

            this.handleAfterAllRender = (node) => {
                if ($('#rowsContainer').children().length === this.codes().length) {
                    $( document ).trigger( "codesRenderingCompleted");
                }
            }

            this.initPaginator = () => {
                // Pagination model
                this.page = ko.observable(1);
                this.total = ko.observable(0);
                this.maxPages = ko.observable(5);
                this.pageSize = ko.observable(10);
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
                    this.loadPage(1);
                });

                this.page.subscribe((value) => {
                    this.loadPage();
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
                            const codes = parseCsv(csv);
                            if (codes.length) {
                                this.uploadCodes(codes);
                            } else {
                                this.isCsvUploading(false);
                            }
                            $('#uploadCsv')[0].value = null;
                            
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

            this.uploadCodes = (codes) => {
                this.isSubmitting(true);
                    $.ajax(
                        {   
                            url: codesApiUrl + '/bulk-upload',
                            method: 'POST',
                            data: {
                                codes
                            },
                        }
                    )
                    .done((response) => {
                        this.loadPage();
                    })
                    .fail(( jqXHR, textStatus ) => {
                        console.log('error during request:', jqXHR.statusText);
                    })
                    .always(() => {
                        this.isSubmitting(false);
                        this.isCsvUploading(false);
                    });
            }

            this.loadPage = (page = this.page()) => {
                this.page(page);
                $.get(codesApiUrl, {
                    page: this.page(),
                    search: this.searchText()
                }, (data) => {
                    this.total(data.total);
                    this.pageSize(data.per_page);
                    const codes = data.data;
                    this.codes.removeAll();
                    if (codes && codes.length) {
                        codes.forEach(code => this.addCode(code));
                    } else {
                        this.addCode();
                    }
                })
            }

            this.initPaginator();
            this.loadPage();
             
        }
        
        model = new CodeViewModel();
        ko.applyBindings(model);

        $(function() {
            $(".warn-on-exit input").first().focus();
        })

        $('form').submit(function(event) {
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
                const line = item.split(/,|;/);
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
