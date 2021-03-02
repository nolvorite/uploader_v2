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

        $default = auth()->user()->role_id === 1 ? 'all' : 'my';
        $email = auth()->user()->email;

        $view = $default;

        $basePathCheck = Input::get('currentBasePath') !== null ? Input::get('currentBasePath') : null;



        $selector = $this->fileTable();

        switch($view){
            case "all":
                //
            break;
            case "my":
                $selector = $selector->where('f.created_by_id',auth()->user()->id)->orWhere("f.path","LIKE",$email."%");
            break;
        }

        if (request('show_deleted') == 1) {
            if (!Gate::allows('file_delete')) {
                return abort(401);
            }
            $files = $selector->whereNotNull("deleted_at");
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

    public function listFilesROR(Request $request){
        return view('admin.files.ror');
    }

    public function assignFilesROR(Request $request){
        return view('admin.files.assign');
    }

    public function fileManager(Request $request)
    {
        if (!Gate::allows('file_create')) {
            return abort(401);
        }
        
        $roleId = Auth::getUser()->role_id;
        $userFilesCount = File::where('created_by_id', Auth::getUser()->id)->count();

        $folderId = $request->folder_id !== null ? intval($request->folder_id) : null;


        $created_bies = \App\User::get()->pluck('name','id')->prepend(trans('quickadmin.qa_please_select'), '');
        $folders = \App\Folder::select("users.email","folders.name","folders.id")->join("users","folders.created_by_id","=","users.id")->get();

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
        $file = File::onlyTrashed()->findOrFail($id);
        $file->forceDelete();

        return redirect()->route('admin.files.index');
    }
}
