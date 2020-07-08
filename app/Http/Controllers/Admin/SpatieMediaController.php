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
        $uploadSucceededWithoutErrors = true;
        foreach ($files as $file) {
            try {
                $model->exists     = true;
                $media             = $model->addMedia($file)->withCustomProperties(['folder_id' => $folderId])->toMediaCollection($email);

                $file = File::create([
                    'id' => $media['id'],
                    'uuid' => (string) \Webpatser\Uuid\Uuid::generate(),
                    'folder_id' => $folderId,
                    'created_by_id' => Auth::getUser()->id
                ]);

                $addedFiles[]      = $media;
            } catch (\Exception $e) {
                $uploadSucceededWithoutErrors = false;
                abort(500, 'Could not upload your file:' . $e->getMessage());
            }
        }

        if($uploadSucceededWithoutErrors){
            //$request2 = $this->saveFiles($request2);
         }

        return response()->json(['files' => $addedFiles]);
    }
}
