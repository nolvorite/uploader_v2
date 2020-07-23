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


class FoldersController extends Controller
{
    /**
     * Display a listing of Folder.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('folder_access')) {
            return abort(401);
        }
        if ($filterBy = Input::get('filter')) {
            if ($filterBy == 'all') {
                Session::put('Folder.filter', 'all');
            } elseif ($filterBy == 'my') {
                Session::put('Folder.filter', 'my');
            }
        }

        $default = auth()->user()->role_id === 1 ? 'all' : 'my';

        $view = Input::get('filter') ? Input::get('filter') : $default;

        if (request('show_deleted') == 1) {
            if (! Gate::allows('folder_delete')) {
                return abort(401);
            }
            $folders = Folder::select('folders.name', 'users.name as users_name','folders.id as id','users.id as user_id','created_by_id','email')->join("users","users.id","=","created_by_id")->onlyTrashed()->get();
        } else {
            $folders = Folder::select('folders.name', 'users.name as users_name','folders.id as id','users.id as user_id','created_by_id','email')->join("users","users.id","=","created_by_id")->get();
        }

        return view('admin.folders.index', compact('folders','view'));
    }

    public function listOfFilesBasic(Request $request){

    }

    public function addSubFolder(Request $request){
        $returnVal = ['status' => false, 'errors' => ''];
        $path = $request->path;
        $folderName = $request->name;
        $legitFolderName = true;
        $isCreated = false;
        $path = preg_replace("#/#","\\",$path);

        $uploadingUnderFolderPath = storage_path("app\public\\".$path);
        $uploadingPath = storage_path("app\public\\".$path."\\".$folderName);

        if(preg_match("#[\/\\\]#",$folderName)){
            $legitFolderName = false;
        }

        if($legitFolderName && file_exists($uploadingUnderFolderPath) && !file_exists($uploadingPath)){
            //check to see if they have permission to upload to this folder
            //either admin or matching email address for subfolder
            $splitter = explode("\\",$path);
            //first folder will ALWAYS be an email address
            $roleCheck = auth()->user()->role_id <= 1;
            $returnVal['checks'] = [$splitter,auth()->user()->email];
            if($roleCheck || $splitter[0] === auth()->user()->email){
                try {
                    mkdir($uploadingPath, 0775);
                    $isCreated = true;
                }
                catch (\Exception $e){
                    $returnVal['error'] = $e->getMessage();

                }      
            }
        }else {
            $returnVal['error'] = "The directory you are trying to upload into doesn't exist, or the directory you are trying to make already exists.";
        }    
        $returnVal['status'] = $isCreated;
        return response()->json($returnVal);
    }

    /**
     * Display a listing of Folder.
     *
     * @return \Illuminate\Http\Response
     */

    public function getListOfFiles(Request $request){
        $path = "public/".$request->path;
        $returnVal = ['status' => false, 'data' => []];
        $pathSplit = explode("/",$path);

        $hasPermissionToView = auth()->user()->role_id === 1 || $pathSplit[1] === auth()->user()->email;
        if($hasPermissionToView){
           
            try {
                $filez = [
                    
                    'directories' => Storage::directories($path),
                    'files' => Storage::files($path)        
                ];
                $returnVal = ['status' => true, 'data' => $filez];
                $returnVal['fullPath'] = $path."/";
            } catch(Exception $e){
                $returnVal['error'] = $e->getMessage();
            }
        }else{
            $returnVal['error'] = "You do not have permission to view this file.";
        }
        
        


        return response()->json($returnVal);
    }

    private function directoryScout($directory = "",$level = 1){
        $this->currentDirectory = $directory !== "" ? $directory : $this->rootDirectory ;
        $this->directoryCache = Storage::directories($this->currentDirectory);

        $currentArray = $this->directoryCache;

        $returnVal = [];

        $currentId = 0;

        foreach($currentArray as $key => $directoryName){

            $directoryFull = $directoryName;
            $directoryName = explode("/",$directoryName);
            $directoryName = $directoryName[count($directoryName)-1];
            if(($level === 2 && ((auth()->user()->role_id !== 1 && $directoryName === auth()->user()->email) || auth()->user()->role_id === 1)) || $level !== 2){
                $returnVal[count($returnVal)] = ['folder_name' => $directoryName,'folder_id' => $this->returnThenAdd(), 'full_path'=> $directoryFull , 'subfolders' => $this->directoryScout($directoryFull,($level+1))];
            } 
                
            
            
        }        




        
        return $returnVal;
    }

    public function getFolderList(Request $request){
        $returnValue = ['status' => false, 'error' => ""];
        try {
        if (! Gate::allows('folder_view')) {
            $returnValue['error'] = "You do not have sufficient permissions to view folders.";
        }else{
            //get role ID
            $userData = auth()->user();
            $rootDirectory = "";
            

            $currentDirectory = $rootDirectory . $request->directory;

            $this->rootDirectory = $rootDirectory;

            // $directories = Storage::allDirectories($currentDirectory);
            // $files = Storage::files($currentDirectory);
            // $listOfFilesToIgnore = ['.gitignore'];
            // $files = array_diff($files,$listOfFilesToIgnore);
            $returnValue['directories'] = $this->directoryScout();
            $returnValue['directoriesPlain'] = Storage::allDirectories($currentDirectory);
        } }
        catch(Exception $e){
            $returnValue['error'] = "Error in obtaining folder." . $e ->getMessage();
        }

        $returnValue['status'] = ($returnValue['error'] === "");

        return response()->json($returnValue);
    }



    public function create()
    {
        if (! Gate::allows('folder_create')) {
            return abort(401);
        }
        
        $created_bies = \App\User::get()->pluck('name', 'id')->prepend(trans('quickadmin.qa_please_select'), '');

        return view('admin.folders.create', compact('created_bies'));
    }

    /**
     * Store a newly created Folder in storage.
     *
     * @param  \App\Http\Requests\StoreFoldersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFoldersRequest $request)
    {
        if (! Gate::allows('folder_create')) {
            return abort(401);
        }

        $requestD = $request->all();

        $email = auth()->user()->email;

        $uploadPath = storage_path("app\public\\".$email."\\".$requestD['name']);
        $uploadPath2 = storage_path("app\public\\".$email."\\");

        if(file_exists($uploadPath)){
            throw ValidationException::withMessages(['field_name' => 'Directory already exists. Please select another name.']);
        }else {
            $isCreated = false;
            $counter = 0;
            while($counter < 3 && !$isCreated){
                try {
                    mkdir($uploadPath, 0775);
                    $folder = Folder::create($requestD);
                    $isCreated = true;
                }
                catch (\Exception $e){

                    if(preg_match("#No such file or directory#",$e->getMessage())){
                        mkdir($uploadPath2, 0775);
                    }else{
                        throw ValidationException::withMessages(['field_name' => 'Could not create new folder. Error message: ' . $e->getMessage()]); 
                    }

                }  
                $counter++;
            }
            
        }

        return redirect('admin/folders');
    }


    /**
     * Show the form for editing Folder.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('folder_edit')) {
            return abort(401);
        }
        
        $created_bies = \App\User::get()->pluck('name', 'id')->prepend(trans('quickadmin.qa_please_select'), '');

        $folder = Folder::findOrFail($id);

        return view('admin.folders.edit', compact('folder', 'created_bies'));
    }

    /**
     * Update Folder in storage.
     *
     * @param  \App\Http\Requests\UpdateFoldersRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFoldersRequest $request, $id)
    {
        if (! Gate::allows('folder_edit')) {
            return abort(401);
        }
        $folder = Folder::findOrFail($id);
        $folder->update($request->all());



        return redirect()->route('admin.folders.index');
    }


    /**
     * Display Folder.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        if (! Gate::allows('folder_view')) {
            return abort(401);
        }
        
        $default = auth()->user()->role_id === 1 ? 'all' : 'my';

        $view = Input::get('filter') ? Input::get('filter') : $default;

        $folder = Folder::select(DB::Raw("id,name,created_by_id,(SELECT email FROM users WHERE created_by_id = users.id) as email"))->findOrFail($id);

        $basePathCheck = Input::get('currentBasePath') !== null ? Input::get('currentBasePath') : null;


        $selector = $this->fileTable()->where("f.folder_id",$id);

        if (request('show_deleted') == 1) {
            if (!Gate::allows('file_delete')) {
                return abort(401);
            }
            $files = $selector->whereNotNull("deleted_at");
        } 


        if($basePathCheck !== null){
            $splitter = explode("/",$basePathCheck);
            $files = $selector->where('path','like',$basePathCheck.'%');
            if($files->get()->count() === 0){
                return redirect('admin/files');
            }
        }

        $files = $selector->get();

        $userFilesCount = $files->count();
        return view('admin.folders.show', compact('folder', 'files', 'view' ,'userFilesCount'));
    }


    /**
     * Remove Folder from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('folder_delete')) {
            return abort(401);
        }
        $folder = Folder::findOrFail($id);
        $folder->delete();

        return redirect()->route('admin.folders.index');
    }

    /**
     * Delete all selected Folder at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (! Gate::allows('folder_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Folder::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Folder from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (! Gate::allows('folder_delete')) {
            return abort(401);
        }
        $folder = Folder::onlyTrashed()->findOrFail($id);
        $folder->restore();

        return redirect()->route('admin.folders.index');
    }

    /**
     * Permanently delete Folder from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (! Gate::allows('folder_delete')) {
            return abort(401);
        }
        $folder = Folder::onlyTrashed()->findOrFail($id);
        $folder->forceDelete();

        return redirect()->route('admin.folders.index');
    }
}
