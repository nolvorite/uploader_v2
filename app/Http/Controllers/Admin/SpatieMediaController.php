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


class SpatieMediaController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    use FileUploadTrait;


    public function create(Request $request,StoreFilesRequest $request2)
    {
        if (! $request->has('model_name') || ! $request->has('file_key') || ! $request->has('bucket')) {
            return abort(500);
        }

        $model = 'App\\' . $request->input('model_name');
        try {
            $model = new $model();
        } catch (ModelNotFoundException $e) {
            abort(500, 'Model not found');
        }

        $files      = $request->file($request->input('file_key'));
        $addedFiles = [];
        $folderId = intval($request->input('folder_id'));
        $email = auth()->user()->email;
        $path = $request->path;
        $croppedPathForEmailCheck = substr($path,0,strlen($email));
        $comparison = $email === $croppedPathForEmailCheck;
        $returnVal = ['error' => ''];        

        if(auth()->user()->role_id === 1 || $comparison){
            $uploadSucceededWithoutErrors = true;
            foreach ($files as $file) {
                try {
                    $model->exists     = true;

                    $folderData = DB::table('folders')->where('id', $folderId)->first();
                    $folderName = $folderData->name;
                    
                    $userData = DB::table('users')->where('id', $folderData->created_by_id)->first();
                    $email =  $userData->email;

                    $basicPath = $email . "/" . $folderName;

                    $relativePath = substr($path,strlen($basicPath)+1) === false ? "" : substr($path,strlen($basicPath)+1);

                    $media = $model->addMedia($file)->withCustomProperties(['folder_id' => $folderId,'relativePath'=> $relativePath])->toMediaCollection($email);

                    

                    $file = DB::table('files')->insert([
                        'id' => $media['id'],
                        'uuid' => (string) \Webpatser\Uuid\Uuid::generate(),
                        'folder_id' => $folderId,
                        'path' => $path,
                        'relative_path' => $relativePath,
                        'created_at'=>DB::raw('NOW()'),
                        'updated_at'=>DB::raw('NOW()'),
                        'created_by_id' => Auth::getUser()->id
                    ]);

                    $addedFiles[] = $media;
                } catch (\Exception $e) {
                    $uploadSucceededWithoutErrors = false;
                    $returnVal['error'] = $e->getMessage();
                }
            }

        }else{
            $returnVal['error'] = "You do not have sufficient privileges to upload in this directory.";
        }

        $returnVal['files'] = $addedFiles;

        return response()->json($returnVal);
    }
}
