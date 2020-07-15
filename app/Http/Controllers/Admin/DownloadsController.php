<?php

namespace App\Http\Controllers\Admin;

use App\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File as FileViewer;
use Illuminate\Support\Facades\Response;

use Spatie\MediaLibrary\Media;

use App\User;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use App\Folder;


class DownloadsController extends Controller
{
    public function download($uuid) {

        $file = File::where([
             ['uuid', '=', $uuid]
            //,['created_by_id', '=', Auth::getUser()->id]
            ])->first();

        $user = User::where(['id' => $file->created_by_id])->first();

        $media = Media::where('id', $file->id)->first();
        $folderId = $media['custom_properties']['folder_id'];

        $folder = Folder::where('id', $folderId)->first();
        
        $pathToFile = storage_path('app' . "/" . 'public' . "/" . $user->email . "/" . $folder->name . "/" . $media->file_name );

        $attributes = $media->getAttributes();

        switch($attributes['mime_type']){
            case "text/html":
                $returnVal = FileViewer::get($pathToFile);
            break;
            default:
                $returnVal = Response::download($pathToFile);
            break;
        }

        return $returnVal;
    }
}
