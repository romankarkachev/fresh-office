<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-cemail',
    'language' => 'ru-RU',
    'name' => 'Корпоративная почта',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'cemail\controllers',
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'enableRegistration' => false,
            'enableConfirmation' => false,
            'enablePasswordRecovery' => false,
            'enableFlashMessages' => false,
            'modelMap' => [
                'LoginForm' => '\cemail\models\LoginForm',
            ],
            'controllerMap' => [
                'security' => 'customer\controllers\SecurityController',
            ],
            'mailer' => [
                'class' => 'common\components\Mailer',
                'viewPath' => '@common/mail',
            ],
        ],
    ],
    'components' => [
        'user' => [
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-cemail', 'httpOnly' => true],
            'loginUrl' => ['/login'],
        ],
        'request' => [
            'csrfParam' => '_csrf-cemail',
            'baseUrl' => '/cemail',
        ],
        'session' => [
            // this is the name of the session cookie used for login on the cemail
            'name' => 'advanced-cemail',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'default/error',
        ],
        // запрещаем bootstrap на корню, у нас свой (из темы coreui)
        'assetManager' => [
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => [
                    'sourcePath' => null, 'css' => []
                ],
                'yii\bootstrap\BootstrapThemeAsset' => [
                    'sourcePath' => null, 'css' => []
                ],
            ],
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@dektrium/user/views' => '@cemail/views/user',
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'default/index',
                '<action:calc-mailboxes-messages-count|primary-fetching-messages-headers|fetch-new-messages-headers|obtain-incomplete-messages>' => 'default/<action>',
                '<action:login|logout>' => '/user/security/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];
