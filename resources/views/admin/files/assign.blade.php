@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('content')                                
   <h3>Assign File</h3>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Employee</label>
            <select class="form-control select2" name="employee_assignment" id="employee_assignment"></select>
        </div>
<div class="form-group">
            <label>Assign Files</label>
            <select class="form-control select2" multiple disabled name="file_assignment[]" id="file_assignment"></select>
        </div><div class="form-group">
            <label>Deadline</label>
            <input class="form-control" id="deadline" disabled>
        </div>

<div class="form-group">
    <button id="submit_assignment" class="btn-success btn">
        Submit Assignment
    </button>
</div>
    </div><div class="col-md-8">
        <div class="form-group">
            <label>Files Currently Assigned To: <span id="email_of_selected">---</span></label>
            <table class="table table-bordered table-striped" id="list_of_assignments">
        		<thead>
        			<tr>
                    <th>Patient
                    </th>
        			<th>File Name
        			</th>
        			<th>Due Date</th>
        			<th>Remark</th>
        			<th>Actions</th>
        		</tr></thead>
        		<tbody>

        		</tbody>
        	</table>
        </div>
    </div>

@stop

@section('javascript')
	<script type="text/javascript">

		currentDate = new Date().toLocaleDateString();

        employeePhase = 1;
        filePhase = 1;

		$(document).ready(function(){
			$("#employee_assignment").select2();
			$("#deadline").datepicker({currentText: currentDate}).val();

            $("#employee_assignment").on("select2:select",function(event){
                $("#file_assignment,#deadline").removeAttr("disabled");
            });

            $("#employee_assignment").select2({
                ajax: {
                    url : siteUrl+"admin/list_of_employees",
                    async: false,
                    type: 'POST',
                    data: {_token:window._token},
                    processResults: function(results){
                        compiledData = [];
                        for(i in results.data){
                            dt = results.data[i];
                            text = dt.name +" ("+dt.email+" - Queue: "+dt.assigned_file_count+")";
                            id = dt.id;
                            compiledData[compiledData.length] = {text: text, id: id};
                        }
                        return {
                            results: compiledData
                        }    
                    }
                }
            });

            $("#employee_assignment").on("select2:select",function(event){
               userId = event.params.data.id;
               getListOfEmployeeAssignments(userId);
            })

            $("#file_assignment").select2({
                ajax: {
                    url : siteUrl+"admin/list_of_unassigned_files",
                    async: false,
                    type: 'POST',
                    data: {_token:window._token, exclude: 'true'},
                    processResults: function(results){
                        compiledData = [];
                        for(i in results.data){
                            dt = results.data[i];
                            text = dt.file_name +" ("+dt.pfn+" "+dt.pln+" - "+dt.assignees+" assigned)";
                            id = dt.id;
                            compiledData[compiledData.length] = {text: text, id: id};
                        }
                        return {
                            results: compiledData
                        }    
                    }
                }
            });

            $("#submit_assignment").on("click",function(){
                data = {
                    employee_id: $("#employee_assignment").val(),
                    file_ids: $("#file_assignment").val(),
                    deadline: $("#deadline").val(),
                    _token:window._token
                };

                if(data.employee_id === null || data.file_ids === null || data.deadline === null){
                    alert("There are still some missing information. Please fill them out.");
                }else{
                    $.post(siteUrl+"admin/submit_assignments",data,function(results){
                        if(results.status){
                            getListOfEmployeeAssignments(data.employee_id);
                        }else{
                            alert("Failure submitting assignment");
                        }
                    },"json");
                }

            });

            $("body").on("click",".delete-assignment",function(event){
                $.post(siteUrl+"admin/delete_assignment",{_token:window._token,fa_id: $(this).attr("fa_id")},function(result){
                    if(result.status){
                        $(".delete-assignment[fa_id='"+result.fa_id+"']").parents("tr").fadeOut("1000",function(){
                            $(this).detach();
                        });
                    }else{
                        alert("Error deleting assignment");
                    }
                });
            });

		});

        function getListOfEmployeeAssignments(userId){
            $.ajax({
                url : siteUrl+"admin/list_of_unassigned_files",
                type: 'POST',
                data: {_token:window._token,user_id: userId},
                success: function(results){
                    console.log('it went here');
                    $("#list_of_assignments tbody").empty();
                    for(i in results.data){
                        dt = results.data[i];
                        dt.url = siteUrl+"storage/"+ dt.folder_creator +"/"+ dt.folder_name +"/"+ dt.relative_path +"/"+ dt.file_name ;

                        template = 
                        "<tr>\
                             <td>"+dt.pfn+" "+dt.pln+"</td>\
                            <td><a href='"+dt.url+"' target='_blank'>"+ dt.file_name +"</a></td>\
                            <td>"+dt.deadline+"</td>\
                            <td>"+dt.remarks+"</td>\
                            <td><button class='btn btn-warning btn-xs delete-assignment' fa_id='"+dt.fa_id+"'>Delete Assignment</button></td>\
                        </tr>";
                        $("#list_of_assignments tbody").append(template);
                    }
                }
            });
        }

	</script>
@stop