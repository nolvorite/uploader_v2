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

    /**
     * Show the form for creating new Folder.
     *
     * @return \Illuminate\Http\Response
     */
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
    public function show($id)
    {
        if (! Gate::allows('folder_view')) {
            return abort(401);
        }
        
        $created_bies = \App\User::get()->pluck('name', 'id')->prepend(trans('quickadmin.qa_please_select'), '');

        $files = \App\File::join("media","files.id","=","media.id")->join("users","users.id","=","created_by_id")->where('folder_id', $id)->get();

        $folder = Folder::findOrFail($id);
        $userFilesCount = File::join("media","files.id","=","media.id")->join("users","users.id","=","created_by_id")->where('created_by_id', Auth::getUser()->id)->count();

        return view('admin.folders.show', compact('folder', 'files', 'userFilesCount'));
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
