<?php
if ( ! function_exists('isBackend')) {
    /**
     * Is current request backend.
     *
     * @param \Illuminate\Http\Request|null $request
     *
     * @return mixed
     */
    function isBackend(\Illuminate\Http\Request $request = null)
    {
        if ( ! $request) {
            $request = request();
        }

        return $request->is('backend/v*');
    }
}

if ( ! function_exists('isApi')) {
    /**
     * Is current request api.
     *
     * @param \Illuminate\Http\Request|null $request
     *
     * @return mixed
     */
    function isApi(\Illuminate\Http\Request $request = null)
    {
        if ( ! $request) {
            $request = request();
        }

        return $request->expectsJson() && $request->is('api/v*');
    }
}

if ( ! function_exists('isFrontend')) {
    /**
     * Is current request frontend.
     *
     * @param \Illuminate\Http\Request|null $request
     *
     * @return mixed
     */
    function isFrontend(\Illuminate\Http\Request $request = null)
    {
        if ( ! $request) {
            $request = request();
        }

        return ! isApi($request);
    }
}

if ( ! function_exists('apiRoute')) {
    /**
     * Return api route url.
     *
     * @param        $name
     * @param array  $parameters
     * @param bool   $absolute
     *
     * @param string $version
     *
     * @return mixed
     */
    function apiRoute($name, $parameters = [], $absolute = true, $version = 'v1')
    {
        return route(sprintf('%s%s', config("kit-routes.api.$version.name"), $name), $parameters, $absolute);
    }
}

if ( ! function_exists('frontendRoute')) {
    /**
     * Return frontend route url.
     *
     * @param        $name
     * @param array  $parameters
     * @param bool   $absolute
     *
     * @param string $version
     *
     * @return mixed
     */
    function frontendRoute($name, $parameters = [], $absolute = true, $version = 'v1')
    {
        return route(sprintf('%s%s', config("kit-routes.frontend.$version.name"), $name), $parameters, $absolute);
    }
}

if ( ! function_exists('backendRoute')) {
    /**
     * Return backend route url.
     *
     * @param        $name
     * @param array  $parameters
     * @param bool   $absolute
     *
     * @param string $version
     *
     * @return mixed
     */
    function backendRoute($name, $parameters = [], $absolute = true, $version = 'v1')
    {
        return route(sprintf('%s%s', config("kit-routes.backend.$version.name"), $name), $parameters, $absolute);
    }
}

if ( ! function_exists('spaRoute')) {
    /**
     * Return spa url.
     *
     * @param        $name
     * @param array  $parameters
     * @param string $version
     * @param bool   $absolute
     *
     * @return mixed
     * @throws Throwable
     */
    function spaRoute($name, $parameters = [], $version = 'frontend', $absolute = true)
    {
        // Grab url generator instance and removable prefix.
        $urlGenerator = app('url');
        $urlPrefix = config("kit-routes.spa.$version.prefix");

        // Detect current domain with http(s).
        $baseUrl = $urlGenerator->getRequest()->getSchemeAndHttpHost();

        // Force redeclare base url.
        app('url')->forceRootUrl(config("kit-routes.spa.$version.base_url"));

        try {
            // Take a route with new base url.
            $route = $urlGenerator->route(sprintf('%s%s', config("kit-routes.spa.$version.name"), $name), $parameters, $absolute);
        } catch(Throwable $e){
            // Revert base url back.
            app('url')->forceRootUrl($baseUrl);

            throw $e;
        }


        // Revert base url back.
        app('url')->forceRootUrl($baseUrl);

        return str_replace([$urlPrefix . '/', $urlPrefix], ['', ''], $route);
    }
}
