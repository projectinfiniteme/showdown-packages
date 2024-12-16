<?php

namespace AttractCores\LaravelCoreMedia\Rules;

use AttractCores\LaravelCoreMedia\MediaStorage;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class AwsExists
 *
 * @version 1.0.0
 * @date    16.09.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
class AwsExists implements Rule
{

    protected $extensions;
    protected $files = [];


    /**
     * Create a new exists rule instance.
     *
     * @param array $extensions
     *
     */
    public function __construct($extensions = [])
    {
        if (is_string($extensions)) {
            $extensions = [ $extensions ];
        }

        $this->extensions = $extensions;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('File does not exists on AWS.');
    }

    /**
     * Skip this files on checks.
     *
     * @param $files
     *
     * @return $this
     */
    public function skip($files)
    {
        if (is_string($files) || is_null($files)) {
            $files = [ $files ];
        }

        $this->files = $this->encodeNames($files);

        return $this;
    }

    /**
     * Encode names with md5.
     *
     * @param array $files
     *
     * @return array
     */
    public function encodeNames(array $files)
    {
        return array_map(function ($file) {
            return is_null($file) ? NULL : md5($file);
        }, $files);
    }

    /**
     * Check the rule.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     * @internal param array $parameters
     *
     */
    public function passes($attribute, $value)
    {
        $skipThis = collect($this->files);
        $extensions = collect($this->extensions);

        if ($this->checkSkipping($value, $skipThis)) {
            return true;
        }

        if ($this->isExists($value)) {
            if ($extensions->isEmpty()) {
                return true;
            }

            $fileExtension = pathinfo($value, PATHINFO_EXTENSION);
            
            foreach ($extensions as $extension) {
                if (Str::contains($extension, '/')) {
                    // If it's a MIME type, we can't check it without getMetadata(), so we'll skip
                    continue;
                } elseif (strtolower($fileExtension) === strtolower($extension)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param            $value
     * @param Collection $skipThis
     *
     * @return bool
     */
    protected function checkSkipping($value, Collection $skipThis)
    {
        return $skipThis->contains($value === NULL ? '' : md5($value));
    }

    /**
     * Check the file existence.
     *
     * @param $value
     *
     * @return bool|array
     */
    protected function isExists($value)
    {
        try {
            $disk = Storage::disk('s3');
            return $disk->exists($value);
        } catch (\Exception $e) {
            Log::error('Error checking file on S3: ' . $e->getMessage());
            return false;
        }
    }
}