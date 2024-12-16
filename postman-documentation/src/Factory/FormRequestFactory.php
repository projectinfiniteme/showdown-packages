<?php

namespace AttractCores\PostmanDocumentation\Factory;

use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

/**
 * Class FormRequestFactory
 *
 * @package AttractCores\PostmanDocumentation
 * Date: 01.12.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
abstract class FormRequestFactory
{

    /**
     * Faker generator for the factory.
     *
     * @var \Faker\Generator
     */
    protected Generator $faker;

    /**
     * The name of the factory's corresponding form request.
     *
     * @var string|null
     */
    protected ?string $request = NULL;

    /**
     * FormRequestFactory constructor.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() : array
    {
        return [];
    }

    /**
     * Return definition by given method.
     *
     * @param string $method
     * @param string $aliasName
     *
     * @return array
     */
    public function getDefinitionByMethod(string $method, string $aliasName)
    {
        $method = Str::lower($method);

        if(method_exists($this, $call = $method . 'Definition')){
            return $this->$call($aliasName);
        }elseif($method != 'get'){
            return $this->definition();
        }

        return [];
    }

    /**
     * Determine that current factory corresponding given class or route name.
     *
     * @param string|NULL $name
     *
     * @return bool
     */
    public function correspondingFor(?string $name)
    {
        return $this->request == $name;
    }

    /**
     * Get a new Faker instance.
     *
     * @return \Faker\Generator
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }

}
