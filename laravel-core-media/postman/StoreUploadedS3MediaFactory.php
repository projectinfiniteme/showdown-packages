<?php

namespace App\Postman;

use AttractCores\LaravelCoreMedia\Http\Requests\MediaRequest;
use AttractCores\PostmanDocumentation\Factory\FormRequestFactory;
use Illuminate\Support\Str;

/**
 * Class StoreUploadedS3MediaFactory
 *
 * @package App\Postman
 * Date: 10.01.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class StoreUploadedS3MediaFactory extends FormRequestFactory
{

    /**
     * The name of the factory's corresponding form request or full route name.
     *
     * @var string|null
     */
    protected ?string $request = MediaRequest::class;

    /**
     * Return definition for post action via S3 triggers.
     *
     * @param string $routeName
     *
     * @return array
     */
    public function postDefinition(string $routeName) : array
    {
        if ( Str::contains($routeName, 's3') ) {
            return [
                'original_name' => NULL,
                'path'          => '{PATH_TO_REAL_FILE_ON_S3_BUCKET}',
            ];
        }

        return [];
    }

}