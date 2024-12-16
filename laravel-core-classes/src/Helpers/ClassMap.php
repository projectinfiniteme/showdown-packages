<?php

if ( ! function_exists('getClassesInNamespace') ) {
    /**
     * Return frontend route url.
     *
     * @param $psrNamespace - Namespace for search. NULL if all classes needed.
     *
     * @return array
     */
    function getClassesInNamespace($psrNamespace = NULL) : array
    {
        $store = \Cache::driver();
        $key = $psrNamespace ? md5($psrNamespace) : 'all_classes';

        if ( \Cache::supportsTags() ) {
            $store = \Cache::tags([ 'classes_in_namespace' ]);
        }

        return $store->get($key, function() use ($psrNamespace, $key){
            $namespaces = array_keys(( new \Facade\Ignition\Support\ComposerClassMap() )->listClasses());

            if ( $psrNamespace ) {
                $namespaces =  array_filter($namespaces, function ($n) use ($psrNamespace) {
                    return \Illuminate\Support\Str::startsWith($n, $psrNamespace);
                });
            }

            if(\Cache::supportsTags()){
                Cache::tags(['classes_in_namespace'])->forever($key, $namespaces);
            }else{
                Cache::forever($key, $namespaces);
            }

            return $namespaces;
        });
    }
}

if ( ! function_exists('getClassesThatImplements') ) {
    /**
     * Return frontend route url.
     *
     * @param               $psrInterface - Interface for search
     * @param string|null   $inNamespace  - Namespace for fastest search. Null if all classes search.
     * @param \Closure|null $callback     - Callback function for result customization on each match.
     *
     * @return array
     */
    function getClassesThatImplements($psrInterface, string $inNamespace = NULL, \Closure $callback = NULL) : array
    {
        $store = \Cache::driver();
        $key = md5($psrInterface);

        if ( \Cache::supportsTags() ) {
            $store = \Cache::tags([ 'classes_that_implements' ]);
        }

        return $store->get($key, function() use($key, $psrInterface, $inNamespace, $callback){
            $map = [];


            foreach ( getClassesInNamespace($inNamespace) as $class ) {
                if ( in_array($psrInterface, class_implements($class)) ) {
                    if ( $callback ) {
                        $map = array_merge($map, $callback($class));
                    } else {
                        $map[] = $class;
                    }
                }
            }

            if(\Cache::supportsTags()){
                \Cache::tags(['classes_that_implements'])->forever($key, $map);
            }else{
                \Cache::forever($key, $map);
            }

            return $map;
        });
    }
}
