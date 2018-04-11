<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-ferryman',
    'language' => 'ru-RU',
    'name' => 'Личный кабинет перевозчика',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'ferryman\controllers',
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'enableRegistration' => true,
            'enableConfirmation' => false,
            'enablePasswordRecovery' => false,
            'enableFlashMessages' => false,
            'modelMap' => [
                'RegistrationForm' => '\ferryman\models\RegistrationForm',
                'LoginForm' => '\ferryman\models\LoginForm',
            ],
            'controllerMap' => [
                'security' => '\ferryman\controllers\SecurityController',
                'registration' => [
                    'class' => \ferryman\controllers\UserRegistrationController::className(),
                    'on ' . \ferryman\controllers\UserRegistrationController::EVENT_AFTER_REGISTER => [
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
        'user' => [
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-ferryman', 'httpOnly' => true],
            'loginUrl' => ['/login'],
        ],
        'request' => [
            'csrfParam' => '_csrf-ferryman',
            'baseUrl' => '/ferryman',
        ],
        'session' => [
            // this is the name of the session cookie used for login on the ferryman
            'name' => 'advanced-ferryman',
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
                    '@dektrium/user/views' => '@ferryman/views/user',
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'default/index',
                '<action:login>' => '/user/security/login',
                '<action:logout>' => '/user/security/logout',
                '<action:profile>' => 'user/settings/<action>',
                '<action:account>' => 'user/settings/<action>',
                '/register' => '/user/registration/register',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>'
            ],
        ],
        // для возможности формирования ссылок на уже готовые инструменты в backend
        'backendUrlManager' => [
            'class' => 'yii\web\urlManager',
            'baseUrl' => '',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
    'params' => $params,
];
