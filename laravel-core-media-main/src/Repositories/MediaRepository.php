<?php

namespace AttractCores\LaravelCoreMedia\Repositories;

use AttractCores\LaravelCoreClasses\CoreRepository;
use AttractCores\LaravelCoreMedia\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class FileRepository
 *
 * @property Media $model
 *
 * @version 1.0.0
 * @date    05/12/2018
 * @author  Yure Nery <yurenery@gmail.com>
 */
class MediaRepository extends CoreRepository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return Media::class;
    }

    /**
     * Store file model.
     *
     * @param FormRequest $request
     * @param string      $field
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store(FormRequest $request, $field = 'file')
    {
        /** @var UploadedFile $file */
        $file = $request->file($field);
        $path = $file->store(Media::LOCAL_TMP, $disk = 'public');

        return $this->saveFile(
            $request->user(),
            $file->getClientOriginalName(),
            last(explode('/', $path)),
            $path,
            $disk
        );
    }

    /**
     * Save file into DB.
     *
     * @param Model|null $user
     * @param            $originName
     * @param            $name
     * @param            $path
     * @param string     $disk
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function saveFile($user, $originName, $name, $path, $disk = 'public')
    {
        $this->model->forceFill([
            'original_name' => $originName,
            'path'          => $path,
            'name'          => $name,
            'ext'           => Str::lower(last(explode('.', $name))),
            'disk'          => $disk,
            'user_id'       => optional($user)->getKey(),
        ])->save();

        return $this->model;
    }

    /**
     * Store file from s3 disk.
     *
     * @param FormRequest $request
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function storeS3(FormRequest $request)
    {
        $originalName = $request->original_name;
        $path = $request->path;
        $name = Arr::last(explode('/', $path));

        return $this->saveFile(
            $request->user(),
            $originalName,
            $name,
            $path,
            's3'
        );
    }

}
