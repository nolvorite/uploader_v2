<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\FilterByUser;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use App\Media;

/**
 * Class File
 *
 * @package App
 * @property string $uuid
 * @property string $folder
 * @property string $created_by
*/
class File extends Model implements HasMedia
{
    use SoftDeletes, FilterByUser, HasMediaTrait;

    protected $fillable = ['uuid', 'folder_id', 'created_by_id'];
    
    /**
     * Set to null if empty
     * @param $input
     */
    public function setFolderIdAttribute($input)
    {
        $this->attributes['folder_id'] = $input ? $input : null;
    }

    public function withMedia(){
        return $this->hasOne(Media::class, 'id', 'id');
    }

    /**
     * Set to null if empty
     * @param $input
     */
    public function setCreatedByIdAttribute($input)
    {
        $this->attributes['created_by_id'] = $input ? $input : null;
    }
    
    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id')->withTrashed();
    }
    
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
    
}
