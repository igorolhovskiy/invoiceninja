@extends('header')

@section('head')
	@parent

    <script src="{{ asset('js/select2.min.js') }}" type="text/javascript"></script>
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" type="text/css"/>
    <script src="{{ asset('js/knockstrap.min.js') }}" type="text/javascript"></script>

@stop

@section('content')
    @if ($method === 'POST')
    {!! Former::open($url)
            ->addClass('col-md-12 warn-on-exit')
            ->method($method)
            ->rules([]) !!}
    @endif
    <div class="row">
        <div class="col-md-10 col-md-offset-1">    
            <div class="panel panel-default">
                <div class="panel-body">
                    @if ($method === 'POST')
                    <h4>Select invoices for export:</h3>
                    @else
                    <h4>Invoices to export:</h3>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-striped data-table dataTable">
                            <thead>
                                <tr role="row">
                                    @if ($method === 'POST')
                                    <th>
                                        <label>
                                            <input type="checkbox" data-bind="checked: isCheckedAll">
                                        </label>
                                    </th>
                                    @endif
                                    <th>Invoice Number</th>
                                    <th>Client Name</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody data-bind="foreach: invoicesData">
                                <tr role="row"
                                    data-bind="if: $index() >= ($parent.page() - 1) * $parent.pageSize() && $index() < $parent.page() * $parent.pageSize(),
                                        css: {'alert alert-danger': !is_filled_sepa_data}">
                                    @if ($method === 'POST')
                                    <td>
                                        <label data-bind="if: is_filled_sepa_data">
                                            <input type="checkbox" data-bind="checked: isChecked">
                                        </label>
                                        <span data-bind="if: !is_filled_sepa_data"
                                            data-toggle="tooltip" title="Please, feel SEPA data for client!"
                                        >
                                            <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                                        </span>
                                        <div data-bind="if: isChecked()">
                                            <input data-bind="value: public_id"
                                                name="invoice_id[]" type="hidden">
                                        </div>
                                    </td>
                                    @endif
                                    <td data-bind="text: invoice_number"></td>
                                    <td data-bind="text: client_name"></td>
                                    <td align="center" data-bind="text: invoice_date"></td>                                    
                                    <td align="right" data-bind="text: amount"></td>
                                    <td align="center" data-bind="text: due_date_sql"></td>
                                    <td align="center" data-bind="text: status"></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="pull-left" style="margin-top:20px;"
                            data-bind="text: showingText">
                        </div>
                        <div class="pull-right" data-bind="pagination: { 
                            currentPage: page, totalCount: total, pageSize: pageSize, maxPages: maxPages, 
                            directions: directions, boundary: boundary, text: text }"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>  

    <center class="buttons">

        {!! Button::normal(trans('texts.cancel'))
                ->large()
                ->asLinkTo(URL::to('/exportsepa'))
                ->appendIcon(Icon::create('remove-circle')) !!}
        @if ($method === 'POST')
        {!! Button::success(trans('texts.export'))
                ->submit()
                ->large()
                ->appendIcon(Icon::create('floppy-disk')) !!}
        @endif
    </center>

    {!! Former::close() !!}    
  
    <script type="text/javascript">

        $(function() {
            $(".warn-on-exit input").first().focus();
        })

        const pageSize = 10;
        const invoices = @if ($invoices) {!! $invoices !!} @else null @endif;

        function ViewModel() {
            // Pagination model
            this.page = ko.observable(1);
            this.total = ko.observable(invoices.length);
            this.maxPages = ko.observable(5);
            this.pageSize = ko.observable(pageSize);
            this.directions = ko.observable(true);
            this.boundary = ko.observable(true);
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

            // =======            
            this.invoicesData = ko.observableArray([]);
            this.isCheckedAll = ko.observable(1);
            if (invoices && invoices.length) {
                invoices.forEach((invoice) => {
                    this.invoicesData.push({
                        isChecked: ko.observable(true),
                        ...invoice
                    });
                });
            }

            this.isCheckedAll.subscribe((newValue) => {
                for (let i = 0; i < this.invoicesData().length; i++) {
                    this.invoicesData()[i].isChecked(newValue);
                }
            })
        }

        ko.applyBindings(new ViewModel());
    </script>
    

@stop
