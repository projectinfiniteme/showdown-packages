<?php

namespace AttractCores\LaravelCoreClasses\Libraries;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Lcobucci\JWT\Parser;
use stdClass;

/**
 * Class ServerResponse
 *
 * @version 2.0.0
 * @date    18.08.16
 * @author  Yure Nery <yurenery@gmail.com>
 */
class ServerResponse extends Response
{

    /**
     * @var null|User
     */
    public $user = NULL;


    /**
     * @var \Illuminate\Support\Collection
     */
    protected $content;

    /**
     * Current request
     *
     * @var \Illuminate\Http\Request
     */
    protected Request $request;


    /**
     * ServerResponse constructor.
     *
     * @param string $content
     * @param int    $code
     * @param array  $headers
     */
    public function __construct($content = '', $code = 200, $headers = [])
    {
        parent::__construct($content, $code, $headers);
        $this->original = $this->getDefaultResponse(true, $code);
        $this->content = $this->original;
        $this->request = request();

        return $this;
    }

    /**
     * Return default response array.
     *
     * @param $status
     *
     * @param $code
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getDefaultResponse($status, $code = 200)
    {
        return collect([
            'code'     => $code,
            'success'  => $status,
            'status'   => $status,
            'data'     => (object) [],
            'links'    => (object) [],
            'meta'     => (object) [],
            'errors'   => collect([]),
            'redirect' => '',
        ]);
    }

    /**
     * Extend server response.
     *
     * @param array $extends
     *
     * @return $this
     */
    public function extend($extends = [])
    {
        $this->content = $this->mergeRecursively(
            $this->content->toArray(),
            $extends instanceof Arrayable ? $extends->toArray() : (array) $extends
        );

        return $this;
    }

    /**
     * Merge recursively first array with second.
     *
     * @param array $content
     * @param array $extendedContent
     *
     * @return Collection
     */
    public function mergeRecursively(array $content, array $extendedContent)
    {
        $result = [];
        foreach ( $content as $key => $item ) {
            if ( ! is_string($key) ) {
                return array_merge($content, $extendedContent);
            } elseif ( isset($extendedContent[ $key ]) ) {
                if ( is_array($content[ $key ]) && is_array($extendedContent[ $key ]) ) {
                    $result[ $key ] = $this->mergeRecursively((array) $content[ $key ],
                        (array) $extendedContent[ $key ]);
                } else {
                    $result[ $key ] = $extendedContent[ $key ];
                }

                // remove added keys.
                unset($extendedContent[ $key ]);
            } else {
                $result[ $key ] = $item;
            }
        }

        // merge untouched array keys.
        $result = array_merge($result, $extendedContent);

        return collect($result);
    }

    /**
     * Set Data prop in server response.
     *
     * @param array $data
     *
     * @return $this
     */
    public function data(array $data)
    {
        $this->content[ 'data' ] = $data;

        return $this;
    }

    /**
     * Add to response result from resource.
     *
     * @param Responsable|array $resource
     *
     * @return $this
     */
    public function resource($resource)
    {
        if ( $resource instanceof Responsable ) {
            $this->extend(json_decode($resource->toResponse($this->request)->getContent(), true));
        } elseif ( is_array($resource) ) {
            $this->data($resource);
        }

        return $this;
    }

    /**
     * Set Errors prop in server response.
     *
     * @param  $errors
     *
     * @return $this
     */
    public function errors($errors)
    {
        $this->content[ 'errors' ] = $errors;

        return $this;
    }

    /**
     * Set code status of response.
     *
     * @param int $code
     *
     * @return $this
     */
    public function status($code = 200)
    {
        $this->setStatusCode($code);
        $this->content[ 'code' ] = $code;
        $this->content[ 'status' ] = $this->content[ 'success' ] = $this->isSuccessful();

        return $this;
    }

    /**
     * Set redirect path of response.
     *
     * @param null $path
     *
     * @return $this
     */
    public function redirect($path = NULL)
    {
        if ( ! is_null($path) ) {
            $this->content[ 'redirect' ] = $path;
        }

        return $this;
    }

    /**
     * Return content as collection
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        return $this->content;
    }

    /**
     * Add one error to the response.
     *
     * @param       $type
     * @param       $errors
     * @param       $field
     * @param       $values
     * @param null  $file
     * @param null  $line
     * @param array $trace
     *
     * @return $this
     */
    public function pushError($type, $errors, $field, $values, $file = NULL, $line = NULL, $trace = [])
    {
        if ( ! ( $this->content[ 'errors' ] instanceof Collection ) ) {
            $this->content->put('errors', collect($this->content[ 'errors' ]));
        }

        $this->content[ 'errors' ]->push($this->getErrorArrayStructure($type, $errors, $field, $values, $file, $line,
            $trace));

        return $this;
    }

    /**
     * Return error array structure.
     *
     * @param       $type
     * @param       $errors
     * @param       $field
     * @param       $values
     *
     * @param null  $file
     * @param null  $line
     * @param array $trace
     *
     * @return array
     */
    public function getErrorArrayStructure($type, $errors, $field, $values, $file = NULL, $line = NULL, $trace = [])
    {
        // Add flexibility for unit tests.
        if ( app()->runningUnitTests() ) {
            return [
                'field'  => $field,
                'type'   => $type,
                'values' => is_array($values) ? $values : [ $values ],
                'file'   => $file,
                'line'   => $line,
                'trace'  => array_values(array_reverse($trace)),
                'errors' => is_array($errors) ? $errors : [ $errors ],
            ];
        }

        return [
            'field'  => $field,
            'type'   => $type,
            'errors' => is_array($errors) ? $errors : [ $errors ],
            'values' => is_array($values) ? $values : [ $values ],
            'file'   => $file,
            'line'   => $line,
            'trace'  => $trace,
        ];
    }

    /**
     * Determine current token model from bearer token in request
     *
     * @param Request $request
     *
     * @return \Laravel\Passport\Token|null
     */
    public static function getCurrentToken(Request $request)
    {
        try {
            $tokenRepository = app(\Laravel\Passport\TokenRepository::class);
            /** @var \Lcobucci\JWT\Token $jwt */
            $jwt = app(Parser::class)->parse($request->bearerToken());

            return $tokenRepository->find($jwt->claims()->get('jti'));
        } catch ( \Exception $e ) {
            return NULL;
        }
    }

    /**
     * Return token response.
     *
     * @return $this
     */
    public function extendWithToken()
    {
        if ( config('kit-core-controller.bearer_token_in_response') ) {
            $token = NULL;

            if ( $bearerToken = $this->request->bearerToken() ) {
                $token = $this::getCurrentToken($this->request);
            }

            $this->extend([
                'bearer' => ! $token ? [
                    'token'      => '',
                    'expires_in' => -1,
                ] : [
                    'token'      => $bearerToken,
                    'expires_in' => $token->expires_at->timestamp - time() < 0 ? -1 :
                        $token->expires_at->timestamp - time(),
                ],
            ]);
        }

        return $this;
    }

    /**
     * Extend answer with a user if needed.
     *
     * @return $this
     */
    public function extendWithUser()
    {
        $resource = config('kit-core-controller.user_response_resource');

        if ( config('kit-core-controller.user_in_response') && $resource && class_exists($resource) ) {
            $user = $this->request->user();

            $this->extend([
                'user' => $user ? new $resource($user) : new StdClass(),
            ]);
        }

        return $this;
    }

    /**
     * Returns the Response as an HTTP string.
     *
     * The string representation of the Response is the same as the
     * one that will be sent to the client only if the prepare() method
     * has been called before.
     *
     * @see prepare()
     * @return string The Response as an HTTP string
     *
     */
    public function __toString()
    {
        $this->set();

        return parent::__toString();
    }

    /**
     * Set content after modifications.
     *
     * @return Response
     */
    public function set()
    {
        $this->setContent($this->content);

        return $this;
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return Response
     */
    public function send()
    {
        $this->set();

        return parent::send();
    }

}