<?php

namespace AttractCores\PostmanDocumentation\Macros;

use AttractCores\PostmanDocumentation\Facade\Markdown;
use AttractCores\PostmanDocumentation\MarkdownDocs;
use AttractCores\PostmanDocumentation\Postman;
use Illuminate\Support\Arr;

/**
 * Trait RouteCallbacks
 *
 * @package AttractCores\PostmanDocumentation\Macros
 * Date: 10.01.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait RouteCallbacks
{

    /**
     * Return callback for ->aliasedName('Some name') fns.
     *
     * @return \Closure
     */
    public static function aliasedNameCallback() : \Closure
    {
        return function (string $alias) {
            $this->action[ 'aliasAs' ] = $alias;

            return $this;
        };
    }

    /**
     * Return callback for ->getAliasedName() fns
     *
     * @return \Closure
     */
    public static function getAliasedNameCallback() : \Closure
    {
        return function () {
            return $this->action[ 'aliasAs' ] ?? NULL;
        };
    }

    /**
     * Return callback for ->description(Markdown::line('Some paragraph text.')) fns.
     *
     * @return \Closure
     */
    public static function descriptionCallback() : \Closure
    {
        return function (MarkdownDocs $docs) {
            $this->action[ 'docDescription' ] = $docs->toString();

            return $this;
        };
    }

    /**
     * Return callback for ->docPattern('scopes|expands|description') fns.
     *
     * @return \Closure
     */
    public static function docPatternCallback() : \Closure
    {
        return function (string $pattern) {
            $this->action[ 'docPattern' ] = $pattern;

            return $this;
        };
    }

    /**
     * Return callback for ->compileDocs() fns.
     *
     * @return \Closure
     */
    public static function compileDocsCallback() : \Closure
    {
        $static = get_called_class();

        return function () use ($static) {
            $markdownDocs = Markdown::new();
            $pattern = Arr::get($this->action, 'docPattern', 'description|expands|scopes');

            foreach ( explode('|', $pattern) as $pattern ) {
                switch ( $pattern ) {
                    case 'description':
                        $static::compileDescriptionMarkdown($markdownDocs, $this->action);
                        break;
                    case 'expands':
                        $static::compileModelBasedDocs(
                            $markdownDocs, $this->action, 'docExpands',
                            'extraFields', 'Possible Expands'
                        );
                        break;
                    case 'scopes':
                        $static::compileModelBasedDocs(
                            $markdownDocs, $this->action, 'docScopes',
                            'extraScopes', 'Possible Scopes'
                        );
                        break;
                };
            }

            return $markdownDocs->toString();
        };
    }

    /**
     * Return callback for ->structureDepth(3) fns
     *
     * @return \Closure
     */
    public static function structureDepthCallback() :\Closure
    {
        return function (int $depth) {
            $this->action[ 'docStructureDepth' ] = $depth;

            return $this;
        };
    }

    /**
     * Return callback for ->getStructureDepth() fns
     *
     * @return \Closure
     */
    public static function getStructureDepthCallback() :\Closure
    {
        return function () {
            return $this->action[ 'docStructureDepth' ] ?? NULL;
        };
    }

    /**
     * Return callback for ->expands(User::class, ['roles' => 'Some description if needed.']) fns.
     *
     * @return \Closure
     */
    public static function expandsCallback() :\Closure
    {
        return static::getDefaultDocModelSettingsClosure('docExpands', 'postmanExpandsDocumentation');
    }

    /**
     * Return callback for ->scopes(User::class, ['byRoles' => 'Some description if needed.']) fns
     *
     * @return \Closure
     */
    public static function scopesCallback() :\Closure
    {
        return static::getDefaultDocModelSettingsClosure('docScopes', 'postmanScopesDocumentation');
    }

    /**
     * Return default model setting closure.
     *
     * @param string $actionKey
     * @param string $modelHookName
     *
     * @return \Closure
     */
    public static function getDefaultDocModelSettingsClosure(string $actionKey, string $modelHookName) : \Closure
    {
        // Note that $this reference inside of callback will be pointed to Illuminate\\Routing\\Route class.
        return function (?string $modelClass = NULL, array $description = []) use ($actionKey, $modelHookName) {
            $this->action[ $actionKey ] = [
                'model'       => $modelClass,
                'description' => ! empty($description) || ! $modelClass ?
                    $description : Postman::callModelHook($modelClass, $modelHookName, []),
            ];

            return $this;
        };
    }

    /**
     * Compile into markdown instance description added to route.
     *
     * @param MarkdownDocs $interface
     * @param array                     $routeActionData
     */
    public static function compileDescriptionMarkdown(MarkdownDocs $interface, array $routeActionData)
    {
        $interface->raw(Arr::get($routeActionData, 'docDescription', ''));
    }

    /**
     * Compile model based docs.
     *
     * @param MarkdownDocs $interface
     * @param array                     $routeActionData
     * @param string                    $routeKey
     * @param string                    $modelMethod
     * @param string                    $markdownHeading
     */
    public static function compileModelBasedDocs(MarkdownDocs $interface, array $routeActionData, string $routeKey, string $modelMethod, string $markdownHeading)
    {
        if ( $data = Arr::get($routeActionData, $routeKey) ) {
            $model = app($data[ 'model' ]);
            if ( method_exists($model, $modelMethod) ) {
                $modelData = $model->$modelMethod();
                if ( ! empty($modelData) ) {
                    $interface->heading($markdownHeading, 'h2');

                    $result = [];
                    foreach ( $model->$modelMethod() as $expand ) {
                        $description = Arr::get($data, "description.$expand");
                        if ( is_string($description) || ! $description ) {
                            $result[] = sprintf("**%s**%s", $expand,
                                $description ? " - *${description}*" : '');
                        } elseif ( is_array($description) ) {
                            $result[ "**$expand**" ] = $description;
                        }
                    }

                    $interface->unorderedList($result);
                }
            }
        }
    }

}