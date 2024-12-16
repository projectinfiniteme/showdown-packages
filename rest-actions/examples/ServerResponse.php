<?php

namespace Amondar\Examples;

use Auth;
use Gate;
use User;
use Illuminate\Http\Response;

/**
 * Class ServerResponse
 *
 * @version 2.0.0
 * @date 18.08.16
 * @author Yure Nery <yurenery@gmail.com>
 */
class ServerResponse extends Response
{

    /**
     * @var null|User
     */
    public $user = null;

    /**
     * @var bool
     */
    public $signedIn = false;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $content;


    /**
     * ServerResponse constructor.
     *
     * @param string $content
     * @param int    $code
     * @param array  $headers
     */
    public function __construct($content = '', $code = 200, $headers = [ ])
    {
        parent::__construct($content, $code, $headers);
        $this->original = $this->getDefaultResponse(true, $code);
        $this->content = $this->original;

        return $this;
    }

    /**
     * Extend server response.
     *
     * @param array $extends
     *
     * @return $this
     */
    public function extend($extends = [ ])
    {
        $this->content = $this->content->merge($extends);

        return $this;
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
        $this->content['data'] = $data;

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
        $this->content['errors'] = $errors;

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
        $this->content['code'] = $code;
        $this->content['status'] = $this->content['success'] = $code == 200 ? true : false;

        return $this;
    }

    /**
     * Set redirect path of response.
     *
     * @param null $path
     *
     * @return $this
     */
    public function redirect($path = null)
    {
        if ( ! is_null($path)) {
            $this->content['redirect'] = $path;
        }

        return $this;
    }

    /**
     * Return default actions array.
     *
     * @param $actions
     *
     * @return array
     */
    protected function getActionsArray($actions)
    {
        $actionsRes = [ ];
        foreach ($actions as $action) {
            $actionsRes[ $action ] = true;
        }

        return $actionsRes;
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
     * Return content as collection
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        return $this->content;
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
        $this->detectUser();

        return collect([
            'code'        => $code,
            'success'     => $status,
            'status'      => $status,
            'user'        => $this->user->toArray(),
            'api_token'   => $this->signedIn ? $this->user->api_token : null,
            'data'        => [ ],
            'errors'      => [ ],
            'redirect'    => false,
        ]);
    }

    /**
     * Detect user instance.
     *
     * @return $this
     */
    public function detectUser()
    {
        $this->signedIn = Auth::check();
        if ( ! $this->signedIn) {
            $this->signedIn = Auth::guard('api')->check();
        }
        if ($this->signedIn) {
            $this->user = Auth::user();
            if ( ! $this->user) {
                $this->user = Auth::guard('api')->user();
            }
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
     * @return string The Response as an HTTP string
     *
     * @see prepare()
     */
    public function __toString()
    {
        $this->set();

        return parent::__toString();
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