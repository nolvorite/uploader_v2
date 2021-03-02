@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('content')
	 <h3 class="page-title">List of Assigned Files</h3>
	 <div class="panel panel-default">
        <div class="panel-body table-responsive">
        	<table class="table table-bordered table-striped">
        		<thead>
        			<th>Assigned By</th>
        			<th>Assigned To</th>
        			<th>Files
        			</th>
        			<th>Due Date</th>
        			<th>Remark</th>
        			<th>Actions</th>
        		</thead>
        		<tbody>
        		<tr>
        			<td>ror2@ror2.com</td>
        			<td>ror1@ror1.com</td>
        			<td><ul>
        				<li><a href=''>file.jpg</a></li>
        				<li><a href=''>file2.jpg</a></li>
        				<li><a href=''>file3.jpg</a></li>
        				<li><a href=''>file4.jpg</a></li>
        			</ul></td>
        			<td>March 8, 2021</td>
        			<td>
        				<em></em>
        				<button class='btn btn-primary btn-xs'>Add File</button>
        			</td>
        			<td><button class='btn btn-warning btn-xs'>Delete Assignment</button></td>
        		</tr>
        		<tr>
        			<td>ror2@ror2.com</td>
        			<td>ror1@ror1.com</td>
        			<td><ul>
        				<li><a href=''>file.jpg</a></li>
        				<li><a href=''>file2.jpg</a></li>
        				<li><a href=''>file3.jpg</a></li>
        				<li><a href=''>file4.jpg</a></li>
        			</ul></td>
        			<td>March 8, 2021</td>
        			<td>
        				<em></em>
        				<button class='btn btn-primary btn-xs'>Add File</button>
        			</td>
        			<td><button class='btn btn-warning btn-xs'>Delete Assignment</button></td>
        		</tr>
        		<tr>
        			<td>ror2@ror2.com</td>
        			<td>ror1@ror1.com</td>
        			<td><ul>
        				<li><a href=''>file.jpg</a></li>
        				<li><a href=''>file2.jpg</a></li>
        				<li><a href=''>file3.jpg</a></li>
        				<li><a href=''>file4.jpg</a></li>
        			</ul></td>
        			<td>March 8, 2021</td>
        			<td>
        				<em></em>
        				<button class='btn btn-primary btn-xs'>Add File</button>
        			</td>
        			<td><button class='btn btn-warning btn-xs'>Delete Assignment</button></td>
        		</tr>
        		
        		<tr>
        			<td>ror2@ror2.com</td>
        			<td>ror1@ror1.com</td>
        			<td><ul>
        				<li><a href=''>file.jpg</a></li>
        				<li><a href=''>file2.jpg</a></li>
        				<li><a href=''>file3.jpg</a></li>
        				<li><a href=''>file4.jpg</a></li>
        			</ul></td>
        			<td>March 8, 2021</td>
        			<td>
        				<em></em>
        				<button class='btn btn-primary btn-xs'>Add File</button>
        			</td>
        			<td><button class='btn btn-warning btn-xs'>Delete Assignment</button></td>
        		</tr>
        		<tr>
        			<td>ror2@ror2.com</td>
        			<td>ror1@ror1.com</td>
        			<td><ul>
        				<li><a href=''>file.jpg</a></li>
        				<li><a href=''>file2.jpg</a></li>
        				<li><a href=''>file3.jpg</a></li>
        				<li><a href=''>file4.jpg</a></li>
        			</ul></td>
        			<td>March 8, 2021</td>
        			<td>
        				<em></em>
        				<button class='btn btn-primary btn-xs'>Add File</button>
        			</td>
        			<td><button class='btn btn-warning btn-xs'>Delete Assignment</button></td>
        		</tr>
        		</tbody>
        	</table>
        </div>
    </div>
@stop