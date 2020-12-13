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
use App\Mail\EmailTemplate;
use Illuminate\Support\Facades\Mail;
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


        $patients = [];

        return view('admin.patients.index', ['patients' => $patients]);

    }

    public function listFiles(Request $request){
        $returnVal = ['status' => false, 'data' => [], 'errors' => ''];
        if($request->has('patient_id')){

            $patientId = intval($request->get('patient_id'));

            $returnVal['patient_id'] = $patientId;

            $data = DB::table(DB::raw('files as f'))->SELECT(DB::raw('(SELECT email FROM users 
                INNER JOIN folders WHERE users.id = fo.created_by_id AND fo.id = f.folder_id LIMIT 0,1) as folder_creator,
                fo.name as folder_name1,
                f.uuid as uuid1,f.created_at as created_at1,f.folder_id as folder_id1,f.created_by_id as file_creator1,f.path,f.relative_path as relative_path1,f.uuid as uuid1,
                m.id,m.model_type,m.name,m.file_name as file_name1,m.created_at,m.size,m.mime_type,m.custom_properties,m.order_column'))
            ->leftJoin("folders as fo","fo.id","=","f.folder_id")
            ->leftJoin("media as m","f.id","=","m.id")
            ->where("f.patient_id",$patientId)->get();

            $returnVal['status'] = true;
            $returnVal['data'] = $data;

        }
        
        return response()->json($returnVal);

    }

    public function listPatients(Request $request){
        $returnVal = ['status' => false, 'data' => []];

        if(Auth::check()){

            DB::connection()->enableQueryLog();

            $userId = auth()->user()->id;
            $roleId = auth()->user()->role_id;
            $email = auth()->user()->email;

            $firstName = $request->has('first_name') ? $request->get('first_name') : '';
            $lastName = $request->has('last_name') ? $request->get('last_name') : '';    

            $initialQuery = DB::table('patient_entries');

            if($firstName === '' || $lastName === ''){
                if($lastName === ''){
                    $data = $initialQuery->where('first_name', 'like' , "%$firstName%");
                }else{
                    $data = $initialQuery->where('last_name' , 'like', "%$lastName%");
                }
            }else{
                $data = $initialQuery
                ->where('last_name', 'like', "%$lastName%")
                ->where('first_name', 'like', "%$firstName%")
                ;                
            }

            if($roleId !== 1){

                //if not admin
                //the patient entry has to be created by the user, or has a file whose name of folder (that it belongs to) contains the current user's email
                $data = $data->where(function($query){
                    
                    $query
                    ->where('user_id',auth()->user()->id)
                    ->orWhere('doctor_name',auth()->user()->email)
                    ->orWhere(function($query){
                        $email = auth()->user()->email;
                        $query->whereNotNull(DB::raw('(SELECT users.id FROM users INNER JOIN files ON users.id = files.created_by_id WHERE files.path LIKE "'.$email.'%" AND files.patient_id = patient_entries.patient_id LIMIT 0,1)'));
                    });
                });
            }

            

            $data = $data->get();

            //var_dump(DB::getQueryLog());

            foreach($data as $key => $val){
                $patientId = $val->patient_id;
                $listOfFiles = DB::
                
                table('files')->select(DB::Raw("files.uuid,media.file_name,(SELECT email FROM users 
                INNER JOIN folders fo WHERE users.id = fo.created_by_id AND fo.id = files.folder_id LIMIT 0,1) as folder_creator, folders.name as folder_name,relative_path"))->leftJoin("folders","folders.id","=","files.folder_id")->leftJoin('media','media.id','=','files.id')->where('patient_id',$patientId)->get();
                $data[$key]->files = $listOfFiles;
            }

            $returnVal['data'] = $data;
        }
        return response()->json($returnVal);
    }

    public function newPatient(Request $request){
        //get list of files added

        $response = 'Successful addition.';

        $files = $request->input('files');


        //first name, last name
        //report date

        $firstName = $request->input('first_name');
        $lastName = $request->input('last_name');
        $reportDate = $request->input('report_date');
        $doctorName = $this->auth()->role_id === 1 ? $request->input('doctor_name') : $this->auth()->email;
        $action = $request->input('action');
        $patientId = $request->input('patient_id');
        $email = $request->input('email');

        //for each of the files, search the HTML file and the PDF file, and get a list of the ID's
        $counter =0;
        $fileIds = "";
        $HTMLID = -1;
        $PDFID = -1;
        $userId = auth()->user()->id;

        if(is_array($files)){
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
        }

        //if editing, check to see that the user has access

        if($action === "edit"){
            if(auth()->user()->role_id !== 1){

                $patientEntryCheck = DB::table('patient_entries')->where('patient_id', $patientId);

                if($patientEntryCheck->count() === 0){
                    return response($response,200)->header('Content-Type', 'text/plain');
                }

            }
        }

        $emailSendData = $request->all();

        $companyName = env('APP_NAME');

        $emailSendData['recipient'] =  $request->input('recipients');
        $emailSendData['content'] = $request->input('email_message');

        $recipient = explode(",",$emailSendData['recipient']);



        foreach($recipient as $i => $r){
            $recipient[$i] = trim($r);
            if(!preg_match("#^(.+)@(.+){2,}\.(.+){2,}$#",$recipient[$i])){
                unset($recipient[$i]);
            }
        }


        $recipient = array_values($recipient);
        
        $subject = ($action === "add") ? "Your patient profile has been registered in $companyName" : "Information regarding your patient file in $companyName";

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

        if($action === "add"){
            $patientEntry = DB::table('patient_entries')->insertGetId([
                'report_date' => DB::Raw("STR_TO_DATE('$reportDate','%m/%d/%Y')"),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'doctor_name' => $doctorName,
                'user_id' => $userId,
                'pdf_file_id' => $PDFID,
                'pdf_html_id' => $HTMLID,
                'email' => $email
            ]);

        }else{
            $patientEntry = DB::table('patient_entries')->where('patient_id', $patientId)->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'doctor_name' => $doctorName,
                'email' => $email
            ]);
            $patientEntry = $patientId;
        }

        if(is_array($files)){
            DB::update("UPDATE files SET patient_id = $patientEntry where id IN($fileIds)");
        }        

        

        return response($response,200)->header('Content-Type', 'text/plain');

    }

    public function searchUsers(Request $request){
        if(auth()->user()->role_id === 1){
            $query = $request->input('query');

            $data = DB::table('users')->select(DB::raw("name,email,first_name,last_name"))
            ->where('first_name', 'like', "%$query%")
            ->orWhere('last_name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%");

            $data = $data->get();

            $response = ['data' => $data];

            return response()->json($response);
        }
    }

    public function create(Request $request)
    {
        if (!Gate::allows('file_create')) {
            return abort(401);
        }

        $patientJSONData = [];

        if(isset($_GET['id'])){
            $patientEntryCheck = DB::table('patient_entries')->where('patient_id', $_GET['id']);
            if($patientEntryCheck->count() === 0){
                Redirect::to('/admin/patients/create');
                exit();
            }else{
                //check to see if user has authority
                if(auth()->user()->role_id !== 1){

                    $patientEntryCheck =DB::table('patient_entries')
                    ->where('patient_id', $_GET['id'])
                    ->orWhere('doctor_name',auth()->user()->email)
                    ;

                    if(count($patientEntryCheck) === 0){
                        Redirect::to('/admin/patients/create');
                        exit();
                    }

                }
            }
            $patientEntry = $patientEntryCheck->first();
            $patientId = $patientEntry->patient_id;
            $listOfFiles = DB::
                
                table('files')->select(DB::Raw("files.uuid,media.file_name,(SELECT email FROM users 
                INNER JOIN folders fo WHERE users.id = fo.created_by_id AND fo.id = files.folder_id LIMIT 0,1) as folder_creator, folders.name as folder_name,relative_path"))->leftJoin("folders","folders.id","=","files.folder_id")->leftJoin('media','media.id','=','files.id')->where('patient_id',$patientId)->get();
            $patientEntry->files = $listOfFiles;

            $patientJSONData = (array) $patientEntry;
        }
        
        $roleId = Auth::getUser()->role_id;
        $userFilesCount = File::where('created_by_id', Auth::getUser()->id)->count();

        $folderId = $request->folder_id !== null ? intval($request->folder_id) : null;

        $nameDisplay = strlen(auth()->user()->first_name) === 0 && strlen(auth()->getUser()->last_name) === 0 ? 

        auth()->getUser()->name 

        :

        auth()->getUser()->first_name . " " . auth()->getUser()->last_name;

        $nameDisplay = htmlspecialchars($nameDisplay);

        $created_bies = \App\User::get()->pluck('name','id')->prepend(trans('quickadmin.qa_please_select'), '');
        $folders = \App\Folder::select("users.email","folders.name","folders.id")->join("users","folders.created_by_id","=","users.id")->get();

        return view('admin.patients.create', compact('folders', 'created_bies', 'userFilesCount', 'roleId', 'folderId', 'nameDisplay','patientJSONData'));
    }

  
}
