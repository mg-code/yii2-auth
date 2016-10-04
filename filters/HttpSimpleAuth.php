<?php

namespace mgcode\auth\filters;

use yii\base\InvalidParamException;
use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\Request;

/**
 * HttpSimpleAuth is an action filter that supports authentication via HTTP headers.
 *
 * You may use HttpSimpleAuth by attaching it as a behavior to a controller or module, like the following:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'simpleAuth' => [
 *             'class' => \mgcode\auth\filters\HttpSimpleAuth::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Maris Graudins <maris@mg-interactive.lv>
 */
class HttpSimpleAuth extends AuthMethod
{
    public $userHeader = 'auth-user';
    public $passwordHeader = 'auth-password';
    
    /**
     * @var callable a PHP callable that will authenticate the user.
     * The callable receives a username and a password as its parameters. It should return an identity object
     * that matches the username and password. Null should be returned if there is no such identity.
     * The following code is a typical implementation of this callable:
     * ```php
     * function ($username, $password) {
     *     return \app\models\User::findOne([
     *         'username' => $username,
     *         'password' => $password,
     *     ]);
     * }
     * ```
     * This property is required.
     */
    public $auth;

    public function init()
    {
        parent::init();
        if (!is_callable($this->auth)) {
            throw new InvalidParamException('`auth` property must be callable.');
        }
    }

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $username = $this->getAuthUser($request);
        $password = $this->getAuthPassword($request);

        if ($username !== null || $password !== null) {
            $identity = call_user_func($this->auth, $username, $password);
            if ($identity !== null) {
                $user->switchIdentity($identity);
            } else {
                $this->handleFailure($response);
            }
            return $identity;
        }

        return null;
    }

    /**
     * @param Request $request
     * @return string|null the username sent via HTTP header, null if the username is not given
     */
    protected function getAuthUser($request)
    {
        if($user = $request->headers->get($this->userHeader)) {
            return $user;
        }
        return null;
    }

    /**
     * @param Request $request
     * @return string|null the password sent via HTTP header, null if the password is not given
     */
    protected function getAuthPassword($request)
    {
        if($user = $request->headers->get($this->passwordHeader)) {
            return $user;
        }
        return null;
    }
}
