<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-customer',
    'language' => 'ru-RU',
    'name' => 'Личный кабинет клиента',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'customer\controllers',
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'enableRegistration' => true,
            'enableConfirmation' => false,
            'enablePasswordRecovery' => false,
            'enableFlashMessages' => false,
            'modelMap' => [
                'RegistrationForm' => '\customer\models\RegistrationForm',
                'LoginForm' => '\customer\models\LoginForm',
            ],
            'controllerMap' => [
                'security' => 'customer\controllers\SecurityController',
                'registration' => [
                    'class' => \customer\controllers\UserRegistrationController::className(),
                    'on ' . \customer\controllers\UserRegistrationController::EVENT_AFTER_REGISTER => [
                        'ferryman\events\FerrymanAfterRegister',
                        'handleAfterRegister'
                    ],
                ],
            ],
            'mailer' => [
                'class' => 'common\components\Mailer',
                'viewPath' => '@common/mail',
            ],
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-customer',
            'baseUrl' => '/customer',
        ],
        'user' => [
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-customer', 'httpOnly' => true],
            'loginUrl' => ['/login'],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-customer',
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
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'locale' => 'ru_RU',
            'defaultTimeZone' => 'Europe/Moscow',
            'timeZone' => 'Europe/Moscow',
            'currencyCode' => 'RUR',
            'dateFormat' => 'php:d.m.Y',
            'datetimeFormat' => 'php:d.m.Y H:i:s',
            'timeFormat' => 'php:H:i:s',
            'thousandSeparator' => ' ',
            'decimalSeparator' => ',',
            'numberFormatterOptions' => [
                NumberFormatter::MIN_FRACTION_DIGITS => 0,
                NumberFormatter::MAX_FRACTION_DIGITS => 2,
                NumberFormatter::DECIMAL_ALWAYS_SHOWN => 0,
            ],
            'nullDisplay' => '',
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
                    '@dektrium/user/views' => '@customer/views/user',
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'default/index',
                '<action:driver-instruction>' => 'default/<action>',
                '<action:login|logout>' => '/user/security/<action>',
                '<action:profile|account>' => '/user-settings/<action>',
                '/register' => '/user/registration/register',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>'
            ],
        ],
    ],
    'params' => $params,
];
