<?php

namespace App\Http\Controllers\Admin;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB as DB;
use App\Http\Requests\Admin\StoreFilesRequest;
use App\Http\Requests\Admin\UpdateFilesRequest;
use App\Http\Controllers\Traits\FileUploadTrait;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;
use Faker\Provider\Uuid;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Spatie\CustomPath;

use App\Mail\EmailTemplate;
use Illuminate\Support\Facades\Mail;


class SpatieMediaController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    use FileUploadTrait;


    public function create(Request $request,StoreFilesRequest $request2)
    {
        if (! $request->has('model_name')) {
            return abort(500);
        }

        $model = 'App\\' . $request->input('model_name');
        try {
            $model = new $model();
        } catch (ModelNotFoundException $e) {
            abort(500, 'Model not found');
        }

        
        $addedFiles = [];
        $folderId = intval($request->input('folder_id'));
        $email = auth()->user()->email;
        $path = $request->path;
        $croppedPathForEmailCheck = substr($path,0,strlen($email));
        $comparison = $email === $croppedPathForEmailCheck;
        $returnVal = ['error' => ''];        
        $isNewPatientEntry = $request->has('is_new_patient');        

        if(auth()->user()->role_id === 1 || $comparison){

            $uploadSucceededWithoutErrors = true;

            if($isNewPatientEntry){
                $pdf = $request->file('pdf_file');
                $html = $request->file('html_file');
                $others = $request->file('other_files');

                // $pdf['type'] = 'pdf';
                // $pdf['type'] = 'html';

                $files[] = $pdf;
                $files[] = $html;
                if($others !== null){
                    $files = array_merge($files,$others);    
                }

            }else{
                $files = $request->file($request->input('file_key'));
            }

            foreach ($files as $file) {

                try {

                    $model->exists = true;

                    $folderData = DB::table('folders')->where('id', $folderId)->first();
                    $folderName = $folderData->name;
                    
                    $userData = DB::table('users')->where('id', $folderData->created_by_id)->first();
                    $email =  $userData->email;

                    $basicPath = $email . "/" . $folderName;

                    $relativePath = substr($path,strlen($basicPath)+1) === false ? "" : substr($path,strlen($basicPath)+1);

                    $media = $model->addMedia($file)->withCustomProperties(['folder_id' => $folderId,'relativePath'=> $relativePath])->toMediaCollection($email); 

                    $type = '';

                    // if($isNewPatientEntry){

                    //     if(!isset($files['type'])){
                    //         $files['type'] = 'others';
                    //     }

                    // }                    

                    $file = DB::table('files')->insert([
                        'id' => $media['id'],
                        'uuid' => (string) \Webpatser\Uuid\Uuid::generate(),
                        'folder_id' => $folderId,
                        'path' => $path,
                        'relative_path' => $relativePath,
                        'created_at'=> DB::raw('NOW()'),
                        'updated_at'=> DB::raw('NOW()'),
                        'created_by_id' => Auth::getUser()->id
                    ]);

                    $addedFiles[] = $media;
                } catch (\Exception $e) {
                    $uploadSucceededWithoutErrors = false;
                    $returnVal['error'] = $e->getMessage();
                }
            }

            if(!$isNewPatientEntry && $uploadSucceededWithoutErrors){
                $subject = "A user has added a file to his folder.";
                $content = "A user has added a file to his folder. List of -files:";

                $emailSendData['recipient'] = env('MAIL_USERNAME');

                $recipient = $emailSendData['recipient'];
                

                foreach($addedFiles as $file){
                    $fileName = $file['file_name'];
                    $content .= "\"" . $basicPath . "/" . $fileName . "\", ";
                }

                $emailSendData['content'] = $content;

                try {
                    Mail::send('emailtemplate', $emailSendData, function($message) use ($recipient,$subject){
                        try {
                            $message->to(env('MAIL_USERNAME'), "User")
                                ->subject($subject);
                            $message->from(env('MAIL_USERNAME'),env('MAIL_FROM_NAME'));
                        } catch(Exception $e){
                            exit();
                        }
                    });
                } catch(Exception $e){
                    $response .= " However, we were unable to send an email to your specified recipient email addresses.";
                }

            }

        }else{
            $returnVal['error'] = "You do not have sufficient privileges to upload in this directory.";
        }

        $returnVal['files'] = $addedFiles;

        return response()->json($returnVal);
    }
}
