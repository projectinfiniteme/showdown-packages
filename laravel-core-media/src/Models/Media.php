<?php

namespace AttractCores\LaravelCoreMedia\Models;

use Amondar\Sextant\Models\SextantModel;
use AttractCores\LaravelCoreClasses\Extensions\HasUUID;
use AttractCores\LaravelCoreMedia\Database\Factories\MediaFactory;
use AttractCores\LaravelCoreMedia\MediaStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class Media
 *
 * @property int         order                        - File ordering in sequence.
 * @property int         user_id                      - Creator id.
 * @property string|NULL original_name                - Client media name.
 * @property string      ext                          - File extension.
 * @property string      name                         - Server media name.
 * @property string      path                         - Server media path. Relative.
 * @property string      pre_signed_path              - [VIRTUAL] return pre-signed path.
 * @property string      disk                         - Disk where media was saved.
 * @property string      url                          - Media url
 * @property string      resize_url                   - Media resize url
 * @property string      crop_url                     - Media crop url
 * @property boolean     is_mocked                    - Check if media is mocked
 * @property boolean     is_raw                       - VIRTUAL field, determine that media is raw url or mocked.
 *
 * @property int         model_id                     - Id of morph model.
 * @property string      model_type                   - Model class name.
 * @property string      media_type_in_model          - relation name foe multiple media types on one model.
 *
 * @property Carbon|NULL created_at                   - Created at date.
 * @property Carbon|NULL updated_at                   - Updated at date.
 *
 * @version 2.0.0
 * @date    29/04/2020
 * @author  Yure Nery <yurenery@gmail.com>
 */
class Media extends SextantModel
{

    use SoftDeletes, HasFactory, HasUUID;

    public const LOCAL_TMP = 'tmp';

    /**
     * Possible fillable fields.
     *
     * @var array
     */
    protected $fillable = [ 'origin_name', 'name', 'path' ];

    /**
     * Cast attributes.
     *
     * @var array
     */
    protected $casts = [
        'is_mocked' => 'boolean',
        'order'     => 'integer',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return MediaFactory::new();
    }

    /**
     * Return relative path to the media or media for  current model.
     *
     * @param null $mediaName
     *
     * @return string
     */
    public static function relativeFilePath($mediaName = NULL) : string
    {
        return sprintf("%s%s", config('kit-media.tmp_path'), $mediaName);
    }

    /**
     * Get all of the owning model models.
     */
    public function model()
    {
        return $this->morphTo('model');
    }

    /**
     * Return medias by type inside model.
     *
     * @param      $query
     * @param null $type
     */
    public function scopeByTypeInFileAble($query, $type = NULL)
    {
        $query->where('media_type_in_model', $type);
    }

    /**
     * Set given type into media.
     *
     * @param null $type
     *
     * @return Media
     */
    public function withType($type = NULL)
    {
        $this->media_type_in_model = $type;

        return $this;
    }

    /**
     * Set given order into media.
     *
     * @param int $order
     *
     * @return Media
     */
    public function withOrder(int $order = 1)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Change disk.
     *
     * @param $disk
     * @param $path
     */
    public function changeFilePreferences($disk, $path)
    {
        $this->disk = $disk;
        $this->path = $path;
        $this->save();
    }

    /**
     * Make media public.
     *
     * @return bool
     */
    public function makePublic()
    {
        return MediaStorage::adapterByDisk($this->disk)->storage()->setVisibility($this->path, 'public');
    }

    /**
     * Get url attribute
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        // Check mocking or cdn url existence.
        if ( $this->is_raw ) {
            return $this->path;
        }

        return url(config('kit-media.base_path_prefix') . $this->pre_signed_path);
    }

    /**
     * Get resize url attribute
     *
     * @return string
     */
    public function getResizeUrlAttribute()
    {
        // Check mocking or cdn url existence.
        if ( $this->is_raw ) {
            return $this->path;
        } elseif ( ! config('kit-media.resize_on_fly.enabled') ) {
            return NULL;
        }

        return url(config('kit-media.resize_on_fly.resize_path') . $this->pre_signed_path);
    }

    /**
     * Get crop url attribute
     *
     * @return string
     */
    public function getCropUrlAttribute()
    {
        // Check mocking or cdn url existence.
        if ( $this->is_raw ) {
            return $this->path;
        } elseif ( ! config('kit-media.resize_on_fly.enabled') ) {
            return NULL;
        }

        return url(config('kit-media.resize_on_fly.crop_path') . $this->pre_signed_path);
    }

    /**
     * Return pre-signed media url.
     *
     * @return string
     */
    public function getPreSignedPathAttribute()
    {
        if ( ! MediaStorage::adapterByDisk($this->disk)->visibility() ) {
            return MediaStorage::adapterByDisk($this->disk)
                               ->temporaryPath(
                                   $this->path,
                                   now()->addMinutes(config('kit-media.pre_signed_lifetime.resources'))
                               );
        }

        return $this->path;
    }

    /**
     * Remove current media from storage.
     *
     * @return bool
     */
    public function removeFromStorage()
    {
        if ( ! $this->is_raw ) {
            return MediaStorage::adapterByDisk($this->disk)->delete($this->path);
        }

        return true;
    }

    /**
     * Check that model is raw.
     *
     * @return bool
     */
    public function getIsRawAttribute()
    {
        return $this->is_mocked || $this->disk == 'raw';
    }

    /**
     * Remove media physically.
     */
    public function removePhysically()
    {
        // Remove from storage.
        $this->removeFromStorage();

        // Force destroy in db.
        $this->forceDelete();
    }

}
