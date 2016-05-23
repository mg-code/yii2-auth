<?php

use yii\db\Schema;
use yii\db\Migration;
use yii\rbac\DbManager;

class m140812_000000_create_auth_items extends Migration
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
        $authManager = $this->getAuthManager();

        $role = $authManager->createRole('admin');
        $authManager->add($role);
    }


    protected function createPublic()
    {
        $authManager = $this->getAuthManager();

        $role = $authManager->createRole('public');
        $authManager->add($role);
    }

    protected function createAuthenticated()
    {
        $authManager = $this->getAuthManager();

        $rule = new \mgcode\auth\rules\AuthenticatedRule();
        $authManager->add($rule);

        $role = $authManager->createRole('authenticated');
        $role->ruleName = $rule->name;

        $authManager->add($role);
    }

    public function down()
    {
        echo "m140812_000000_create_auth_items cannot be reverted.\n";

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
