<?php

namespace mgcode\auth\helpers;

use Yii;
use mgcode\commandLogger\LoggingTrait;
use yii\base\InvalidParamException;
use yii\rbac\DbManager;
use yii\base\InvalidConfigException;

/**
 * Trait that makes adding new right items easier
 */
trait MigrationHelperTrait
{
    use LoggingTrait;

    public $authManagerName = 'authManager';

    /**
     * @throws InvalidConfigException
     * @return DbManager
     */
    protected function getAuthManager()
    {
        $authManager = Yii::$app->get($this->authManagerName);
        if (!$authManager instanceof DbManager) {
            throw new \yii\base\InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }
        return $authManager;
    }

    protected function addPermission($name)
    {
        $authManager = $this->getAuthManager();
        $item = $authManager->getPermission($name);
        if (!$item) {
            $item = $authManager->createPermission($name);
            $authManager->add($item);
            $this->msg('Added new permission {name}', ['name' => $name]);
        }
    }

    protected function renameItem($oldName, $newName)
    {
        $authManager = $this->getAuthManager();
        $item = $authManager->getPermission($oldName);
        if (!$item) {
            throw new InvalidParamException('item not found');
        }

        $item->name = $newName;
        $authManager->update($oldName, $item);
        $this->msg('Renamed permission {oldName} to {newName}', ['oldName' => $oldName, 'newName' => $newName]);
    }

    protected function addItemToItem($child, $parent)
    {
        $authManager = $this->getAuthManager();

        $childItem = $authManager->getPermission($child);
        if (!$childItem) {
            throw new InvalidParamException('child item not found');
        }

        $parentItem = $authManager->getPermission($parent);
        if (!$parentItem) {
            throw new InvalidParamException('parent item not found');
        }

        $children = $authManager->getChildren($parent);
        if (!array_key_exists($child, $children)) {
            $authManager->addChild($parentItem, $childItem);
            $this->msg('Added {child} to {parent}', ['child' => $child, 'parent' => $parent]);
        }
    }

    protected function addRole($name)
    {
        $authManager = $this->getAuthManager();
        $role = $authManager->getRole($name);
        if (!$role) {
            $role = $authManager->createRole($name);
            $authManager->add($role);
            $this->msg('Added new role {name}', ['name' => $name]);
        }
    }

    protected function addItemToRole($child, $parent)
    {
        $authManager = $this->getAuthManager();

        $item = $authManager->getPermission($child);
        if (!$item) {
            throw new InvalidParamException('item not found');
        }

        $role = $authManager->getRole($parent);
        if (!$role) {
            throw new InvalidParamException('role not found');
        }

        $children = $authManager->getChildren($parent);
        if (!array_key_exists($child, $children)) {
            $authManager->addChild($role, $item);
            $this->msg('Added {child} to {parent}', ['child' => $child, 'parent' => $parent]);
        }
    }

    protected function addRoleToRole($child, $parent)
    {
        $authManager = $this->getAuthManager();

        $childRole = $authManager->getRole($child);
        if (!$childRole) {
            throw new InvalidParamException('item not found');
        }

        $role = $authManager->getRole($parent);
        if (!$role) {
            throw new InvalidParamException('role not found');
        }

        $children = $authManager->getChildren($parent);
        if (!array_key_exists($child, $children)) {
            $authManager->addChild($role, $childRole);
            $this->msg('Added {child} to {parent}', ['child' => $child, 'parent' => $parent]);
        }
    }
}