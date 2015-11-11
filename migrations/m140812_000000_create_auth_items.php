<?php

use yii\db\Schema;
use yii\db\Migration;

class m140812_000000_create_auth_items extends Migration
{
    public function up()
    {
        $authManager = Yii::$app->getAuthManager();

        $item = $authManager->getRole('admin');
        if(!$item) {
            $this->createAdmin();
        }

        $item = $authManager->getRole('public');
        if(!$item) {
            $this->createPublic();
        }

        $item = $authManager->getRole('authenticated');
        if(!$item) {
            $this->createAuthenticated();
        }
    }

    protected function createAdmin()
    {
        $authManager = Yii::$app->getAuthManager();

        $role = $authManager->createRole('admin');
        $authManager->add($role);
    }


    protected function createPublic()
    {
        $authManager = Yii::$app->getAuthManager();

        $role = $authManager->createRole('public');
        $authManager->add($role);
    }

    protected function createAuthenticated()
    {
        $authManager = Yii::$app->getAuthManager();

        $rule = new \mgcode\auth\rules\AuthenticatedRule();
        $authManager->add($rule);

        $role = $authManager->createRole('authenticated');
        $role->ruleName = $rule->name;

        $authManager->add($role);
    }

    public function down()
    {
        echo "m150828_095206_create_auth_items cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
