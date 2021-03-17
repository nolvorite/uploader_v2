@extends('layouts.app')



@section('content')
    <h3 class="page-title">File Manager</h3>
    {!! Form::open(['method' => 'POST', 'route' => ['admin.files.store'], 'files' => true,]) !!}

    <div class="panel panel-default">
        <!--<div class="panel-heading">
            @lang('quickadmin.qa_create')
        </div> -->

        <div class="panel-body">
            <div class="row">
                <!-- <div class="col-xs-12 form-group">
                <label class="control-label">Uploading Guide</label>
                <p>Hello. First select a folder, then pick the files that you want to upload. You will be notified when the files are done uploading.</p>
                <p>If you have no more files to upload, simply click "Go Back" to see your new files.</p>
                </div> -->
                <div class="col-xs-12 form-group">
                    {!! Form::label('folder_id', trans('quickadmin.files.fields.folder').'*', ['class' => 'control-label']) !!}
                    <select class="form-control select2" required="" id="folder_id" name="folder_id">
                        <option selected disabled>Please select...</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}">{{ $folder->email }}/{{ $folder->name }}</option>
                        @endforeach
                    </select>

                    <p class="help-block"></p>
                    @if($errors->has('folder_id'))
                        <p class="help-block">
                            {{ $errors->first('folder_id') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="row hide" id="subfolder_view">
                <div class="col-xs-12 form-group">
                    {!! Form::label('folder_id', 'Subfolder (Optional)', ['class' => 'control-label']) !!}
                    @include('partials/directorybrowser',['purpose' => 'create_files'])
                </div>
            </div>
            <div class="row hide" id="files_view">
                <div class="col-xs-12 form-group">
                    {!! Form::label('filename', 'Files', ['id' => 'upload-form','class' => 'control-label']) !!}
                    <p>Upload all your files here. Alternatively, you can also drag and drop files here to upload instead. You can even upload entire folders.</p>
                    <div>
                    {!! Form::file('filename[]', [
                        'multiple',
                        'class' => 'form-control file-upload',
                        'data-url' => route('admin.media.upload'),
                        'data-bucket' => 'filename',
                        'data-filekey' => 'filename',
                        'id' => 'my_id',
                        ]) !!}
                        <p class="help-block"></p>
                        <div class="photo-block">
                            <div class="progress-bar form-group">&nbsp;</div>
                            <div class="files-list"></div>
                        </div>
                    </div>
                    
                    <div id="drag_drop_box"></div>
                    @if($errors->has('filename'))
                        <p class="help-block">
                            {{ $errors->first('filename') }}
                        </p>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- <a href="{{ url('admin/files') }}" class="btn btn-success">Finish Uploading</a> -->

    {!! Form::close() !!}
@stop

@section('javascript')
    @parent

    <script src="{{ asset('quickadmin/plugins/fileUpload/js/jquery.iframe-transport.js') }}"></script>
    <script src="{{ asset('quickadmin/plugins/fileUpload/js/jquery.fileupload.js') }}"></script>
    <script>

        isFileManagerPage = true;

        $("#drag_drop_box").addClass("dropzone").dropzone({
            url: siteUrl+"admin/spatie/media/upload", 
            uploadMultiple: true,
            paramName: 'filename',
            params: function(){
                return {
                    _token: window._token,
                    path: fullPath,
                    bucket: 'filename',
                    file_key: 'filename',
                    model_name: 'File',
                    folder_id: $("#folder_id").val()
                }
            },
            success: displayWhenUploadFinishes2
        });

        @if($folderId !== null)
            var loadedFolderId = {{ $folderId }};
        @endif
        $(document).ready(function () {
            fileIds = []; 

            var exfiles = '<?php echo $userFilesCount; ?>';
            var existingFiles = Number(exfiles);

            

            if(typeof loadedFolderId !== "undefined"){
                
                $("#folder_id").select2().val(loadedFolderId).trigger("change.select2");

            }

            $("#folder_id").select2().on("select2:select",function(e){
                data= e.params.data;
                console.log(data,e);
                $("#subfolder_view,#files_view").removeClass("hide");

                clickToPath(data.text);
            });


            $('.file-upload').change(function () {
                var uploadingFiles = $(this)[0].files;
                var totalCount = uploadingFiles.length + existingFiles;

                var Id = '<?php echo $roleId; ?>';
                var roleId = Number(Id);
                console.log(roleId);
                console.log(totalCount);
                counter = 0;
                if($("#folder_id").val() !== ""){

                    $(this).fileupload('enable');

                    $(this).fileupload('option','formData').folder_id = $("#folder_id").val();
                    $(this).fileupload('option','formData').path = fullPath;
                    //fileIds = $(this).fileupload('option','fileIds').file_ids 
                    // $(this).fileupload('option','fileIds').folderId
                    $(this).fileupload();

                    // $('.file-upload').each(function () {
                    //     var $this = $(this);

                    //     $(this).fileupload({
                    //         dataType: 'json',
                    //         formData: (function(){ return {
                    //             model_name: 'File',
                    //             bucket: $this.data('bucket'),
                    //             file_key: $this.data('filekey'),
                    //             _token: '{{ csrf_token() }}',
                    //             folder_id: $("#folder_id").val()
                    //         }})(),

                    //         add: function (e, data) {
                    //             data.abort();
                    //         }
                    //     })
                    // });
                    

                }else{
                    alert("Please select a folder first.");
                }
                
            });

            $('.file-upload').each(function () {
                var $this = $(this);
                var $parent = $(this).parent();

                

                $(this).fileupload({
                    dataType: 'json',
                    url: $this.data('url'),
                    formData: (function(){
                        console.log($("#folder_id").val());
                        return {
                            model_name: 'File',
                            bucket: $this.data('bucket'),
                            file_key: $this.data('filekey'),
                            _token: '{{ csrf_token() }}',
                            folder_id: $("#folder_id").val(),
                            path: fullPath
                        }
                    })(),

                    add: function (e, data) {
                        data.submit();
                    },
                    fail: function(e, data){
                        console.log(data);
                        alert("Error uploading file. Please try again later.");
                    },
                    done: function (e, data) {
                        counter++;
                        if(counter === data.result.files.length){
                            alert("All files have finished uploading!");
                        }
                        $.each(data.result.files, function (index, file) {
                            var $line = $($('<p/>', {class: "form-group"}).html(file.name + ' (' + file.size + ' bytes)').appendTo($parent.find('.files-list')));
                            if ($parent.find('.' + $this.data('bucket') + '-ids').val() != '') {
                                $parent.find('.' + $this.data('bucket') + '-ids').val($parent.find('.' + $this.data('bucket') + '-ids').val() + ',');
                            }
                            $parent.find('.' + $this.data('bucket') + '-ids').val($parent.find('.' + $this.data('bucket') + '-ids').val() + file.id);
                        });
                        $parent.find('.progress-bar').hide().css(
                            'width',
                            '0%'
                        );
                        getListOfFiles(fullPath);
                    }

                }).on('fileuploadprogressall', function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $parent.find('.progress-bar').show().css(
                            'width',
                            progress + '%'
                    );
                });

                $(this).fileupload('disable');

            });
            $(document).on('click', '.remove-file', function () {
                var $parent = $(this).parent();
                $parent.remove();
                return false;
            });
        });
    </script>
@stop