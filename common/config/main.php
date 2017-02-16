<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'admins' => ['administrator'],
            'enableRegistration' => false,
            'enableConfirmation' => false,
            'enablePasswordRecovery' => false,
            'enableFlashMessages' => false,
//            'modelMap' => [
//                'User' => 'common\models\User',
//                'UserSearch' => 'common\models\UserSearch',
//                'Profile' => 'common\models\Profile',
//            ],
            'controllerMap' => [
                //'admin' => 'backend\controllers\UsersController',
                //'settings' => 'backend\controllers\SettingsController',
                'security' => 'backend\controllers\SecurityController',
            ],
        ],
        'rbac' => 'dektrium\rbac\RbacWebModule',
        'datecontrol' =>  [
            'class' => 'kartik\datecontrol\Module',
            'displayTimezone' => 'Europe/Moscow',
            'saveTimezone' => 'Europe/Moscow',
            'autoWidget' => true,
            'ajaxConversion' => true,
        ],
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];
