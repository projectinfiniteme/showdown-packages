<?php

namespace Amondar\SecurityRequest;

use Illuminate\Support\Str;

/**
 * Trait SecurityRequest
 *
 * @version 1.0.0
 * @date 19.01.17
 * @author Yure Nery <yurenery@gmail.com>
 */
trait SecurityRequest
{

    /**
     * Determine actions check.
     *
     * @return array
     */
    protected $actions = [ ];
    protected $action;
    protected $actionName;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->detectAction();
    }

    /**
     * Apply method determine to rules.
     *
     * @return array
     */
    public function rules()
    {
        if (method_exists($this, $method = $this->getActionMethodName())) {
            return $this->$method();
        }

        return [ ];
    }

    /**
     * Detect action
     *
     * @param bool $checkAbilities
     *
     * @return bool
     */
    public function detectAction($checkAbilities = true)
    {
        $method = $this->method();
        $auth = detectUser();

        foreach ( $this->actions as $action => $object ) {
            if (
                $object &&
                in_array($method, $object[ 'methods' ]) &&
                (
                    ! $checkAbilities || (
                        ( isset($object[ 'route' ]) && $this->is($object[ 'route' ]) &&
                          $this->checkDefaultPermission($auth->user, $object) ) ||
                        ( ! isset($object[ 'route' ]) && $this->checkDefaultPermission($auth->user, $object) )
                    )
                )
            ) {
                $this->actionName = $action;
                $this->action = $object;

                return true;
            }
        }

        return false;
    }

    /**
     * Check default permission.
     *
     * @param $user
     * @param $action
     *
     * @return bool
     */
    protected function checkDefaultPermission($user, $action)
    {
        try{
            //try to get permission array.
            $permission = $action['permission'];
        }catch(\Throwable $e){
            //catch if this is only string in second parameter for "else" side of if.
            $permission = $action;
        }

        if (is_array($permission)) {
            $isAndCompare = isset($action['permission_compare']) && mb_strtoupper($action['permission_compare']) == 'AND';
            foreach ($permission as $perm) {
                $permExists = $this->checkDefaultPermission($user, $perm);

                if($isAndCompare && !$permExists){
                    //Compare permissions by AND. Example ['perm1', 'perm2']. false if one of it empty on user.
                    return false;
                }elseif(!$isAndCompare && $permExists){
                    //Compare permissions by OR. Example ['perm1', 'perm2']. True if one of it exists on user.
                    return true;
                }
            }

            if($isAndCompare || empty($permission)){
                return true;
            }else{
                return false;
            }
        } else {
            return $permission == 'default' || ($user && $user->can($permission));
        }
    }


    /**
     * Return valid action method name.
     *
     * @return string
     */
    protected function getActionMethodName()
    {
        if ( ! empty($this->action)) {
            $method = strtolower($this->getMethodType($this->action['methods'][0]));
            if (isset($this->action['route'])) {
                return $this->camelizeAction($method . $this->actionName);
            }

            return $this->camelizeAction($method);
        }
    }

    /**
     * Return valid action method name.
     *
     * @return string
     */
    protected function getMessagesMethodName()
    {
        if ($method = $this->getActionMethodName()) {
            return $method . 'Messages';
        }
    }

    /**
     * Get camelized action name.
     *
     * @param $method
     *
     * @return string
     */
    protected function camelizeAction($method)
    {
        return Str::camel($method . 'Action');
    }

    /**
     * Sanitize request method name.
     *
     * @param $method
     *
     * @return string
     */
    protected function getMethodType($method)
    {
        if ($method == 'PUT' || $method == 'PATCH') {
            $method = 'PUT';
        }

        return $method;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        if (method_exists($this, $method = $this->getMessagesMethodName())) {
            return $this->$method();
        } elseif (method_exists($this, 'messagesArray')) {
            return $this->messagesArray();
        }

        return [ ];
    }
}