
@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('content')
<h3>Patients</h3>
    <div id="patient_slide_1">
        <div class="card">
            <div class="card-body">
                <div class="col-md-6">
                    <input type="text" value="" placeholder="Patient's First Name" name="first_name" class="searcher form-control">
                </div>
                <div class="col-md-6">
                    <input type="text" value="" placeholder="Patient's Last Name" name="last_name" class="searcher form-control">
                </div>
            </div>
        </div>
    </div><br>
    <div id="patient_slide_2" class="hide">
    <br>
        <div class="col-lg-12">
                <div class="panel panel-default">

                    <div class="panel-heading">Patients</div>


    <div class="panel-body table-responsive" style='    max-height: 400px;    overflow: auto;'>
        
        <table class="table table-bordered table-striped" id="listof_patients">
            <thead>
            <tr>
                <th>Doctor's Name</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>File Names</th>
                <th>Date Reported</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>

            @if (count($patients) > 0)
                @foreach ($patients as $file)
                @endforeach
            @endif

            </tbody>
        </table>
        </div>
    </div></div>
    <!--<div class="col-lg-6">
        <div class="panel panel-default">

                    <div class="panel-heading">Full Details</div>


    <div class="panel-body table-responsive">
        <div id="full_details_pane" class="hide">
            <h3>{Patient Name}</h3>
            <div id="full_patient_desc" style='    max-height: 500px;    overflow: auto;'>
                <button class="btn btn-xs btn-success">View HTML File</button> <button class="btn btn-xs btn-primary">View PDF File</button><br><br>
                <table class="table table-bordered table-striped" id="patient_details">
                    <thead>
                        <tr>
                            <th width="25%">Detail</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < 15; $i++)
                            <tr><th>Detail {{$i+1}}</th><td>infoinfoinfo</td></tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div></div> -->

</div>

@endsection
@section('javascript')
    @parent
    <script type="text/javascript">

        $(".searcher").on("keyup",function(){
            firstName = $("[name=first_name]").val();
            lastName = $("[name=last_name]").val();

            $.post(siteUrl+"admin/list_patients",{_token:window._token,first_name: firstName, last_name: lastName}).done(function(results){
                console.log(results);
                //clear table results
                $("#patient_slide_2").removeClass("hide");
                $("#listof_patients tbody").empty();

                for(index in results.data){

                    dt = results.data[index];

                    

                    fileList = "<em>No Files in Patient.</em>";
                    firstData = '';

                    if(dt.files.length > 0){
                        fileList = "";
                        for(fileI in dt.files){
                            file = dt.files[fileI];
                            file.url = siteUrl+"storage/"+ file.folder_creator +"/"+ file.folder_name +"/"+ file.relative_path +"/"+ file.file_name ;
                            if(typeof firstData === 'string'){
                                firstData = file;

                            }
                            fileList += "<a href='"+file.url+"' target='_blank'>"+ file.file_name +"</a><br>";
                        }

                    }else{
                        //
                    }

                    downloadLinks = (dt.files.length > 0) ? "<a href='"+siteUrl+"storage/"+ firstData.folder_creator +"/"+ firstData.folder_name +"/"+ firstData.relative_path +"/"+ firstData.file_name +"' class=\"btn btn-xs btn-success view-full-details\" target='_blank'>View File</a><a href='"+siteUrl+"admin/"+ firstData.uuid +"/download' class=\"btn btn-xs btn-success\">Download File</a>" : '<em>No Files Available.</em>';

                    editOption = "<a href='/admin/patients/create?id="+dt.patient_id+"'></a>";
                    

                    layout = "\
                    <tr>\
                    <td>"+ dt.doctor_name +"</td>\
                    <td><a href='/admin/patients/create?id="+dt.patient_id+"'>"+ dt.first_name +"</a></td>\
                    <td><a href='/admin/patients/create?id="+dt.patient_id+"'>"+ dt.last_name +"</a></td>\
                    <td class='file_list'>"+ fileList +"</td>\
                    <td>"+ dt.report_date +"</td>\
                    <td class='optionz'>\
                    "+downloadLinks+" "+editOption+"</td>\
                    \
                    </tr>\
                    ";

                    $("#listof_patients tbody").append(layout);

                }


                

            });
        });

        $("body").on("click",".view-full-details",function(event){
            if(!$("body").hasClass("sidebar-collapse")){
                $(".sidebar-toggle").trigger("click");
                $("#full_details_pane").removeClass("hide");
            }
            
        });
    </script>
    <style>
        .optionz .btn{margin-right:5px;}
    </style>
@stop