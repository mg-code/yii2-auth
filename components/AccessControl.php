<?php

namespace mgcode\auth\components;

use yii\web\ForbiddenHttpException;
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
 *     'app' => 'backend',
 *     'allowActions' => ['site/login', 'site/error'],
 *     'disallowActions' => ['disabled/action']
 * ]
 * ~~~
 * @author Maris Graudins <maris@mg-interactive.lv>
 */
class AccessControl extends \yii\base\ActionFilter
{
    /**
     * @var null|string By default no application prefix used.
     */
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
        $this->initAllowActions();
    }

    /**
     * @inheritdoc
     * @return bool|void
     */
    public function beforeAction($action)
    {
        $user = Yii::$app->user;

        // Check for allowedAction method and match. This works only for checking acces of current action.
        if ($action->controller->hasMethod('allowAction') && in_array($action->id, $action->controller->allowAction())) {
            return false;
        }

        $permissionName = $action->getUniqueId();
        if ($this->app) {
            $permissionName = $this->app.'/'.$permissionName;
        }
        $params = Yii::$app->request->get();
        if (static::can($permissionName, $params)) {
            return true;
        }
        $this->denyAccess($user);
    }

    /**
     * Checks if current user has access to url route.
     * @param string $permissionName
     * @param array $params
     * @return bool
     */
    public static function can($permissionName, $params = [])
    {
        $user = Yii::$app->user;
        $access = Yii::$app->getBehavior('access');
        if (!($access instanceof AccessControl)) {
            $access = null;
        }

        if (substr($permissionName, 0, 1) !== '/') {
            $permissionName = '/'.$permissionName;
        }

        // Check disallowed actions
        if ($access && static::matchActions($permissionName, $access->disallowActions, $access->app)) {
            return false;
        }

        // Check allowed actions
        if ($access && static::matchActions($permissionName, $access->allowActions, $access->app)) {
            return true;
        }

        // Check permission of action
        if ($user->can($permissionName, $params)) {
            return true;
        }

        // Check permission of parents
        do {
            $permissionName = rtrim($permissionName, '/*');
            $explode = explode('/', $permissionName);
            array_pop($explode);
            $permissionName = implode('/', $explode).'/*';

            if ($user->can($permissionName, $params)) {
                return true;
            }
        } while ($permissionName != '/*');

        return false;
    }

    /**
     * Match action against action list
     * @param string $action
     * @param array $actions
     * @param string| null $app
     * @return bool
     */
    protected static function matchActions($action, array $actions, $app)
    {
        if ($app) {
            $action = substr($action, strlen('/'.$app));
        }

        foreach ($actions as $route) {
            if (substr($route, 0, 1) !== '/') {
                $route = '/'.$route;
            }
            if (substr($route, -1) === '*') {
                $route = rtrim($route, "*");
                if ($route === '/' || strpos($action, $route) === 0) {
                    return true;
                }
            } else if ($action === $route) {
                return true;
            }
        }

        return false;
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
     * Initializes default allowed actions
     */
    protected function initAllowActions()
    {
        // Error action is allowed
        $errorAction = Yii::$app->getErrorHandler()->errorAction;
        if ($errorAction === null) {
            $errorAction = 'site/error';
        }
        $this->allowActions[] = $errorAction;

        // Login action is allowed
        $loginUrl = Yii::$app->user->loginUrl;
        if (is_array($loginUrl) && isset($loginUrl[0])) {
            $this->allowActions[] = $loginUrl[0];
        }
    }
}
