<?php

namespace AttractCores\LaravelCoreMedia\Http\Requests;

use AttractCores\LaravelCoreClasses\CoreFormRequest;
use AttractCores\LaravelCoreMedia\Rules\AwsExists;
use Illuminate\Validation\Rule;

/**
 * Class PreSignedRequest
 *
 * @version 1.0.0
 * @date    06.07.2018
 * @author  Yure Nery <yurenery@gmail.com>
 */
class PreSignedRequest extends CoreFormRequest
{

    /**
     * Actions.
     *
     * @var array
     */
    protected $actions = [
        'source-link' => [
            'methods'    => [ 'GET' ],
            'permission' => 'default',
        ],
        'pre-sign'    => [
            'methods'    => [ 'POST' ],
            'permission' => 'default',
        ],
    ];

    /**
     * Validate get action.
     *
     * @return array
     */
    public function getAction()
    {
        return [
            's3_path' => [ 'required', new AwsExists() ],
        ];
    }

    /**
     * @return array
     */
    public function postAction()
    {
        return $this->rulesArray();
    }

    /**
     * Rules array.
     *
     * @return array
     */
    public function rulesArray()
    {
        $restrictedMimeTypes = config('kit-media.restrictions.mimetypes');
        $escapedTmp = preg_quote(config('kit-media.tmp_path'), '/');
        $pattern = '/^' . $escapedTmp . '.+$/i';

        return [
            'Key'           => [ 'required', 'string', "regex:{$pattern}" ], // Path on bucket
            'ContentType'   => [
                'required', 'string',
                ! empty($restrictedMimeTypes) ? Rule::in($restrictedMimeTypes) : '',
            ],
            'ContentLength' => [
                'required', 'integer', 'max:' . ( config('kit-media.restrictions.max_size') * 1000 ),
            ],// IN BYTES
        ];
    }

    /**
     * Messages translations.
     *
     * @return array
     */
    public function messagesArray()
    {
        return [
            's3_path.required'       => __('Please, provide file path on AWS sources bucket.'),
            'Key.required'           => __('Please, provide file name to upload to AWS.'),
            'Key.regex'              => __('File name should contain tmp path prefix ":prefix".',
                [ 'prefix' => config('kit-media.tmp_path') ]),
            'ContentType.required'   => __('Please, provide file content type, that you want to upload to AWS.'),
            'ContentType.in'         => __('You can request upload of file with this mime types only: :mimes.', [
                'mimes' => implode(', ', config('kit-media.restrictions.mimetypes', [])),
            ]),
            'ContentLength.required' => __('Please, provide file content length in bytes, that you want to upload to AWS.'),
            'ContentLength.max'      => __('You can request upload of file with maximum :max Kb length.',
                [ 'max' => config('kit-media.restrictions.max_size') ]),
        ];
    }

}