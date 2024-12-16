<?php

namespace AttractCores\PostmanDocumentation\Factory;

use AttractCores\PostmanDocumentation\PostmanRoute;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

/**
 * Class FormFactory
 *
 * @package AttractCores\PostmanDocumentation
 * Date: 01.12.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class FormFactory
{

    /**
     * Contains loaded request fakers.
     *
     * @var array
     */
    protected array $loadedFactories = [];


    /**
     * Return data by
     *
     * @param PostmanRoute $route
     * @param string                    $method
     * @param string|null               $formRequestClass
     *
     * @return array
     */
    public function getFormData(PostmanRoute $route, string $method, ?string $formRequestClass)
    {
        /** @var FormRequestFactory $factory */
        foreach ( $this->loadedFactories as $factory ) {
            if (
                $factory->correspondingFor($name = $route->getName()) || $factory->correspondingFor($formRequestClass)
            ) {
                return $factory->getDefinitionByMethod($method, $name);
            }
        }

        return [];
    }

    /**
     * Load fakers through given paths.
     *
     * @param $paths
     *
     * @throws \ReflectionException
     */
    public function load($paths)
    {
        $paths = array_unique(Arr::wrap($paths));

        $paths = array_filter($paths, function ($path) {
            return is_dir($path);
        });

        if ( empty($paths) ) {
            return;
        }

        $namespace = app()->getNamespace();

        foreach ( ( new Finder )->in($paths)->files() as $faker ) {
            $faker = $namespace . str_replace(
                    [ '/', '.php' ],
                    [ '\\', '' ],
                    Str::after($faker->getRealPath(), realpath(app_path()) . DIRECTORY_SEPARATOR)
                );

            if (
                is_subclass_of($faker, FormRequestFactory::class)
                && ! ( new ReflectionClass($faker) )->isAbstract()
            ) {
                $this->loadedFactories[] = app($faker);
            }
        }
    }

}
