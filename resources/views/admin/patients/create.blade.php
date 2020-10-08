@extends('layouts.app')



@section('content')
    <h3 class="page-title">New Patient Entry</h3>
    {!! Form::open(['method' => 'POST', 'route' => ['admin.files.store'], 'files' => true, 'id' => 'submitt']) !!}

    <div class="panel panel-default">
        <div class="panel-heading">
            @lang('quickadmin.qa_create')
        </div>

        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12 form-group">
                <label class="control-label">Uploading Guide</label>
                <p>Aside, from the patient details, you will also need to upload his medical history. First select a folder, then pick the files that you want to upload.</p>
                <p>Afterwards, you can add the patient data.</p>
                </div>
                
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

            <div class="row"><br>
                <div class="col-xs-6 form-group">
                    {!! Form::label('filename', 'PDF File', ['class' => 'control-label']) !!}
                    {!! Form::file('filename[]', [
                        'name' => 'pdf_file',
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
                    @if($errors->has('filename'))
                        <p class="help-block">
                            {{ $errors->first('filename') }}
                        </p>
                    @endif
                </div>
                <div class="col-xs-6 form-group">
                    {!! Form::label('filename', 'HTML File', ['class' => 'control-label']) !!}
                    {!! Form::file('filename', [
                        'name' => 'html_file',
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
                    @if($errors->has('filename'))
                        <p class="help-block">
                            {{ $errors->first('filename') }}
                        </p>
                    @endif
                </div>
                <div class="col-xs-12 form-group">
                    {!! Form::label('filename', 'Other Files', ['class' => 'control-label']) !!}
                    {!! Form::file('filename[]', [
                        'multiple',
                        'name' => 'other_files[]',
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
                    @if($errors->has('filename'))
                        <p class="help-block">
                            {{ $errors->first('filename') }}
                        </p>
                    @endif
                    <br><input type="submit" value="Finish Adding Files" class="btn btn-success" id="compile_all_files">
                    <input type="hidden" id="files_compiled" name="file_and_media_ids" value="">
                </div>
            </div>


            <div id="patient_info">

            <h3>Patient Info</h3>

            <div class="row">
                <div class="col-xs-6">
                    <div class="card-body">
                        <label>Patient's First Name</label>
                        <input type="text" value="" placeholder="First Name" name="first_name" class="form-control"><br>
                        <label>Patient's Last Name</label>
                        <input type="text" value="" placeholder="Last Name" name="last_name" class="form-control">
                    </div>
                </div><div class="col-xs-6"><div class="card-body">
                        <label>Report Date</label>
                        <input type="text" value="" id="report_date" name="report_date" placeholder="Report Date" class="form-control">

                        
                    </div></div>
                    <div class="col-xs-6"><div class="card-body">
                        <label>Doctor Name</label>
                        <input type="text" value="" name="doctor_name" placeholder="Report Date" class="form-control">

                        
                    </div></div>
                </div>
                <br><br><button class="btn btn-primary" id="new_patient_entry">Add New Patient Entry!</button>
            </div>

        </div>
    </div>

    <!-- <a href="{{ url('admin/files') }}" class="btn btn-success">Finish Uploading</a> -->

    {!! Form::close() !!}
@stop

@section('javascript')
    @parent
    <script src="{{ asset('js/jquery-ui.js') }}"></script>
    <script src="{{ asset('quickadmin/plugins/fileUpload/js/jquery.iframe-transport.js') }}"></script>
    <script src="{{ asset('quickadmin/plugins/fileUpload/js/jquery.fileupload.js') }}"></script>
    <script>
        $(document).ready(function () {

            //fileIds = []; 
            fileData = [];

            var exfiles = '<?php echo $userFilesCount; ?>';
            var existingFiles = Number(exfiles);

            $('.file-upload').attr('disabled',true);

            $("#report_date").datepicker();
            

            if(typeof loadedFolderId !== "undefined"){
                
                $("#folder_id").select2().val(loadedFolderId).trigger("change.select2");

            }

            $("#folder_id").select2().on("select2:select",function(e){
                data= e.params.data;
                console.log(data,e);
                $("#subfolder_view").removeClass("hide");

                $('.file-upload').removeAttr('disabled');

                clickToPath(data.text);
            });

            $("body").on("click","#new_patient_entry",function(event){

                event.preventDefault();

                firstName = $("[name=first_name]").val();
                lastName = $("[name=last_name]").val();
                reportDate = $("[name=report_date]").val();
                doctorName = $("[name=doctor_name]").val();

                $.post(
                    siteUrl+"admin/new_patient",
                     {first_name: firstName, last_name: lastName, report_date: reportDate, doctor_name: doctorName, files: fileData, _token: window._token}
                ).done(function(data){
                    alert("New Patient has been added!");
                    location.assign(siteUrl+"admin/patients");
                });

                return false;
            });

            $("body").on("submit","form#submitt",function(){

                if($("#folder_id").val() !== null && $("#folder_id").val() !== ""){

                    formData = new FormData($(this)[0]);

                    formData.append("is_new_patient","true");

                    formData.append("model_name","File");
                    formData.append("path",fullPath);

                    $("#compile_all_files").val("Adding files... please wait.");


                    $.ajax({
                        url: siteUrl+"admin/spatie/media/upload",
                        type: 'POST', 
                        data: formData, 
                        cache: false, 
                        contentType: false, 
                        processData: false, 
                        success: function(data){
                            
                            alert("All files have finished uploading!");

                            $("#compile_all_files").val("Finish Adding Files");

                            fileData = data.files;
                            // $.each(data.result.files, function (index, file) {
                            //     var $line = $($('<p/>', {class: "form-group"}).html(file.name + ' (' + file.size + ' bytes)').appendTo($parent.find('.files-list')));
                            //     if ($parent.find('.' + $this.data('bucket') + '-ids').val() != '') {
                            //         $parent.find('.' + $this.data('bucket') + '-ids').val($parent.find('.' + $this.data('bucket') + '-ids').val() + ',');
                            //     }
                            //     $parent.find('.' + $this.data('bucket') + '-ids').val($parent.find('.' + $this.data('bucket') + '-ids').val() + file.id);
                            // });
                            // $parent.find('.progress-bar').hide().css(
                            //     'width',
                            //     '0%'
                            // );
                            getListOfFiles(fullPath);

                        }

                    });

                }else{
                    alert("Please select a folder first.");
                }            

                return false;

            });



            $('.file-upload').change(function () {
                var uploadingFiles = $(this)[0].files;
                var totalCount = uploadingFiles.length + existingFiles;

                var Id = '<?php echo $roleId; ?>';
                var roleId = Number(Id);

                counter = 0;

                if($("#folder_id").val() !== "" && $("#folder_id").val() !== null){

                    //$(this).fileupload('enable');

                    // $(this).fileupload('option','formData').folder_id = $("#folder_id").val();
                    // $(this).fileupload('option','formData').path = fullPath;

                    //fileIds = $(this).fileupload('option','fileIds').file_ids
                    // $(this).fileupload('option','fileIds').folderId

                    //$(this).fileupload();

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

            // $('.file-upload').each(function () {
            //     var $this = $(this);
            //     var $parent = $(this).parent();

                

            //     $(this).fileupload({
            //         dataType: 'json',
            //         url: $this.data('url'),
            //         formData: (function(){
            //             console.log($("#folder_id").val());
            //             return {
            //                 model_name: 'File',
            //                 bucket: $this.data('bucket'),
            //                 file_key: $this.data('filekey'),
            //                 _token: '{{ csrf_token() }}',
            //                 folder_id: $("#folder_id").val(),
            //                 path: fullPath
            //             }
            //         })(),

            //         add: function (e, data) {
            //             data.submit();
            //         },
            //         fail: function(e, data){
            //             console.log(data);
            //             alert("Error uploading file. Please try again later.");
            //         },
            //         done: function (e, data) {
            //             counter++;
            //             if(counter === data.result.files.length){
            //                 alert("All files have finished uploading!");
            //             }
            //             $.each(data.result.files, function (index, file) {
            //                 var $line = $($('<p/>', {class: "form-group"}).html(file.name + ' (' + file.size + ' bytes)').appendTo($parent.find('.files-list')));
            //                 if ($parent.find('.' + $this.data('bucket') + '-ids').val() != '') {
            //                     $parent.find('.' + $this.data('bucket') + '-ids').val($parent.find('.' + $this.data('bucket') + '-ids').val() + ',');
            //                 }
            //                 $parent.find('.' + $this.data('bucket') + '-ids').val($parent.find('.' + $this.data('bucket') + '-ids').val() + file.id);
            //             });
            //             $parent.find('.progress-bar').hide().css(
            //                 'width',
            //                 '0%'
            //             );
            //             getListOfFiles(fullPath);
            //         }

            //     }).on('fileuploadprogressall', function (e, data) {
            //         var progress = parseInt(data.loaded / data.total * 100, 10);
            //         $parent.find('.progress-bar').show().css(
            //                 'width',
            //                 progress + '%'
            //         );
            //     });

            //     $(this).fileupload('disable');

            // });


            // $(document).on('click', '.remove-file', function () {
            //     var $parent = $(this).parent();
            //     $parent.remove();
            //     return false;
            // });
        });
    </script>
@stop