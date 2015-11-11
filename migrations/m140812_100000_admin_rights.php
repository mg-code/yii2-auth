<?php

use yii\db\Schema;
use yii\db\Migration;

class m140812_100000_admin_rights extends Migration
{
    public function up()
    {
        $authManager = Yii::$app->authManager;

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
        echo "m140812_114939_rbac_authitem cannot be reverted.\n";

        return false;
    }
}
