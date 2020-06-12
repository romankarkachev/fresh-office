<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'name' => 'Fresh office',
    'language' => 'ru-RU',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'components' => [
        'user' => [
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
            'loginUrl' => ['/login'],
        ],
        'request' => [
            'csrfParam' => '_csrf-backend',
            'baseUrl' => '',
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
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
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@dektrium/user/views' => '@backend/views/user',
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'default/index',
                '<action:phpinfo>' => 'default/<action>',
                '<action:login|logout>' => '/user/security/<action>',
                '/users' => '/user/admin/index',
                '<controller:users>/<action:create|update|delete|update-profile|info|trusted|confirm|block|assignments|create-trusted-on-the-fly|delete-trusted-on-the-fly>' => '/user/admin/<action>',
                '<action:profile|account>' => '/user/settings/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
        'yandexCloud' => [
            'class' => 'chemezov\yii2\yandex\cloud\Service',
            'credentials' => [
                'key' => \common\models\YandexServices::SPEECHKIT_SERVICE_ACCOUNT_ID,
                'secret' => \common\models\YandexServices::SPEECHKIT_SERVICE_ACCOUNT_KEY,
            ],
            'defaultBucket' => '1nok',
        ],
    ],
    'params' => $params,
];
