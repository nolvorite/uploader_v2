@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@if(session()->exists('errors')):

{{ session('errors') }}

@endif

@section('content')
    <h3 class="page-title">@lang('quickadmin.folders.title')</h3>
    @can('folder_create')
        <p>
            <a href="{{ route('admin.folders.create') }}" class="btn btn-success">@lang('quickadmin.qa_add_new')</a>
        </p>
    @endcan

    @can('folder_delete')
        <p>
        <ul class="list-inline">
            <li><a href="{{ route('admin.folders.index') }}" style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">@lang('quickadmin.qa_all')</a></li>
            |
            <li><a href="{{ route('admin.folders.index') }}?show_deleted=1" style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">@lang('quickadmin.qa_trash')</a></li>
        </ul>
        </p>
    @endcan

    @include('partials.directorybrowser')

    <div class="panel panel-default">
        <div class="panel-heading">
            @lang('quickadmin.qa_list')
        </div>

        <div class="panel-body table-responsive">
            <table id="myTable" class="table table-bordered table-striped {{ count($folders) > 0 ? 'datatable' : '' }} @can('folder_delete') @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                <thead>
                <tr>
                    @can('folder_delete')
                        @if ( request('show_deleted') != 1 )
                            <th style="text-align:center;"><input type="checkbox" id="select-all"/></th>@endif
                    @endcan

                    <th>@lang('quickadmin.folders.fields.name')</th>
                    @if( $view === "all" || $view === "ror" )
                    <th>Creator</th>
                    @endif 

                    @if( request('show_deleted') == 1 )
                        <th>&nbsp;</th>
                    @else
                        <th>&nbsp;</th>
                    @endif
                </tr>
                </thead>

                <tbody>
                @if (count($folders) > 0)
                    @foreach ($folders as $folder)
                        <tr data-entry-id="{{ $folder->id }}">
                            @can('folder_delete')
                                @if ( request('show_deleted') != 1 )
                                    <td></td>@endif
                            @endcan

                            <td field-key='name'>
                                @can('folder_view')
                                    <a href="{{ route('admin.folders.show',[$folder->id]) }}/?currentBasePath={{ urlencode($folder->name) }}">{{$folder->name}}</a>
                                @endcan
                            </td>

                            @if( $view === "all" || $view === "ror" )
                            <td>{{ $folder->email }}</td>
                            @endif 

                            @if( request('show_deleted') == 1 )
                                <td>
                                    @can('folder_delete')
                                        {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'POST',
        'onsubmit' => "return confirm('".trans("quickadmin.qa_are_you_sure")."');",
        'route' => ['admin.folders.restore', $folder->id])) !!}
                                        {!! Form::submit(trans('quickadmin.qa_restore'), array('class' => 'btn btn-xs btn-success')) !!}
                                        {!! Form::close() !!}
                                    @endcan
                                    @can('folder_delete')
                                        {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("quickadmin.qa_are_you_sure")."');",
        'route' => ['admin.folders.perma_del', $folder->id])) !!}
                                        {!! Form::submit(trans('quickadmin.qa_permadel'), array('class' => 'btn btn-xs btn-danger')) !!}
                                        {!! Form::close() !!}
                                    @endcan
                                </td>
                            @else
                                <td>
                                    @can('folder_edit')
                                        <a href="{{ route('admin.folders.edit',[$folder->id]) }}" class="btn btn-xs btn-info">@lang('quickadmin.qa_edit')</a>
                                    @endcan
                                    @can('folder_delete')
                                        {!! Form::open(array(
                                                                                'style' => 'display: inline-block;',
                                                                                'method' => 'DELETE',
                                                                                'onsubmit' => "return confirm('".trans("quickadmin.qa_are_you_sure")."');",
                                                                                'route' => ['admin.folders.destroy', $folder->id])) !!}
                                        {!! Form::submit(trans('quickadmin.qa_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
                                        {!! Form::close() !!}
                                    @endcan
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7">@lang('quickadmin.qa_no_entries_in_table')</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('javascript')
    <script>
        $(document).ready(function () {
//            var table = $('#myTable_Wrapper').DataTable();
//console.log(table);
//            table.button( '.dt-button' ).remove();
        })
    </script>
    <script>
        @can('folder_delete')
                @if ( request('show_deleted') != 1 ) window.route_mass_crud_entries_destroy = '{{ route('admin.folders.mass_destroy') }}'; @endif
        @endcan

    </script>
@endsection