<?php

namespace mgcode\auth\helpers;

use Yii;
use mgcode\commandLogger\LoggingTrait;
use yii\base\InvalidParamException;

/**
 * Trait that makes adding new right items easier
 */
trait MigrationHelperTrait {
    use LoggingTrait;

    protected function addPermission($name)
    {
        $authManager = Yii::$app->authManager;
        $item = $authManager->getPermission($name);
        if(!$item) {
            $item = $authManager->createPermission($name);
            $authManager->add($item);
            $this->msg('Added new permission {name}', ['name' => $name]);
        }
    }

    protected function addItemToRole($child, $parent)
    {
        $authManager = Yii::$app->authManager;

        $item = $authManager->getPermission($child);
        if(!$item) {
            throw new InvalidParamException('item not found');
        }

        $role = $authManager->getRole($parent);
        if(!$role) {
            throw new InvalidParamException('role not found');
        }

        $children = $authManager->getChildren($parent);
        if(!array_key_exists($child, $children)) {
            $authManager->addChild($role, $item);
            $this->msg('Added {child} to {parent}', ['child' => $child, 'parent' => $parent]);
        }
    }
}