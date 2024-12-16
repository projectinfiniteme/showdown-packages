<?php

namespace AttractCores\LaravelCoreMedia\Database\Factories;

use AttractCores\LaravelCoreMedia\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class MediaFactory
 *
 * @package AttractCores\LaravelCoreMedia\Database\Factories
 * Date: 11.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class MediaFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = md5('tests/resources/default.png') . '.png';

        return [
            'user_id'       => NULL,
            'original_name' => 'default.png',
            'name'          => $name,
            'ext'           => 'png',
            'path'          => config('kit-media.tmp-path') . $name,
            'disk'          => 'raw',
            'is_mocked'     => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }

}