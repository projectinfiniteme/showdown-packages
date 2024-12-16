<?php

namespace AttractCores\LaravelCoreMedia\Http\Requests;

use AttractCores\LaravelCoreClasses\CoreFormRequest;
use AttractCores\LaravelCoreMedia\Rules\AwsExists;

/**
 * Class UserLoginRequest
 *
 * @package App\Http\Requests
 */
class MediaRequest extends CoreFormRequest
{

    /**
     * Possible actions
     *
     * @var array
     */
    protected $actions = [
        's3' => [
            'methods'    => [ 'POST' ],
            'route'      => '*s3/upload',
            'permission' => 'default',
        ],
        'post'    => [
            'methods'    => [ 'POST' ],
            'permission' => 'default',
        ],
    ];

    /**
     * Post action rules
     *
     * @return array
     */
    public function postAction()
    {
        return $this->rulesArray();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rulesArray()
    {
        $restrictedMimeTypes = config('kit-media.restrictions.mimetypes');

        return [
            'file' => [
                'required', 'file', 'max:' . config('kit-media.restrictions.max_size'),
                ! empty($restrictedMimeTypes) ? 'mimetypes:' . implode(',', $restrictedMimeTypes) : '',
            ],
        ];
    }

    /**
     * Post action rules
     *
     * @return array
     */
    public function postS3Action()
    {
        return [
            'original_name' => [ 'nullable', 'sometimes', 'string' ],
            'path'          => [ 'required', 'string', new AwsExists() ],
        ];
    }

    /**
     * Message translations.
     *
     * @return array
     */
    public function messagesArray()
    {
        return [
            'file.required' => __('Please, provide valid multipart request with uploaded file.'),
            'file.file'     => __('Please, provide valid multipart request with uploaded file.'),
            'file.max'     => __('File size can be :max Kb in length.',
                [ 'max' => config('kit-media.restrictions.max_size') ]),
            'path.required' => __('You should provide file name, that was saved on AWS.'),
        ];
    }

}
