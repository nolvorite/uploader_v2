@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('content')
	 <h3 class="page-title">List of Assigned Files 

     @if(!$request->has('show_completed'))
     (<a href='/admin/list_of_files_ror?show_completed=true'>Show Completed</a>)
     @else
     (<a href='/admin/list_of_files_ror'>Hide Completed</a>)
     @endif
     </h3>
	 <div class="panel panel-default">
        <div class="panel-body table-responsive">
        	<table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Assigned By</th>
                        <th>Assigned To</th>
                        <th>Files
                        </th>
                        <th>Due Date</th>
                        <th>Remark File/Remarks</th>
                        <th>Actions</th>
                    </tr></thead>
                <tbody>
                <?php foreach($rorList as $ror): ?>
                
                <tr assignment_id="{{ $ror->id }}">
                    <td>{{ $ror->email }}</td>
                    <td>{{ $ror->assigned_to }}</td>
                    <td><div class='file_info'><a href="{{url('/storage/' . $ror->folder_creator . '/'. $ror->folder_name .'/' . $ror->relative_path .'/'. $ror->file_name )}}" target="_blank">{{ $ror->file_name }}</a></div></td>
                    <td>{{ $ror->deadline }}</td>
                    <td>
                        @if($ror->file_name2 !== null)
                        <a href="{{url('/storage/' . $ror->folder_creator2 . '/'. $ror->folder_name2 .'/' . $ror->relative_path2 .'/'. $ror->file_name2 )}}" target="_blank">{{ $ror->file_name2 }}</a>
                        @else
                        <em>No remark files yet.</em>
                        @endif

                        @can('ror_supervision')

                        <button class='edit-remark btn btn-xs btn-info' assignment_id="{{ $ror->fa_id }}">Add/Edit Remarks</button>

                        @endcan
                        <br><b>Remarks:</b>
                        <div class='remarks' assignment_id='{{ $ror->fa_id }}'>
                        
                        @if(!($ror->remarks === null || $ror->remarks === ""))
                            {!! preg_replace("#\n#","<br>",htmlspecialchars($ror->remarks)) !!}
                            @else
                            <em>none yet</em>
                        @endif                            
                        </div>
                    </td>
                    <td>
      
                    <div class="dropdown" assignment_id="{{ $ror->fa_id }}">
                        <?php
                            switch($ror->status){
                                case "pending":
                                    $className = "btn-warning";
                                    $text = "Mark As Complete";
                                break;
                                case "approval_check":
                                    $className = "btn-primary";
                                    $text = "Mark As Complete";
                                break;
                                default: //this case doesn't really exist, but just in case
                                    $className = "btn-secondary";
                                break;
                            }
                        ?>
                      <button class="btn btn-primary dropdown-toggle btn-xs add-file-as-remark" assignment_id="{{ $ror->fa_id }}" id="dropdownMenu{{ $ror->fa_id }}" data-toggle="dropdown" aria-haspopup="true">
                        Add File As Remark <span class="caret"></span>
                      </button>

                      <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu{{ $ror->fa_id }}">
                        <li>Loading..</li>
                      </ul>
                    </div>

                    @if(auth()->user()->role_id === 5)
                    
                        @if($ror->status === "approval_check")
                            <button class="btn btn-warning btn-xs" disabled assignment_id="{{ $ror->fa_id }}">Currently Pending Approval of Completion</button>

                        @else

                        <button class="btn btn-success btn-xs mark-as-complete" assignment_id="{{ $ror->fa_id }}">Mark Assignment as Complete</button>

                        @endif

                    @else

                    <button class="btn btn-success btn-xs mark-as-complete" assignment_id="{{ $ror->fa_id }}">Mark Assignment as Complete</button>

                    @endif

                    @if($ror->status === "approval_check")

                    @can('ror_supervision')
                    <button class="btn btn-warning btn-xs reset-to-pending" assignment_id="{{ $ror->fa_id }}">Reset To Pending</button>

                    @endcan

                    @endif

      



                    </td>
                </tr>
                
                <?php endforeach; ?>
                
                
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('javascript')

    <script type="text/javascript">
        $(document).ready(function(){

            $("body").on("click",".edit-remark",function(event){
                assignmentId = $(this).attr("assignment_id");
                actualElem = $(this);
                elemToModify = $(".remarks[assignment_id='"+assignmentId+"']");
                $.post(siteUrl+"admin/edit_remark",{assignment_id: assignmentId, _token: window._token},function(results){
                    console.log(results.contents);
                    elemToModify.html("<textarea class='remark-editor form-control' assignment_id='"+ assignmentId+"'>"+results.contents.escape()+"</textarea>");
                    actualElem.text("Finish Editing Remark").removeClass("edit-remark btn-info").addClass("finish-editing-remark btn-warning");
                },"json");
            });

            $("body").on("click",".finish-editing-remark",function(event){
                assignmentId = $(this).attr("assignment_id");
                contents = $(".remark-editor[assignment_id='"+assignmentId+"']").val();
                actualElem = $(this);
                elemToModify = $(".remarks[assignment_id='"+assignmentId+"']");
                $.post(siteUrl+"admin/finish_editing_remark",{assignment_id: assignmentId, contents: contents, _token: window._token},function(results){
                    actualElem.text("Add/Edit Remarks").addClass("edit-remark btn-info").removeClass("finish-editing-remark btn-info");
                    contents = contents.escape();
                    elemToModify.html(contents.replace(/\n/gi,"<br>"));

                },"json");
            });

            $("body").on("click",".mark-as-complete,.reset-to-pending",function(event){
                
                event.preventDefault();
                assignmentId = parseInt($(this).attr("assignment_id"));

                url = ($(this).is(".reset-to-pending")) ? "reset_as_pending" : "mark_as_complete";

                $.post(siteUrl+"admin/"+url,{_token: '{{ csrf_token() }}',assignment_id: assignmentId},function(results){
                    if(results.status){
                        alert(results.message);
                    }else{
                        if(url === "reset_as_pending"){
                            alert("Failed to mark as complete.");
                        }else{
                            alert("Failed to set to pending.");
                        }
                        
                    }
                },"json");

            });

            $("body").on("click",".assign-as-remark",function(event){
                event.preventDefault();
                fileId = $(this).attr("file_id");
                $.post(siteUrl+"admin/assign_as_remark",{_token: '{{ csrf_token() }}', assignment_id: assignmentId, file_id: fileId},function(results){

                    if(results.status){
                        alert('Successfully set remark file.');
                        location.reload();
                    }

                });

            });

            $("body").on("click",".add-file-as-remark",function(){
                assignmentId = parseInt($(this).attr("assignment_id"));
                $.post(siteUrl+"admin/list_of_eligible_files_for_remark",{_token: '{{ csrf_token() }}', assignment_id: assignmentId},function(results){

                    $(".dropdown[assignment_id='"+assignmentId+"'] ul").empty();
                    for(i in results.data){
                        dt = results.data[i];
                        $(".dropdown[assignment_id='"+assignmentId+"'] ul").append("\
                                <li><a href='' file_id='"+dt.id+"' class='assign-as-remark'>"+dt.file_name+"</a></li>\
                            ");
                    }
                    console.log(results);
                },"json");
            });

        });

    </script>

@stop