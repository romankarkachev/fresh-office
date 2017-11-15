<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'admins' => ['root', 'administrator'],
            'enableRegistration' => false,
            'enableConfirmation' => false,
            'enablePasswordRecovery' => false,
            'enableFlashMessages' => false,
            'modelMap' => [
                'User' => 'common\models\User',
                'UserSearch' => 'common\models\UserSearch',
                'Profile' => 'common\models\Profile',
            ],
            'controllerMap' => [
                'admin' => 'backend\controllers\UsersController',
                //'settings' => 'backend\controllers\SettingsController',
                'security' => [
                    'class' => 'backend\controllers\SecurityController',
                    'on ' . backend\controllers\SecurityController::EVENT_AFTER_LOGIN => function ($e) {
                        if (Yii::$app->user->can('operator')) {
                            // пользователя с правами оператора сразу переводим на страницу добавления нового обращения
                            Yii::$app->response->redirect(['/appeals/create'])->send();
                            Yii::$app->end();
                        }
                        if (Yii::$app->user->can('logist')) {
                            // пользователя с правами логиста сразу переводим на страницу проектов
                            Yii::$app->response->redirect(['/projects'])->send();
                            Yii::$app->end();
                        }
                        if (Yii::$app->user->can('sales_department_manager')) {
                            // пользователя с правами логиста сразу переводим на страницу проектов
                            Yii::$app->response->redirect(['/transport-requests'])->send();
                            Yii::$app->end();
                        }
                    }
                ],
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
        'gii' => [
            'class' => \yii\gii\Module::className(),
            //'allowedIPs' => ['*'],
            'allowedIPs' => ['127.0.0.1', '185.154.73.*'],
        ]
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];
