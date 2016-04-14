<?php

namespace mgcode\auth\components;

use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;
use yii\base\Module;
use Yii;

/**
 * Access Control Filter (ACF) is a simple authorization method that is best used by applications that only need some simple access control.
 * As its name indicates, ACF is an action filter that can be attached to a controller or a module as a behavior.
 * ACF will check a set of access rules to make sure the current user can access the requested action.
 * To use AccessControl, declare it in the application config as behavior.
 * For example.
 * ~~~
 * 'as access' => [
 *     'class' => 'mgcode\auth\components\AccessControl',
 *     'allowActions' => ['site/login', 'site/error'],
 *     'disallowActions' => ['disabled/action']
 * ]
 * ~~~
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @author Maris Graudins <m.graudins@yahoo.com>
 */
class AccessControl extends \yii\base\ActionFilter
{
    public $app;

    /**
     * @var array List of action that not need to check access.
     */
    public $allowActions = [];


    /**
     * @var array List of action that are disallowed.
     */
    public $disallowActions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!$this->app) {
            throw new InvalidConfigException('Property `app` must be set.');
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
         $user = Yii::$app->user;

        // Check disallowed actions
        if($this->matchActions($action, $this->disallowActions)) {
            $this->denyAccess($user);
            return false;
        }

        $actionId = $action->getUniqueId();

        // Whether has access directly to action
        $permission = "/{$this->app}/$actionId";
        if ($user->can($permission)) {
            return true;
        }

        $params = Yii::$app->request->get();

        // Whether has access to parent object
        $obj = $action->controller;
        do {
            $permission = "/{$this->app}/".ltrim($obj->getUniqueId().'/*', '/');
            if ($user->can($permission, $params)) {
                return true;
            }
            $obj = $obj->module;
        } while ($obj !== null);

        // Whether has access to all actions
        if ($user->can('/*')) {
            return true;
        }

        $this->denyAccess($user);
    }

    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     * @param  \yii\web\User $user the current user
     * @throws \yii\web\ForbiddenHttpException if the user is already logged in.
     */
    protected function denyAccess($user)
    {
        if ($user->getIsGuest() && $user->loginUrl) {
            $user->loginRequired();
            Yii::$app->end();
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    /**
     * @inheritdoc
     */
    protected function isActive($action)
    {
        $uniqueId = $action->getUniqueId();

        // error action route
        $errorAction = Yii::$app->getErrorHandler()->errorAction;
        if($errorAction === null) {
            $errorAction = 'site/error';
        }

        // Error action is allowed
        if ($uniqueId === $errorAction) {
            return false;
        }

        // Match allowed actions
        if($this->matchActions($action, $this->allowActions)) {
            return false;
        }

        // Check for allowedAction method and match
        if ($action->controller->hasMethod('allowAction') && in_array($action->id, $action->controller->allowAction())) {
            return false;
        }

        // Login page is allowed
        $user = Yii::$app->user;
        if ($user->getIsGuest() && is_array($user->loginUrl) && isset($user->loginUrl[0]) && $uniqueId === trim($user->loginUrl[0], '/')) {
            return false;
        }

        return true;
    }

    /**
     * @param \yii\base\Action $action
     * @param array $actions
     * @return bool
     */
    protected function matchActions($action, array $actions)
    {
        $uniqueId = $action->getUniqueId();
        if ($this->owner instanceof Module) {
            // convert action uniqueId into an ID relative to the module
            $mid = $this->owner->getUniqueId();
            $id = $uniqueId;
            if ($mid !== '' && strpos($id, $mid.'/') === 0) {
                $id = substr($id, strlen($mid) + 1);
            }
        } else {
            $id = $action->id;
        }

        foreach ($actions as $route) {
            if (substr($route, -1) === '*') {
                $route = rtrim($route, "*");
                if ($route === '' || strpos($id, $route) === 0) {
                    return true;
                }
            } else {
                if ($id === $route) {
                    return true;
                }
            }
        }

        return false;
    }
}
