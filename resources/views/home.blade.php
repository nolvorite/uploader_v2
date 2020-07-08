@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-10">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('quickadmin.qa_dashboard')</div>

                <div class="panel-body">
                    @lang('quickadmin.qa_dashboard_text') Welcome, <strong>{{ auth()->user()->email }}</strong>.
                </div>
            </div>
        </div>
    </div>
@endsection
