<?php

namespace App\Http\Controllers\Admin;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFilesRequest;
use App\Http\Requests\Admin\UpdateFilesRequest;
use App\Http\Controllers\Traits\FileUploadTrait;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File as FileManager; 

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;
use Faker\Provider\Uuid;

use Illuminate\Support\Facades\DB as DB;



class FilesController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of File.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('file_access')) {
            return abort(401);
        }
        if ($filterBy = Input::get('filter')) {
            if ($filterBy == 'all') {
                Session::put('File.filter', 'all');
            } elseif ($filterBy == 'my') {
                Session::put('File.filter', 'my');
            }
        }

        $default = Gate::allows('file_manager') ? 'all' : 'my';
        $email = auth()->user()->email;

        $view = $default;

        $basePathCheck = Input::get('currentBasePath') !== null ? Input::get('currentBasePath') : null;



        $selector = $this->fileTable();

        switch($view){
            case "all":
                //
            break;
            case "my":
                $selector = $selector->where(function($query){
                    $query->where('f.created_by_id',auth()->user()->id)->orWhere("f.path","LIKE",auth()->user()->email."%");
                }); 
            break;
        }

        if (request('show_deleted') == 1) {
            if (!Gate::allows('file_delete')) {
                return abort(401);
            }
            $files = $selector->whereNotNull("f.deleted_at");
        }else{
            $files = $selector->where(function($query){
                $query->whereNull("f.deleted_at");
            });
        } 

        DB::connection()->enableQueryLog();
        if($basePathCheck !== null){
            $splitter = explode("/",$basePathCheck);
            $files = $selector->where('path','like',$basePathCheck.'%');
            if($files->get()->count() === 0){
                //var_dump(DB::getQueryLog());
                //return redirect('admin/files');
            }
        }

        $files = $selector->get();

        $userFilesCount = $files->count();

        return view('admin.files.index', compact('files', 'userFilesCount','view'));
    }

    /**
     * Show the form for creating new File.
     *
     * @return \Illuminate\Http\Response
     */
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

        return view('admin.files.create', compact('folders', 'created_bies', 'userFilesCount', 'roleId', 'folderId'));
    }

    public function listOfEmployeesROR(Request $request){
        if(!Gate::allows('ror_supervision')){
            return abort(401);
        }

        $data = ['action' => 'listOfEmployeesROR'];
        $data['data'] = DB::table("users")->select("email","id","name",DB::Raw('(SELECT COUNT(*) FROM file_assignments WHERE file_assignments.user_id = users.id AND file_assignments.status != \'complete\') as assigned_file_count'))->where("role_id",5)->get();

        return response()->json($data);
    }

    public function getAsDownloadable(Request $request){
        if(!Gate::allows('file_manager')){
            return abort(401);
        }

        $path = $request->get('link');

        return Response::download($path);

    }

    public function generateDownloadLink(){
        if(!Gate::allows('file_manager')){
            return abort(401);
        }

        $result = ['status' => false];

        $currentDate = date("Y-m-d")."-".microtime(true);

        $fileName = "FOLDER_COMPILED_".$currentDate;

        $pathToGet = session('current_path');

        $uploadingUnderFolderPath = storage_path("app\public\zips");

        if(!file_exists($uploadingUnderFolderPath)){
            
            try {
                mkdir($uploadingUnderFolderPath, 0775);
                $zipsDirExists = true;
            }
            catch (\Exception $e){
                $zipsDirExists = false;
                $result['error'] = $e->getMessage();
            } 

        }else{
            $zipsDirExists = true;
        }

        if($zipsDirExists){
            // dd(
            //     storage_path("app\public\zips\\".$fileName.'.zip'),
            //     storage_path("app\public\\".$pathToGet.'/*')
            // );

            \Zipper::make(storage_path("app\public\zips\\".$fileName.'.zip'))->add(glob(storage_path("app\public\\".$pathToGet.'/*')))->close();

            try {

                //check to see if file was actually created
                $size = \File::size(storage_path("app\public\zips\\".$fileName.'.zip'));

                DB::table("zips")->insert([
                    'created_by_id' => auth()->user()->id,
                    'full_path_and_file' => "zips/".$fileName.".zip",
                    'path_of_folder' => $pathToGet,
                    'file_size' => $size
                ]);

                $result = ['status' => true];

            }catch(Exception $e){
                $result['error'] = $e->getException();
            }

            

        }

        return Response::json($result);

    }

    public function downloadFolder(Request $request){
        if(!Gate::allows('file_manager')){
            return abort(401);
        }

        $data = ['status' => false, 'download_links' => [], 'already_existed' => false, 'message' => ''];
        $path = $request->get('path');

        session(['current_path' => $path]);

        //first, search
        $listofZipsForThisFolder = DB::table('zips')->where("path_of_folder",$path)->orderBy('id','DESC')->get();

        if(count($listofZipsForThisFolder) > 0){
            $data['status'] = true;
            $data['download_links'] = $listofZipsForThisFolder;
            $data['message'] = "There are ". count($listofZipsForThisFolder) ." zips for this folder.";
        }else{
            $data['message'] = "There are no zips for this folder. Would you like to make one?";
        }

        return response()->json($data);

    }

    public function listOfUnassignedFilesROR(Request $request){
        
        if(!Gate::allows('ror_supervision')){
            return abort(401);
        }

        DB::enableQueryLog();

        $excludeCheck = Input::post('exclude') !== null;

        $queryCheck = Input::post('user_id') !== null ? Input::post('user_id') : null;

        if(!$excludeCheck && $queryCheck){
            session(['user_id_temp' => $queryCheck]);
        }

        $additionalColumns = ", (SELECT COUNT(*) FROM file_assignments WHERE file_assignments.file_id = f.id) as assignees, file_assignments.deadline, file_assignments.remarks,file_assignments.id as fa_id";

        $data = ['action' => 'listOfUnassignedFilesROR'];

        $dataFetcher = $this->fileTable($additionalColumns);

        if($queryCheck !== null && !$excludeCheck){

            $dataFetcher = $dataFetcher->join("file_assignments","file_assignments.file_id","=","f.id")->where("file_assignments.user_id", $queryCheck);

        }else{

            $dataFetcher = $dataFetcher->leftJoin("file_assignments","file_assignments.file_id","=","f.id");

            if(!$excludeCheck){
                //
            }else{
                //filter out already assigned files
                $dataFetcher = $dataFetcher->where(function($query){
                    $query->whereNotIn("file_assignments.user_id",[session('user_id_temp')])->orWhereNull("file_assignments.user_id");
                });

                
            }
           
        }

        $dataFetcher = $dataFetcher->where(function($query){
            $query->where("f.created_by_id",auth()->user()->id);
        });

        //as it turns out, you can only assign files made by the user itself sooo...

        $data['data'] = $dataFetcher

            //->where("file_assignments.status","!=","complete")
            
            ->orderBy("assignees","ASC")

            ->get();


        //dd(DB::getQueryLog());


        return response()->json($data);
    }



    public function deleteAssignment(Request $request){
        if(!Gate::allows('ror_supervision')){
            return abort(401);
        }

        $data = ['status' => false, 'fa_id' => $request->get('fa_id')];



        $deleter = DB::table("file_assignments")->where("id",$request->get('fa_id'))->delete();

        $data['status'] = true;
        

        return response()->json($data);
    }

    public function submitAssignments(Request $request){
        if(!Gate::allows('ror_supervision')){
            return abort(401);
        }

        $data = ['status' => false];

        foreach($request->get("file_ids") as $key => $val){
            $deleter = DB::table("file_assignments")->insert([
                'user_id' => $request->get('employee_id'),
                'file_id' => $val,
                'remark_file_id' => 0,
                'remarks' => '',
                'patient_id' => '0',
                'deadline' => DB::Raw("STR_TO_DATE('". $request->get('deadline') ."','%m/%d/%Y')")
            ]);
        }

        $data['status'] = true;

        return response()->json($data);
    }

    public function listOfEligibleFilesForRemark(Request $request){
        if(!Gate::allows('ror_maintenance')){
            return abort(401);
        }

        $userId = auth()->user()->id;

        $results = ['status' => true];
        
        $dataSample = $this->fileTable();

        $dataSample = $dataSample

        ->where(function($query){
            $query
            ->whereNull("patient_id")
            ->orWhere("patient_id","0");
        })

        ->where("f.created_by_id",$userId)

        ;

        $dataSample = $dataSample->orderBy("f.id","DESC");

        $dataSample = $dataSample->get();

        $results['data'] = $dataSample;

        return response()->json($results);

    }

    public function resetAsPending(Request $request){
        if(!Gate::allows('ror_supervision')){
            return abort(401);
        }

        $data = ['status' => true, 'message' => ''];
        $assignmentId = $request->get("assignment_id");

        DB::table('file_assignments')->where("id",$assignmentId)->update(["status"=>"pending"]);

        $data['message'] = "File Assignment reset to pending.";

        return response()->json($data);

    }

    public function assignAsRemark(Request $request){
        if(!Gate::allows('ror_maintenance')){
            return abort(401);
        }
        $data = ['status' => false, 'message' => ''];

        $assignmentId = $request->get('assignment_id');
        $fileId = $request->get('file_id');

        if(!Gate::allows('ror_supervision')){
            
            $validityCheck = DB::table('file_assignments')->where(['user_id' => auth()->user()->id, 'id' => $assignmentId])->get();

            $validityCheck2 = DB::table('files')->where(['created_by_id' => auth()->user()->id, 'id' => $fileId])->get();

            if(count($validityCheck) > 0 && count($validityCheck2) > 0){
                $data['status'] = true;
                DB::table('file_assignments')->where("id",$assignmentId)->update(["remark_file_id"=>$fileId]);
                $data['message'] = "Marked as Remark file.";
            }

        }else{

            DB::table('file_assignments')->where("id",$assignmentId)->update(["remark_file_id"=>$fileId]);
            $data['status'] = true;
            $data['message'] = "Successfully marked as complete.";

        }

        return response()->json($data);

    }

    public function markAsComplete(Request $request){
        if(!Gate::allows('ror_maintenance')){
            return abort(401);
        }
        $data = ['status' => false, 'message' => ''];

        $assignmentId = $request->get('assignment_id');

        if(!Gate::allows('ror_supervision')){
            
            $validityCheck = DB::table('file_assignments')->where(['user_id' => auth()->user()->id, 'id' => $assignmentId])->get();
            
            if(count($validityCheck) > 0){
                $data['status'] = true;
                DB::table('file_assignments')->where("id",$assignmentId)->update(["status"=>"approval_check"]);
                $data['message'] = "Successfully marked as complete. This is still pending approval by a supervisor, however.";
            }

        }else{

            DB::table('file_assignments')->where("id",$assignmentId)->update(["status"=>"complete"]);
            $data['status'] = true;
            $data['message'] = "Successfully marked as complete.";

        }

        return response()->json($data);

    }

    public function finishEditingRemark(Request $request){
        if(!Gate::allows('ror_supervision')){
            return abort(401);
        }

        $data = ['status' => true, 'contents' => ''];
        $assignmentId = $request->get('assignment_id');
        $contents = $request->get('contents');
        $getRemarks = DB::table('file_assignments')->where('id',$assignmentId)->update(["remarks"=>$contents]);

        $data['contents'] = $contents;

        return response()->json($data);

    }

    public function editRemark(Request $request){
        if(!Gate::allows('ror_supervision')){
            return abort(401);
        }

        $data = ['status' => true, 'contents' => ''];
        $assignmentId = $request->get('assignment_id');
        $getRemarks = DB::table('file_assignments')->where('id',$assignmentId)->first();

        $data['contents'] = $getRemarks->remarks;

        return response()->json($data);
    }

    public function listFilesROR(Request $request){
        if(auth()->user()->role_id === 5){
            $rorList = $this->rorAssignments(auth()->user()->id,$request->has('show_completed'));
        }else if(preg_match("#^1|4$#",auth()->user()->role_id)){
            $rorList = $this->rorAssignments(null,$request->has('show_completed'));
        }

        $rorList = $rorList->join("patient_entries","patient_entries.patient_id","=","f.patient_id")->get();
        
        return view('admin.files.ror', compact('rorList'));
    }

    public function assignFilesROR(Request $request){
        return view('admin.files.assign');
    }

    public function fileManager(Request $request)
    {
        if (!Gate::allows('file_manager')) {
            return abort(401);
        }
        
        $roleId = Auth::getUser()->role_id;
        $userFilesCount = File::where('created_by_id', Auth::getUser()->id)->count();

        $folderId = $request->folder_id !== null ? intval($request->folder_id) : null;


        $created_bies = \App\User::get()->pluck('name','id')->prepend(trans('quickadmin.qa_please_select'), '');
        $folders = DB::table('folders')->select("users.email","folders.name","folders.id")->join("users","folders.created_by_id","=","users.id")->get();

        return view('admin.files.manage', compact('folders', 'created_bies', 'userFilesCount', 'roleId', 'folderId'));
    }

    /**
     * Store a newly created File in storage.
     *
     * @param  \App\Http\Requests\StoreFilesRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFilesRequest $request)
    {
        if (!Gate::allows('file_create')) {
            return abort(401);
        }
        
        // $request = $this->saveFiles($request);

        // $data = $request->all();
        // $fileIds = $request->input('filename_id');

        // foreach ($fileIds as $fileId) {
        //     $file = File::create([
        //         'id' => $fileId,
        //         'uuid' => (string)\Webpatser\Uuid\Uuid::generate(),
        //         'folder_id' => $request->input('folder_id'),
        //         'created_by_id' => Auth::getUser()->id

        //     ]);
        // }

        // foreach ($request->input('filename_id', []) as $index => $id) {
        //     $model = config('laravel-medialibrary.media_model');
        //     $file = $model::find($id);
        //     $file->model_id = $file->id;
        //     $file->save();
        // }

        $subject = "A user has added a file to his folder. List of files: ";



        try {
            Mail::send('emailtemplate', $emailSendData, function($message) use ($recipient,$subject){
                try {
                    $message->to($recipient, "User")
                        ->subject($subject);
                    $message->from(env('MAIL_USERNAME'),env('MAIL_FROM_NAME'));
                } catch(Exception $e){
                    exit();
                }
            });
        } catch(Exception $e){
            $response .= " However, we were unable to send an email to your specified recipient email addresses.";
        }



        return redirect()->route('admin.files.index');

    }


    /**
     * Show the form for editing File.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Update File in storage.
     *
     * @param  \App\Http\Requests\UpdateFilesRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFilesRequest $request, $id)
    {
        if (!Gate::allows('file_edit')) {
            return abort(401);
        }
        $request = $this->saveFiles($request);
        $file = File::findOrFail($id);
        $file->update($request->all());


        $media = [];
        foreach ($request->input('filename_id', []) as $index => $id) {
            $model = config('laravel-medialibrary.media_model');
            $file = $model::find($id);
            $file->model_id = $file->id;
            $file->save();
            $media[] = $file->toArray();
        }
        $file->updateMedia($media, 'filename');

        return redirect()->route('admin.files.index');
    }


    /**
     * Display File.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Remove File from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('file_delete')) {
            return abort(401);
        }
        $file = File::findOrFail($id);
        $file->deletePreservingMedia();

        return redirect()->route('admin.files.index');
    }

    /**
     * Delete all selected File at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('file_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = File::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->deletePreservingMedia();
            }
        }
    }


    /**
     * Restore File from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('file_delete')) {
            return abort(401);
        }
        $file = File::onlyTrashed()->findOrFail($id);
        $file->restore();

        return redirect()->route('admin.files.index');
    }

    /**
     * Permanently delete File from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('file_delete')) {
            return abort(401);
        }

        $permissionChecks = [
            'is_creator_of_files' => count(DB::table('folders')->where(['created_by_id' => auth()->user()->id, 'id' => $id])->get()) > 0,
            'can_edit_others_files' => Gate::allows('can_delete_others_files')
        ];

        if($permissionChecks['is_creator_of_files'] || $permissionChecks['can_edit_others_files']){
            $file = File::onlyTrashed()->withMedia()->findOrFail($id);

            FileManager::delete(storage_path("app\public\\".$file->first()->path."\\".$file->first()->get()->file_name));

            $file->forceDelete();

        }        

        return redirect()->route('admin.files.index');
    }
}
