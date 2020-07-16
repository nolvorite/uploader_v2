@extends('layouts.app')

@section('content')
    <h3 class="page-title">@lang('quickadmin.files.title')</h3>

    <div class="panel panel-default">
        <div class="panel-heading">
            @lang('quickadmin.qa_view')
        </div>

        <div class="panel-body table-responsive">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>@lang('quickadmin.files.fields.folder')</th>
                            <td field-key='folder'>{{ $file->folder->name or '' }}</td>
                        </tr>
                        <tr>
                            <th>@lang('quickadmin.files.fields.created-by')</th>
                            <td field-key='created_by'>{{ $file->created_by->name or '' }}</td>
                        </tr>
                        <tr>
                            <th>@lang('quickadmin.files.fields.filename')</th>
                            <td field-key='filename'> 
                                    <p class="form-group">
                                        <a href="{{url('/storage/' . $file->created_by->email . '/'. $file->folder->name .'/' . $file->file_name )}}" target="_blank">{{ $file->file_name }} ({{ $file->size }} KB)</a>
                                    </p>
                                </td>
                        </tr>
                    </table>
                </div>
            </div>

            <p>&nbsp;</p>

            <a href="{{ route('admin.files.index') }}" class="btn btn-default">@lang('quickadmin.qa_back_to_list')</a>
        </div>
    </div>
@stop
