@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('content')                                
   <h3>Assign File</h3>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Employee</label>
            <select class="form-control select2" id="employee_assignment"><option>Select Employee Here...</option></select>
        </div>
<div class="form-group">
            <label>Assign Files</label>
            <select class="form-control select2" id="file_assignment"><option>Assign Files Here...</option></select>
        </div><div class="form-group">
            <label>Deadline</label>
            <input class="form-control" id="deadline">
        </div>

<div class="form-group">
    <button id="submit_assignment" class="btn-success btn">
        Submit Assignment
    </button>
</div>
    </div><div class="col-md-8">
        <div class="form-group">
            <label>Files Currently Assigned To: <span id="email_of_selected">ror2@ror2.ror2</span></label>
            <table class="table table-bordered table-striped" id="list_of_assignments">
        		<thead>
        			<tr>
        			<th>File Name
        			</th>
        			<th>Due Date</th>
        			<th>Remark</th>
        			<th>Actions</th>
        		</tr></thead>
        		<tbody>
        		<tr>
        			<td>file1.php</td>
        			<td>March 8, 2021</td>
                    <th>Remark</th>
        			<td><button class="btn btn-warning btn-xs">Delete Assignment</button></td>
        		</tr>

        		</tbody>
        	</table>
        </div>
    </div>

@stop

@section('javascript')
	<script type="text/javascript">

		currentDate = new Date().toLocaleDateString();

		$(document).ready(function(){
			$("#employee_assignment,#file_assignment").select2();
			$("#deadline").datepicker({currentText: currentDate}).val();
		});

	</script>
@stop