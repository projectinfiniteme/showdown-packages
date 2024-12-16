<?php

namespace App\Postman;

use AttractCores\LaravelCoreMedia\Http\Requests\PreSignedRequest;
use AttractCores\PostmanDocumentation\Factory\FormRequestFactory;
use Illuminate\Support\Str;

/**
 * Class S3PreSignedRequestFactory
 *
 * @package App\Postman
 * Date: 10.01.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class S3PreSignedRequestFactory extends FormRequestFactory
{

    /**
     * The name of the factory's corresponding form request or full route name.
     *
     * @var string|null
     */
    protected ?string $request = PreSignedRequest::class;

    /**
     * Return definition for post action via S3 triggers.
     *
     * @return array
     */
    public function postDefinition() : array
    {
        return [
            'Key'           => config('kit-media.tmp_path') . '{UNIQUE_NAME_OF_THE_MEDIA}',
            'ContentType'   => '{MIME_TYPE_OF_THE_MEDIA}',
            'ContentLength' => '{LENGTH_OF_THE_MEDIA_IN_BYTES}',
        ];
    }

    /**
     * Return definition for post action via S3 triggers.
     *
     * @return array
     */
    public function getDefinition() : array
    {
        return [
            's3_path' => '{PATH_TO_UPLOADED_TMP_FILE}',
        ];
    }

}