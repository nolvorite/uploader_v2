<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Support\Facades\DB as DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $rootDirectory = "";
    public $level = 1;
    public $currentArray = [];
    public $currentDirectory = "";
    public $folderId = 0;

    public function fileTable($extraSelects = ''){
    	return DB::table("files as f")->select(DB::raw('(SELECT email FROM users INNER JOIN folders WHERE users.id = folders.created_by_id AND folders.id = f.folder_id LIMIT 0,1) as folder_creator,f.id,f.uuid,f.created_at,f.folder_id,f.created_by_id as file_creator,f.path,f.relative_path,media.id,media.model_type,media.name,media.file_name,folders.name as folder_name,media.mime_type,media.custom_properties,media.order_column,media.created_at,media.size,users.email'.$extraSelects))->join("folders","folders.id","=","f.folder_id")->join("users","users.id","=","f.created_by_id")->join("media","f.id","=","media.id");
    }

    public function returnThenAdd(){
    	$num = $this->folderId;
    	$this->folderId++;
    	return $num;
    }

    public function rorAssignments($userId = null, $showCompleted = false){

        $fileTableSample =  $this

        ->fileTable(",

            patient_entries.first_name as pfn, 
            patient_entries.last_name as pln, 
            file_assignments.deadline, 
            file_assignments.remarks, 
            (SELECT email FROM users WHERE users.id = file_assignments.user_id) as assigned_to, 
            file_assignments.id as fa_id,
            file_assignments.status,

            (SELECT email FROM users INNER JOIN folders WHERE users.id = folders.created_by_id AND folders.id = f2.folder_id LIMIT 0,1) as folder_creator2,

            folders2.name as folder_name2,
            media2.file_name as file_name2,
            f2.relative_path as relative_path2
            
            ")



        ->join("file_assignments","file_assignments.file_id","=","f.id")
        ->leftJoin("files as f2","f2.id","=","file_assignments.remark_file_id")
        ->leftJoin("folders as folders2","f2.folder_id","=","folders2.id")
        ->leftJoin("media as media2","f2.id","=","media2.id");

        ;

        if($userId !== null){
            $userId = intval($userId);
            $fileTableSample = $fileTableSample->where([
                "file_assignments.user_id" => $userId
            ]);
        }

        if($showCompleted){
            $fileTableSample = $fileTableSample->where('status' , '!=', 'complete');
        }

        

        return $fileTableSample;

    }

}
