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
            $firstName = $request->has('first_name') ? $request->get('first_name') : '';
            $lastName = $request->has('last_name') ? $request->get('last_name') : '';    

            $initialQuery = DB::table('patient_entries')->SELECT(DB::raw('(SELECT email FROM users INNER JOIN folders WHERE users.id = folders.created_by_id AND folders.id = f.folder_id LIMIT 0,1) as folder_creator,f.id,uuid,f.created_at,f.folder_id,f.created_by_id as file_creator,f.path,f.relative_path,media.id,model_type,media.name,media.file_name,folders.name as folder_name,media.mime_type,custom_properties,order_column,media.created_at,media.size,patient_entries.patient_id, patient_entries.pdf_html_id,first_name,last_name,doctor_name,report_date, f.uuid'))->leftJoin("files as f","patient_entries.pdf_file_id","=","f.id")->leftJoin("folders","folders.id","=","f.folder_id")->leftJoin("media","f.id","=","media.id");

            if($firstName === '' || $lastName === ''){
                if($lastName === ''){
                    $data = $initialQuery->where('first_name', 'like' , "%$firstName%");
                }else{
                    $data = $initialQuery->where('last_name' , 'like', "%$lastName%");
                }
            }else{
                $data = $initialQuery->where('last_name', 'like', "%$lastName%")->where('first_name', 'like', "%$firstName%");
            }

            $data = $data->get();

            $returnVal['data'] = $data;
        }
        return response()->json($returnVal);
    }

    public function newPatient(Request $request){
        //get list of files added
        $files = $request->input('files');


        //first name, last name
        //report date

        $firstName = $request->input('first_name');
        $lastName = $request->input('last_name');
        $reportDate = $request->input('report_date');
        $doctorName = $request->input('doctor_name');

        //for each of the files, search the HTML file and the PDF file, and get a list of the ID's
        $counter =0;
        $fileIds = "";
        $HTMLID = -1;
        $PDFID = -1;
        $userId = auth()->user()->id;

        foreach($files as $file){

            if($counter > 0){
                $fileIds .= ",";
            }
            $fileIds .= intval($file['id']);
            $counter++;

            if(preg_match("#html#",$file['mime_type']) && $HTMLID === -1){
                $HTMLID = $file['id'];
            }

            if(preg_match("#pdf#",$file['mime_type']) && $PDFID === -1){
                $PDFID = $file['id'];
            }            

        }

        $patientEntry = DB::table('patient_entries')->insertGetId([
            'report_date' => DB::Raw("STR_TO_DATE('$reportDate','%m/%d/%Y')"),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'doctor_name' => $doctorName,
            'user_id' => $userId,
            'pdf_file_id' => $PDFID,
            'pdf_html_id' => $HTMLID
        ]);

        DB::update("UPDATE files SET patient_id = $patientEntry where id IN($fileIds)");

        return response('Successful addition.',200)->header('Content-Type', 'text/plain');

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
