@extends('layouts.app')



@section('content')

    <h3 class="page-title"><?php echo (count($patientJSONData) > 0) ? "Modifying Patient Entry": "New Patient Entry" ?></h3>
    {!! Form::open(['method' => 'POST', 'route' => ['admin.files.store'], 'files' => true, 'id' => 'submitt']) !!}

    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo (count($patientJSONData) > 0) ? "Update": "Create" ?>
        </div>

        <div class="panel-body">
            <div class="col-lg-6" id="uploader_pane">
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

                <div class="row hide" id="files_view"><br>
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
                        <div>
                        {!! Form::file('filename[]', [
                            'multiple',
                            'name' => 'other_files[]',
                            'class' => 'form-control file-upload',
                            'data-url' => route('admin.media.upload'),
                            'data-bucket' => 'filename',
                            'data-filekey' => 'filename',
                            'id' => 'my_id',
                            ]) !!}
                        </div>
                        <p class="help-block"></p>
                        <div class="photo-block">
                            <div class="progress-bar form-group">&nbsp;</div>
                            <div class="files-list"></div>
                        </div>
                        <div id="drag_drop_box"></div>
                        @if($errors->has('filename'))
                            <p class="help-block">
                                {{ $errors->first('filename') }}
                            </p>
                        @endif
                        <br><input type="submit" value="Finish Adding Files" class="btn btn-success" id="compile_all_files">
                        <input type="hidden" id="files_compiled" name="file_and_media_ids" value="">
                    </div>
                </div>

            </div>

            <div class="col-lg-6">
                <div class="col-xs-12 form-group">
                    <label class="control-label">Email Content</label>
                    <p>An email will also be sent to the user whom you are assigning the file to. The email will be the name of the root folder where it's located. You can also modify the recipients of the email message, and the message itself, below.</p>
                    <div class="card-body">
                        <label>Recipients (separate by comma)</label>
                        <input type="text" name="recipients" placeholder="(To: )" class="form-control"><br>
                        <label>Email Message</label>
                        <textarea name="email_message" style="min-height:200px;" placeholder="Email Message Here." class="form-control">@if(count($patientJSONData) === 0)
Hello!

You have been added as a patient in {{ env('APP_NAME') }}. Your name will be kept in our file. Thank you for using {{ env('APP_NAME') }}'s services.

Kind regards,
{{ env('APP_NAME') }}
@else
We are sending this email to inform you that we have modified your patient file under {{ $patientJSONData['first_name'] }} {{ $patientJSONData['last_name'] }}. Feel free to reply to this email if you have any questions.

Thank you for understanding, 
{{ env('APP_NAME') }}
@endif
</textarea>
                    </div>
                </div>
            </div>

            <div class="col-sm-12<?php if(isset($_REQUEST['id'])): ?> col-lg-8<?php endif; ?>" id="patient_info">

                <h3>Patient Info</h3>

                <div class="row">
                    <div class="col-xs-6">
                        <div class="card-body">
                            <label>Patient's First Name</label>
                            <input type="text" value="" placeholder="First Name" name="first_name" class="form-control"><br>
                            <label>Patient's Last Name</label>
                            <input type="text" value="" placeholder="Last Name" name="last_name" class="form-control">

                            <br>
                            <label>Patient's Email Address</label>
                            <input type="text" value="" placeholder="Email Address" name="email" class="form-control">


                        </div>
                    </div><div class="col-xs-6"><div class="card-body">
                            <label>Report Date</label>
                            <input type="text" value="" id="report_date" name="report_date" placeholder="Report Date" class="form-control"><br>

                            
                        </div></div>
                        @if(auth()->user()->role_id === 1)
                        <div class="col-xs-6"><div class="card-body">
                            <label>Doctor Email</label>
                            <input type="text" name="doctor_name" id="doctor_email" placeholder="Search for ANY user information here..." class="form-control">

                            
                        </div></div>
                        @endif
                </div>
                <br><br><button class="btn btn-primary" id="new_patient_entry"><?php echo (count($patientJSONData) > 0) ? "Modify Patient Information!": "Add New Patient Entry!" ?></button>
            </div>
            <?php if(isset($_REQUEST['id'])): ?>
            <div class="col-lg-4" id="current_list_of_files">

                <h3>Patient's Files</h3>
                <ul id="patient_files"></ul>
            </div>
            <?php endif; ?>
            
            

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

            <?php if(count($patientJSONData) > 0){ ?>

            patientData = {!! json_encode($patientJSONData) !!};
            isEditingPatientEntry = true;

            <?php } else { ?>

            isEditingPatientEntry = false;

            <?php }?>

            //fileIds = []; 
            fileData = [];
            dataFromDrops = [];

            var exfiles = '<?php echo $userFilesCount; ?>';
            var existingFiles = Number(exfiles);

            adminDaemonToSendTo = "<?php echo env('MAIL_USERNAME'); ?>";

            $("[name=recipients]").val(adminDaemonToSendTo +", ");


            currentDate = (isEditingPatientEntry) ? new Date(patientData.report_date).toLocaleDateString() : new Date().toLocaleDateString();

            if(isEditingPatientEntry){

                $("[name=report_date]").attr('readonly',true).val(currentDate);
                $("[name=first_name]").val(patientData.first_name);
                $("[name=last_name]").val(patientData.last_name);
                $("[name=email]").val(patientData.email);
                setTimeout(function(){
                    $("[name=email]").trigger('change');
                },1000);
                $("[name=doctor_name]").val(patientData.doctor_name);

                for(index in patientData.files){
                    uuid = patientData.files[index].uuid;

                    $("#patient_files").append("<li><i class=\"fa fa-file-excel-o\" aria-hidden=\"true\"></i>&nbsp;<a target='_blank' href='"+siteUrl+"admin/"+encodeURI(uuid)+"/download'>"+name+"</a></li>");
                }

            }else{
                $('.file-upload').attr('disabled',true);
                $("#report_date").datepicker({currentText: currentDate}).val();
            }      
            

            if(typeof loadedFolderId !== "undefined"){
                
                $("#folder_id").select2().val(loadedFolderId).trigger("change.select2");

            }

            $("body").on('change',"#email_list",function(event){
                val = $(this).val();
                $("#doctor_email").val(val);
                $(this).detach();
            });

            $("#doctor_email").on("keyup",function(event){
                val = $(this).val();

                $.post(siteUrl+"admin/search_users",{query: val,_token:window._token},function(results){
                    $("#email_list").detach();
                    $("#doctor_email").after("<select id='email_list' class='form-control' /> ");
                    for(i in results.data){
                        $("#email_list").append("<option value='"+results.data[i].email+"'>"+results.data[i].first_name+" "+results.data[i].last_name+" ("+results.data[i].email+")</option>");
                    }
                    $("#email_list").prepend("<option selected>Select email address here...</option>");
                },"json");

            });

            $("[name=email]").on("change",function(event){
                emailToAddInRecipients = $(this).val();
                if(/^[ \t]{0,}(.+)@(.+){2,}\.(.+){2,}[ \t]{0,}$/.test(emailToAddInRecipients)){

                    currentVal = $("[name=recipients]").val();

                    if(currentVal.indexOf(emailToAddInRecipients) < 0){
                        if(/\,[ \t]$/.test(currentVal)){
                            $("[name=recipients]").val(currentVal + " "+emailToAddInRecipients);
                        }else{
                            $("[name=recipients]").val(currentVal + ", "+emailToAddInRecipients);
                        }
                    }
                }
            });

            $("#folder_id").select2().on("select2:select",function(e){
                data= e.params.data;
                console.log(data,e);
                $("#subfolder_view,#files_view").removeClass("hide");

                emailToAddInRecipients = /^([^\/]+)/.exec(data.text)[1];

                if(/(.+)@(.+){2,}\.(.+){2,}/.test(emailToAddInRecipients)){ 

                    $("#doctor_email").val(emailToAddInRecipients);

                    if($("[name=recipients]").val().length === 0){
                       $("[name=recipients]").val(emailToAddInRecipients); 
                    }else{
                        currentVal = $("[name=recipients]").val();
                        //check if email is found already

                        if(currentVal.indexOf(emailToAddInRecipients) < 0){
                            if(/\,[ \t]$/.test(currentVal)){
                                $("[name=recipients]").val(currentVal + " "+emailToAddInRecipients);
                            }else{
                                $("[name=recipients]").val(currentVal + ", "+emailToAddInRecipients);
                            }
                        }

                        
                    }
                }

                $('.file-upload').removeAttr('disabled');

                clickToPath(data.text);
            });

            $("body").on("click","#new_patient_entry",function(event){

                event.preventDefault();

                firstName = $("[name=first_name]").val();
                lastName = $("[name=last_name]").val();
                reportDate = $("[name=report_date]").val();
                doctorName = $("[name=doctor_name]").val();
                recipients = $("[name=recipients]").val();
                emailMessage = $("[name=email_message]").val();
                emailAddress = $("[name=email]").val();
                patientId = isEditingPatientEntry ? patientData.patient_id : -1;

                action = !isEditingPatientEntry ? "add" : "edit";

                for(i in dataFromDrops){
                    fileData.push(dataFromDrops[i]);
                }

                $.post(
                    siteUrl+"admin/new_patient",
                     {first_name: firstName, last_name: lastName, report_date: reportDate, doctor_name: doctorName, files: fileData, email: emailAddress, recipients: recipients, action: action, patient_id: patientId, email_message: emailMessage,_token: window._token}
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


                }else{
                    alert("Please select a folder first.");
                }
                
            });

       
        });
    </script>
@stop