<?php

use common\models\AuthItem;

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
                        $roleName = Yii::$app->user->identity->getRoleName();

                        switch ($roleName) {
                            case AuthItem::ROLE_OPERATOR:
                                // пользователя с правами оператора сразу переводим на страницу добавления нового обращения
                                Yii::$app->response->redirect(['/appeals/create'])->send();
                                Yii::$app->end();
                                break;
                            case AuthItem::ROLE_LOGIST:
                                // пользователя с правами логиста сразу переводим на страницу проектов
                                Yii::$app->response->redirect(['/projects'])->send();
                                Yii::$app->end();
                                break;
                            case AuthItem::ROLE_SALES_DEPARTMENT_MANAGER:
                                // пользователя с правами логиста сразу переводим на страницу проектов
                                Yii::$app->response->redirect(['/transport-requests'])->send();
                                Yii::$app->end();
                                break;
                            case (in_array($roleName, AuthItem::ROLES_SET_ECOLOGISTS)):
                                // пользователя с правами эколога сразу переводим на страницу проектов по экологии
                                Yii::$app->response->redirect(\backend\controllers\EcoProjectsController::ROOT_URL_AS_ARRAY)->send();
                                Yii::$app->end();
                                break;
                            case AuthItem::ROLE_ACCOUNTANT_SALARY:
                                // бухгалтера по зарплате сразу перекидываем в платежные ордера по бюджету,
                                // ему больше вообще ничего не доступно
                                Yii::$app->response->redirect(\backend\controllers\PoController::ROOT_URL_AS_ARRAY)->send();
                                Yii::$app->end();
                                break;
                        }

                        unset($roleName);
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
            'allowedIPs' => ['*'],
            //'allowedIPs' => ['127.0.0.1', '193.160.204.148'],
        ]
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];
