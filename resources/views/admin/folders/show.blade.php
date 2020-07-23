@extends('layouts.app')

@section('content')
    <h3 class="page-title">{{$folder->email}}/{{$folder->name}}</h3>
    <p>


    <a href="{{url('admin/files/create?folder_id=' . $folder->id)}}" class="btn btn-success">Add file to this Folder</a>
  
    </p>
    @include('partials.directorybrowser')
    <div class="panel panel-default">
        <div class="panel-heading">
            Files
        </div>
        {{--<div class="tab-content">--}}

        {{--<div role="tabpanel" class="tab-pane active" id="files">--}}
        <div class="panel-body table-responsive">
            <table class="table table-bordered table-striped {{ count($files) > 0 ? 'datatable' : '' }} @can('file_delete') @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                <thead>
                <tr>
                    @can('file_delete')
                        @if ( request('show_deleted') != 1 )
                            <th style="text-align:center;"><input type="checkbox" id="select-all"/></th>@endif
                    @endcan

                    <th>Filename</th>

                    @if( $view === "all" && Auth::getUser()->role_id === 1 )
                    <th>Creator</th>
                    @endif 
<th>&nbsp;</th>
                   
                </tr>
                </thead>

                <tbody>
                @if (count($files) > 0)
                    @foreach ($files as $file)
                        <tr data-entry-id="{{ $file->id }}">
                            @can('file_delete')
                                @if ( request('show_deleted') != 1 )
                                    <td></td>@endif
                            @endcan
                            <td field-key='filename'> 
                                    <p class="form-group">
                                        <a href="{{url('/storage/' . $file->folder_creator . '/'. $file->folder_name .'/' . $file->file_name )}}" target="_blank">{{ $file->file_name }} ({{ $file->size }} KB)</a><br>
                                        <span><strong>Full Path:</strong> <span class='full_path'><a href="{{ url('admin/files?currentBasePath='.$file->path) }}">{{$file->path}}</a> </span></span><br>
                                        <span><strong>Time Created:</strong> <span class='time_created'>{{ $file->created_at }}</span></span>
                                    </p>
                                </td>

                            @if( $view === "all" && auth()->getUser()->role_id === 1 )
                            <td field-key='email'>{{ $file->email or '' }}</td>
                            @endif 

                             @if( request('show_deleted') == 1 )
                                <td>
                                    @if (Auth::getUser()->role_id == 2 && $userFilesCount >= 5)
                                        @can('file_delete')
                                            {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'DELETE',
            'onsubmit' => "return confirm('".trans("quickadmin.qa_are_you_sure")."');",
            'route' => ['admin.files.perma_del', $file->id])) !!}
                                            {!! Form::submit(trans('quickadmin.qa_permadel'), array('class' => 'btn btn-xs btn-danger')) !!}
                                            {!! Form::close() !!}
                                        @endcan
                                    @else
                                    @can('file_delete')
                                        {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'POST',
        'onsubmit' => "return confirm('".trans("quickadmin.qa_are_you_sure")."');",
        'route' => ['admin.files.restore', $file->id])) !!}
                                        {!! Form::submit(trans('quickadmin.qa_restore'), array('class' => 'btn btn-xs btn-success')) !!}
                                        {!! Form::close() !!}
                                    @endcan
                                    @can('file_delete')
                                        {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("quickadmin.qa_are_you_sure")."');",
        'route' => ['admin.files.perma_del', $file->id])) !!}
                                        {!! Form::submit(trans('quickadmin.qa_permadel'), array('class' => 'btn btn-xs btn-danger')) !!}
                                        {!! Form::close() !!}
                                    @endcan
                                        @endif
                                </td>

                            @else
                                <td>
                                    <a href="{{url('/storage/' . $file->folder_creator . '/'. $file->folder_name .'/' . $file->file_name )}}" class="btn btn-xs btn-success">Download</a>
                                    @can('file_delete')
                                        {!! Form::open(array(
                                                                                'style' => 'display: inline-block;',
                                                                                'method' => 'DELETE',
                                                                                'onsubmit' => "return confirm('".trans("quickadmin.qa_are_you_sure")."');",
                                                                                'route' => ['admin.files.destroy', $file->id])) !!}
                                        {!! Form::submit(trans('quickadmin.qa_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
                                        {!! Form::close() !!}
                                    @endcan
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9">@lang('quickadmin.qa_no_entries_in_table')</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
    {{--</div>--}}
    {{--</div>--}}

    <p>&nbsp;</p>

    <a href="{{ route('admin.folders.index') }}" class="btn btn-default">@lang('quickadmin.qa_back_to_list')</a>


@stop
@section('javascript')
    @parent
    <script type="text/javascript">
        folderData = $.parseJSON('{!! json_encode($folder) !!}');

    </script>
@endsection