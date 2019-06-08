@extends('header')

@section('content')

    {!! Former::open($url)
            ->addClass('col-md-10 col-md-offset-1 warn-on-exit')
            ->method($method)
            ->setAttribute('id', 'telcopackageForm')
            ->rules([]) !!}

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

                {!! Former::text('name') !!}
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
                        {!! Former::text()
                            ->label('')
                            ->data_bind('value: code, attr: {name: \'codes[\' + $index() + \'][code]\'}')
                            ->required()
                        !!}
                    </div>
                    <div class="col-md-6">
                        {!! Former::text()
                            ->label('') 
                            ->data_bind('value: description, attr: {name: \'codes[\' + $index() + \'][description]\'}')
                            ->required()
                        !!}
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
    </script>
    

@stop
