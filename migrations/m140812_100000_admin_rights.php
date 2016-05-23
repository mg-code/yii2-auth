<?php

use yii\db\Schema;
use yii\db\Migration;
use yii\rbac\DbManager;

class m140812_100000_admin_rights extends Migration
{
    public $authManagerName = 'authManager';

    /**
     * @throws yii\base\InvalidConfigException
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

    public function up()
    {
        $authManager = $this->getAuthManager();

        $item = $authManager->getPermission('/*');
        if(!$item) {
            $item = $authManager->createPermission('/*');
            $authManager->add($item);
        }

        $children = $authManager->getChildren('admin');
        if(!array_key_exists('/*', $children)) {
            $admin = $authManager->getRole('admin');
            $authManager->addChild($admin, $item);
        }
    }

    public function down()
    {
        echo "m140812_100000_admin_rights cannot be reverted.\n";

        return false;
    }
}
