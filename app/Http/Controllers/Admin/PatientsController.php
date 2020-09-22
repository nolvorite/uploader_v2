<?php

namespace App\Http\Controllers\Admin;

use App\File;
use App\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFoldersRequest;
use App\Http\Requests\Admin\UpdateFoldersRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB as DB;


class PatientsController extends Controller
{
    /**
     * Display a listing of Folder.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
     	if (!Gate::allows('file_access')) {
            return abort(401);
        }
        // if ($filterBy = Input::get('filter')) {
        //     if ($filterBy == 'all') {
        //         Session::put('File.filter', 'all');
        //     } elseif ($filterBy == 'my') {
        //         Session::put('File.filter', 'my');
        //     }
        // }

        // $default = auth()->user()->role_id === 1 ? 'all' : 'my';

        // $view = $default;

        // $basePathCheck = Input::get('currentBasePath') !== null ? Input::get('currentBasePath') : null;



        // $selector = $this->fileTable();

        // switch($view){
        //     case "all":
        //         //
        //     break;
        //     case "my":
        //         $selector = $selector->where('f.created_by_id',auth()->user()->id);
        //     break;
        // }

        // if (request('show_deleted') == 1) {
        //     if (!Gate::allows('file_delete')) {
        //         return abort(401);
        //     }
        //     $files = $selector->whereNotNull("deleted_at");
        // } 


        // if($basePathCheck !== null){
        //     $splitter = explode("/",$basePathCheck);
        //     $files = $selector->where('path','like',$basePathCheck.'%');
        //     if($files->get()->count() === 0){
        //         return redirect('admin/files');
        //     }
        // }

        // $files = $selector->get();

        // $userFilesCount = $files->count();

        $patients = [];

        return view('admin.patients.index', ['patients' => $patients]);

    }

    public function listPatients(Request $request){
        $returnVal = ['status' => false, 'data' => []];
        if(Auth::check()){
            
        }
        return response()->json($returnVal);
    }

    public function create(Request $request)
    {
        if (!Gate::allows('file_create')) {
            return abort(401);
        }
        
        $roleId = Auth::getUser()->role_id;
        $userFilesCount = File::where('created_by_id', Auth::getUser()->id)->count();

        $folderId = $request->folder_id !== null ? intval($request->folder_id) : null;


        $created_bies = \App\User::get()->pluck('name','id')->prepend(trans('quickadmin.qa_please_select'), '');
        $folders = \App\Folder::select("users.email","folders.name","folders.id")->join("users","folders.created_by_id","=","users.id")->get();

        return view('admin.patients.create', compact('folders', 'created_bies', 'userFilesCount', 'roleId', 'folderId'));
    }

  
}
