<?php

namespace mgcode\auth\rules;

use yii\rbac\Rule;

class AuthenticatedRule extends Rule
{
    public $name = 'isAuthenticated';

    /**
     * @param string|integer $user the user ID.
     * @param \yii\rbac\Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        return $user !== null;
    }
}