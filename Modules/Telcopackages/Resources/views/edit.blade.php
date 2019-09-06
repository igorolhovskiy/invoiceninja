@extends('header')

@section('content')

    {!! Former::open($url)
            ->addClass('col-md-10 col-md-offset-1 warn-on-exit')
            ->method($method)
            ->setAttribute('id', 'telcopackageForm')
            ->rules([array(
        		'name' => 'required',
        	)]) !!}

    @if ($telcopackages)
      {!! Former::populate($telcopackages) !!}
      <div style="display:none">
          {!! Former::text('public_id') !!}
      </div>
    @endif

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">
            <div class="panel-body">

                {!! Former::text('name')
                    ->addGroupClass('package-name')
                    ->onchange('checkName()')
                !!}
{!! Former::text('amount_of_minutes') !!}
{!! Former::text('price') !!}


            </div>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">
            <div class="panel-body">
                <div class="row" data-bind="visible: codes().length">
                    <div class="col-md-4">
                        Code
                    </div>
                    <div class="col-md-8">
                        Description
                    </div>
                </div>
                <div class="row" data-bind="foreach: codes">
                    {!! Former::hidden('public_id')
                        ->data_bind('value: id, attr: {name: \'codes[\' + $index() + \'][id]\'}')
                    !!}
                    <div class="col-md-4">
                        <div class="form-group required">
                            <div class="col-lg-12 col-sm-12">
                                <input class="form-control" 
                                    data-bind="value: code, attr: {name: 'codes[' + $index() + '][code]'}" 
                                    required type="text">
                            </div>
                        </div>                         
                    </div>
                    <div class="col-md-6">
                        <div class="form-group required">
                            <div class="col-lg-12 col-sm-12">
                                <input class="form-control" 
                                    data-bind="value: description, attr: {name: 'codes[' + $index() + '][description]'}" 
                                    required type="text">
                            </div>
                        </div>                        
                    </div>
                    <div class="col-md-2">
                        {!! Button::danger()
                            ->withAttributes(['data-bind' =>'click: $root.removeCode'])
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
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <center class="buttons">

        {!! Button::normal(trans('texts.cancel'))
                ->large()
                ->asLinkTo(URL::to('/telcopackages'))
                ->appendIcon(Icon::create('remove-circle')) !!}

        {!! Button::success(trans('texts.save'))
                ->withAttributes(['id' =>'formSubmitButton'])
                ->submit()
                ->large()
                ->appendIcon(Icon::create('floppy-disk')) !!}

    </center>

    {!! Former::close() !!}


    <script type="text/javascript">
        const codes = @if ($telcopackages) {!! $telcopackages->codes !!} @else null @endif;

        $(function() {
            $(".warn-on-exit input").first().focus();
        })
        
        $('form').submit(function(e) {
            // check name is unique
            checkName(true);
            if ($('.package-name').hasClass('has-error')) {
                return false;
            }
            $('#formSubmitButton')
                .prop("disabled", true)
                .text('Saving...');                    
            return true;
        });

        function CodeViewModel() {
            this.codes = ko.observableArray([]);     

            this.addCode = (code = null) => {
                if (this.codes().length && $.grep($('#telcopackageForm [name^="codes"]'), item => !item.checkValidity()).length) {
                    $('#telcopackageForm button[type=submit]')[0].click();
                    return true;
                }
                this.codes.push({
                    id: code ? code.id : null,
                    code: code ? code.code : '',
                    description: code ? code.description : '',
                });
            };

            this.removeCode = (code) => this.codes.remove(code);

            if (codes && codes.length) {
                codes.forEach(code => this.addCode(code));
            } else {
                this.addCode();
            }
        }

        ko.applyBindings(new CodeViewModel());

        function checkName(syncMode = false) {
            var url = '/telcopackages/check_name{{ $telcopackages && $telcopackages->id ? '/' . $telcopackages->public_id : '' }}?name=' + encodeURIComponent($('#name').val());

            $.ajax({
                url: url, 
                success: function(data) {
                    var isValid = data == '{{ RESULT_SUCCESS }}' ? true : false;
                    if (isValid) {
                        $('.package-name')
                            .removeClass('has-error')
                            .find('span')
                            .hide();
                    } else {
                        if ($('.package-name').hasClass('has-error')) {
                            return;
                        }
                        $('.package-name')
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
