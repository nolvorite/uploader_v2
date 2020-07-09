<?php

namespace App\Spatie;

use Illuminate\Support\Facades\DB as DB;
use \Spatie\MediaLibrary\PathGenerator\PathGenerator;

use Spatie\MediaLibrary\Media;

class CustomPath implements PathGenerator
{

    /*
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        $folderData = DB::table('folders')->where('id', $media->getCustomProperty('folder_id'))->first();
        $folderName = $folderData->name;

        $email = auth()->user()->email;
        return $email."/". $folderName ."/";
    }

    /*
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        $folderData = DB::table('folders')->where('id', $media->getCustomProperty('folder_id'))->first();
        $folderName = $folderData->name;

        $email = auth()->user()->email;
        return $email."/". $folderName ."/conversions/";
    }

    /*
     * Get a (unique) base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        return $media->getKey();
    }
}
