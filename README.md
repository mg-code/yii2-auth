Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'components' => [
        ....
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => ['public', 'authenticated'],
        ]
    ],
    'as access' => [
        'class' => 'mgcode\auth\components\AccessControl',
        'app' => 'backend',
        'allowActions' => [
            'site/error'
        ],
        'disallowActions' => [
            'disabled/action',
        ]
    ],
];
```
See [Yii RBAC](http://www.yiiframework.com/doc-2.0/guide-security-authorization.html#role-based-access-control-rbac) for more detail.