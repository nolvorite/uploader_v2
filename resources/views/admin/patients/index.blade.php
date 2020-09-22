
@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('content')
<h3>Patients</h3>
    <div id="patient_slide_1">
        <div class="card">
            <div class="card-body">
                <input type="text" value="" placeholder="Search for Patient..." id="searcher" class="form-control">
            </div>
        </div>
    </div>
    <div id="patient_slide_2" class="hide">
    <br>
    	<div class="col-lg-12">
    		    <div class="panel panel-default">

    		    	<div class="panel-heading">Patients</div>


    <div class="panel-body table-responsive" style='    max-height: 500px;    overflow: auto;'>
    	
        <table class="table table-bordered table-striped" id="listof_patients">
            <thead>
            <tr>
                <th>Doctor's Name</th>
                <th>Patient's Name</th>
                <th>File Name</th>
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
        $("#searcher").on("keyup",function(){
            value = $(this).val();
            $.post(siteUrl+"admin/list_patients",{_token:window._token,"query":value}).done(function(results){
                console.log(results);
                //clear table results
                $("#patient_slide_2").removeClass("hide");
                $("#listof_patients tbody").empty();

                for(index in results.data){
                    dt = results.data[index];

                }

                for(i = 0; i < 30; i++){
                    layout = "\
                    <tr>\
                    <td>Doctor Name</td>\
                    <td>Patient Name</td>\
                    <td>File Name.html</td>\
                    <td>September 15, 2020</td>\
                    <td><button class=\"btn btn-xs btn-success view-full-details\">View File</button> <button class=\"btn btn-xs btn-success view-full-details\">Download Files</button></td>\
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
@stop