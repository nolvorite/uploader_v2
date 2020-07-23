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
    public function fileTable(){
    	return DB::table("files as f")->select(DB::raw('(SELECT email FROM users INNER JOIN folders WHERE users.id = folders.created_by_id AND folders.id = f.folder_id LIMIT 0,1) as folder_creator,f.id,uuid,f.created_at,f.folder_id,f.created_by_id as file_creator,f.path,f.relative_path,media.id,model_type,media.name,media.file_name,folders.name as folder_name,media.mime_type,custom_properties,order_column,media.created_at,media.size,users.email'))->join("folders","folders.id","=","f.folder_id")->join("users","users.id","=","f.created_by_id")->join("media","f.id","=","media.id");
    }
    public function returnThenAdd(){
    	$num = $this->folderId;
    	$this->folderId++;
    	return $num;
    }



}
