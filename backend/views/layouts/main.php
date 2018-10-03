<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use backend\assets\AppAsset;
use common\widgets\Alert;

AppAsset::register($this);

rmrevin\yii\fontawesome\AssetBundle::register($this);

\hiqdev\assets\icheck\iCheckAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?= $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/ico', 'href' => '/favicon.ico']) ?>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php
NavBar::begin([
    'brandLabel' => Yii::$app->name,
    'brandUrl' => '/',
    'options' => ['class' => 'navbar navbar-default navbar-fixed-top'],
    'innerContainerOptions' => ['class' => 'container-fluid'],
]);
$items = [];
// да, вот так реализовано меню. и что теперь?!
if (Yii::$app->user->can('root'))
    $items = [
        // вернуть когда-нибудь может быть:
        //['label' => '<i class="fa fa-file-text-o fa-lg"></i>', 'url' => ['/documents'], 'linkOptions' => ['title' => 'Документы']],
        ['label' => '<i class="fa fa-headphones fa-lg text-primary"></i>', 'url' => ['/appeals'], 'linkOptions' => ['title' => 'Обращения']],
        [
            'label' => 'Справочники',
            'url' => '#',
            'items' => [
                '<li class="dropdown-header">Основные</li>',
                ['label' => '<i class="fa fa-cubes text-info"></i> Организации', 'url' => ['/organizations']],
                ['label' => '<i class="fa fa-user-secret text-info"></i> Источники обращения', 'url' => ['/appeal-sources']],
                ['label' => '<i class="fa fa-cubes text-info"></i> ФККО', 'url' => ['/fkko']],
                ['label' => '<i class="fa fa-recycle text-info"></i> Виды обращения', 'url' => ['/handling-kinds']],
                '<li class="dropdown-header">Вспомогательные</li>',
                ['label' => '<i class="fa fa-remove text-info"></i> Исключения номенклатуры', 'url' => ['/products-excludes']],
                ['label' => '<i class="fa fa-user-circle text-info"></i> Подстановка ответственных', 'url' => ['/responsible-substitutes']],
                ['label' => '<i class="fa fa-user-circle-o text-info"></i> Ответственные для отказа', 'url' => ['/responsible-refusal']],
                ['label' => '<i class="fa fa-user-plus text-info"></i> Ответственные для новых', 'url' => ['/responsible-fornewca']],
                ['label' => 'Ответственные по типам проектов', 'url' => ['/responsible-by-project-types']],
                ['label' => 'Получатели корреспонденции от производства', 'url' => ['/responsible-for-production']],
                ['label' => 'Получатели уведомлений по просроченным проектам', 'url' => ['/notifications-receivers-sncflt']],
                ['label' => 'Получатели оповещений по проектам', 'url' => ['/notifications-receivers-sncbt']],
                ['label' => 'Типы контента загружаемых файлов', 'url' => ['/uploading-files-meanings']],
                '<li class="divider"></li>',
                ['label' => '<i class="fa fa-users text-info"></i> Пользователи', 'url' => ['/users']],
            ],
        ],
        [
            'label' => 'Логистика',
            'url' => '#',
            'items' => [
                ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']],
                ['label' => 'Подбор перевозчиков', 'url' => ['/projects/ferrymen-casting']],
                ['label' => 'Транспорт в пути', 'url' => ['/freights-on-the-way']],
                ['label' => '<i class="fa fa-map-marker" aria-hidden="true"></i> Транспорт на карте', 'url' => ['/freights-on-the-way/geopos']],
                ['label' => 'Проверка транспорта и водителей', 'url' => ['/ferrymen/missing-drivers-transport']],
                '<li class="divider"></li>',
                ['label' => '<i class="fa fa-money" aria-hidden="true"></i> Платежные ордеры', 'url' => ['/payment-orders']],
                '<li class="divider"></li>',
                ['label' => 'Перевозчики', 'url' => ['/ferrymen']],
                ['label' => 'Водители', 'url' => ['/ferrymen-drivers']],
                ['label' => 'Транспорт', 'url' => ['/ferrymen-transport']],
                //'<li class="dropdown-header">Отчеты</li>',
                '<li class="dropdown-header">Дополнительно</li>',
                ['label' => 'Марки автомобилей', 'url' => ['/transport-brands']],
                ['label' => 'Типы техники', 'url' => ['/transport-types']],
                //['label' => 'Регионы и города', 'url' => ['/cities']],
                //['label' => 'Классы опасности', 'url' => ['/danger-classes']],
                ['label' => 'Виды упаковки', 'url' => ['/packing-types']],
                //['label' => 'Агрегатные состояния', 'url' => ['/aggregate-states']],
                //['label' => 'Виды периодичности', 'url' => ['/periodicity-kinds']],
                ['label' => 'Единицы измерения', 'url' => ['/units']],
                //['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']],
            ],
        ],
        [
            'label' => '<i class="fa fa-file-pdf-o"></i>',
            'title' => 'Хранилище',
            'url' => '#',
            'items' => [
                '<li class="dropdown-header">Файловое хранилище</li>',
                ['label' => 'Файлы', 'url' => ['/storage']],
                ['label' => 'Парсинг', 'url' => ['/storage/scan-directory']],
            ],
            'visible' => false,
        ],
        [
            'label' => '<i class="fa fa-briefcase"></i>',
            //'linkOptions' => ['title' => 'Подсказка'],
            'url' => '#',
            'items' => [
                ['label' => '<i class="fa fa-envelope"></i> Пакеты корреспонденции', 'url' => ['/correspondence-packages']],
                '<li class="dropdown-header">Проекты</li>',
                ['label' => 'Проекты', 'url' => ['/projects']],
                ['label' => 'Оценки проектов', 'url' => ['/projects/ratings']],
                ['label' => 'Матрица статусов проектов', 'url' => ['/projects/states-matrix']],
                '<li class="dropdown-header"><i class="fa fa-cog"></i> Производство</li>',
                ['label' => 'Производство', 'url' => ['/production']],
                ['label' => 'Файлы обратной связи', 'url' => ['/production-feedback-files']],
                /*
                '<li class="dropdown-header"><i class="fa fa-leaf"></i> Экология</li>',
                ['label' => \backend\controllers\EcoProjectsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\EcoProjectsController::ROOT_URL_AS_ARRAY],
                ['label' => \backend\controllers\EcoMilestonesController::MAIN_MENU_LABEL, 'url' => \backend\controllers\EcoMilestonesController::ROOT_URL_AS_ARRAY],
                ['label' => \backend\controllers\EcoTypesController::MAIN_MENU_LABEL, 'url' => \backend\controllers\EcoTypesController::ROOT_URL_AS_ARRAY],
                */
                '<li class="dropdown-header"><i class="fa fa-file-text-o"></i> Лицензии</li>',
                ['label' => '<i class="fa fa-magic text-primary"></i> Мастер обработки запросов лицензий', 'url' => ['/licenses-requests/wizard']],
                ['label' => 'Запросы лицензий', 'url' => ['/licenses-requests']],
                ['label' => 'Файлы сканов', 'url' => ['/licenses-files']],
                '<li class="dropdown-header"><i class="fa fa-file-pdf-o"></i> Файловое хранилище</li>',
                ['label' => 'Файлы', 'url' => ['/storage']],
                ['label' => 'Парсинг', 'url' => ['/storage/scan-directory']],
                '<li class="dropdown-header">Личный кабинет клиента</li>',
                ['label' => 'Отправить приглашение', 'url' => ['/invite-customer']],
            ],
        ],
        [
            'label' => '<i class="fa fa-envelope"></i>',
            'url' => '#',
            'items' => [
                ['label' => 'Пакеты корреспонденции', 'url' => ['/correspondence-packages']],
            ],
            'visible' => false,
        ],
        [
            'label' => '<i class="fa fa-phone" aria-hidden="true"></i>',
            'linkOptions' => ['title' => 'Телефония'],
            'url' => '#',
            'items' => [
                ['label' => \backend\controllers\PbxCallsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\PbxCallsController::ROOT_URL_AS_ARRAY],
                '<li class="dropdown-header">Справочники мини-АТС</li>',
                ['label' => \backend\controllers\PbxDepartmentsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\PbxDepartmentsController::ROOT_URL_AS_ARRAY],
                ['label' => \backend\controllers\PbxEmployeesController::MAIN_MENU_LABEL, 'url' => \backend\controllers\PbxEmployeesController::ROOT_URL_AS_ARRAY],
                ['label' => \backend\controllers\PbxInternalPhoneNumbersController::MAIN_MENU_LABEL, 'url' => \backend\controllers\PbxInternalPhoneNumbersController::ROOT_URL_AS_ARRAY],
                ['label' => \backend\controllers\PbxWebsitesController::MAIN_MENU_LABEL, 'url' => \backend\controllers\PbxWebsitesController::ROOT_URL_AS_ARRAY],
                '<li class="dropdown-header"><i class="fa fa-pie-chart"></i> Отчеты</li>',
                ['label' => 'Анализ телефонии', 'url' => ['/reports/pbx-analytics']],
                ['label' => 'Наличие проектов и задач', 'url' => ['/reports/pbx-calls-has-tasks-assigned']],
            ],
        ],
        [
            'label' => 'Отчеты и обработки',
            'url' => '#',
            'items' => [
                '<li class="dropdown-header">Отчеты</li>',
                ['label' => '<i class="fa fa-pie-chart text-primary"></i> Анализ обращений', 'url' => ['/reports/analytics']],
                ['label' => '<i class="fa fa-pie-chart text-primary"></i> Анализ запросов на транспорт', 'url' => ['/reports/tr-analytics']],
                ['label' => '<i class="fa fa-pie-chart text-primary"></i> Анализ корреспонденции', 'url' => ['/reports/correspondence-analytics']],
                ['label' => '<i class="fa fa-pie-chart text-primary"></i> Анализ отправлений', 'url' => ['/reports/correspondence-manual-analytics']],
                ['label' => '<i class="fa fa-bar-chart text-primary"></i> Статистика по хранилищу', 'url' => ['/reports/file-storage-stats']],
                '<li class="divider"></li>',
                ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по оборотам клиентов', 'url' => ['/reports/turnover']],
                ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по пустым клиентам', 'url' => ['/reports/emptycustomers']],
                ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по клиентам без оборотов', 'url' => ['/reports/nofinances']],
                ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по дубликатам в контрагентах', 'url' => ['/reports/ca-duplicates']],
                ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по клиентам без оплаты транспорта', 'url' => ['/reports/no-transport-has-projects']],
                '<li class="dropdown-header">Обработки</li>',
                ['label' => '<i class="fa fa-cogs"></i> Оплата рейсов', 'url' => ['/process/freights-payments']],
                ['label' => '<i class="fa fa-cogs"></i> Замена видов упаковки', 'url' => ['/transport-requests/packing-type-mass-replace']],
                ['label' => '<i class="fa fa-cogs"></i> Закрытие этапов', 'url' => ['/process/closing-milestones']],
                ['label' => '<i class="fa fa-key"></i> Замена паролей', 'url' => ['/process/replace-passwords']],
            ],
        ],
        [
            'label' => 'Обработки',
            'url' => '#',
            'items' => [
                ['label' => '<i class="fa fa-cogs"></i> Оплата рейсов', 'url' => ['/process/freights-payments']],
            ],
            'visible' => false,
        ],
    ];
elseif (Yii::$app->user->can('role_documents'))
    $items = [
        ['label' => '<i class="fa fa-file-text-o fa-lg"></i>', 'url' => ['/documents'], 'linkOptions' => ['title' => 'Документы']],
    ];
elseif (Yii::$app->user->can('accountant'))
    $items = [
        ['label' => 'Перевозчики', 'url' => ['/ferrymen']],
        ['label' => '<i class="fa fa-money" aria-hidden="true"></i> Платежные ордеры', 'url' => ['/payment-orders']],
        [
            'label' => 'Обработки',
            'url' => '#',
            'items' => [
                ['label' => '<i class="fa fa-cogs"></i> Оплата рейсов', 'url' => ['/process/freights-payments']],
            ],
        ],
    ];
elseif (Yii::$app->user->can('sales_department_head'))
    $items = [
        ['label' => '<i class="fa fa-magic fa-lg text-success"></i> Мастер обработки обращений', 'url' => ['/appeals/wizard']],
        ['label' => '<i class="fa fa-magic fa-lg text-primary"></i> Мастер обработки запросов лицензий', 'url' => ['/licenses-requests/wizard']],
        ['label' => '<i class="fa fa-headphones fa-lg"></i>', 'url' => ['/appeals'], 'linkOptions' => ['title' => 'Обращения']],
        ['label' => '<i class="fa fa-phone fa-lg"></i>', 'url' => \backend\controllers\PbxCallsController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Звонки']],
        ['label' => '<i class="fa fa-truck fa-lg"></i>', 'url' => ['/transport-requests'], 'linkOptions' => ['title' => 'Запросы на транспорт']],
        ['label' => '<i class="fa fa-file-text-o"></i>', 'url' => ['/licenses-requests'], 'linkOptions' => ['title' => 'Запросы лицензий']],
        ['label' => '<i class="fa fa-file-pdf-o"></i>', 'url' => ['/storage'], 'linkOptions' => ['title' => 'Файловое хранилище']],
        [
            'label' => 'Отчеты',
            'url' => '#',
            'items' => [
                ['label' => '<i class="fa fa-pie-chart text-primary"></i> Анализ обращений', 'url' => ['/reports/analytics']],
                '<li class="divider"></li>',
                ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по клиентам', 'url' => ['/reports/turnover']],
                ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по дубликатам в контрагентах', 'url' => ['/reports/ca-duplicates']],
                ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по клиентам без оплаты транспорта', 'url' => ['/reports/no-transport-has-projects']],
            ],
        ],
    ];
elseif (Yii::$app->user->can('sales_department_manager'))
    $items = [
        ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']],
        [
            'label' => '<i class="fa fa-file-text-o"></i>',
            'linkOptions' => ['title' => 'Запросы лицензий'],
            'url' => '#',
            'items' => [
                ['label' => 'Создать запрос лицензии', 'url' => ['/licenses-requests/create']],
                ['label' => 'Запросы лицензий', 'url' => ['/licenses-requests']],
            ],
        ],
        ['label' => '<i class="fa fa-envelope"></i> Пакеты корреспонденции', 'url' => ['/correspondence-packages']],
        ['label' => '<i class="fa fa-file-pdf-o"></i> Файловое хранилище', 'url' => ['/storage']],
    ];
elseif (Yii::$app->user->can('operator_head'))
    $items = [
        ['label' => '<i class="fa fa-fax fa-lg"></i> Добавить обращение', 'url' => ['/appeals/create']],
        ['label' => '<i class="fa fa-volume-control-phone fa-lg"></i> Обращения', 'url' => ['/appeals'], 'linkOptions' => ['title' => 'Обращения']],
        ['label' => '<i class="fa fa-envelope"></i> Пакеты документов', 'url' => ['/correspondence-packages']],
        [
            'label' => '<i class="fa fa-file-pdf-o"></i>',
            'title' => 'Хранилище',
            'url' => '#',
            'items' => [
                '<li class="dropdown-header">Файловое хранилище</li>',
                ['label' => 'Файлы', 'url' => ['/storage']],
                ['label' => 'Парсинг', 'url' => ['/storage/scan-directory']],
            ],
        ],
    ];
elseif (Yii::$app->user->can('operator'))
    $items = [
        ['label' => '<i class="fa fa-fax fa-lg"></i> Добавить обращение', 'url' => ['/appeals/create']],
        ['label' => '<i class="fa fa-volume-control-phone fa-lg"></i> Обращения', 'url' => ['/appeals'], 'linkOptions' => ['title' => 'Обращения']],
        ['label' => 'Пакеты документов', 'url' => ['/correspondence-packages']],
    ];
elseif (Yii::$app->user->can('logist'))
    $items = [
        ['label' => '<i class="fa fa-briefcase"></i> Проекты', 'url' => ['/projects'], 'linkOptions' => ['title' => 'Проекты']],
        ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']],
        ['label' => 'Подбор перевозчиков', 'url' => ['/projects/ferrymen-casting']],
        ['label' => '<i class="fa fa-money" aria-hidden="true"></i> Платежные ордеры', 'url' => ['/payment-orders']],
        ['label' => 'Транспорт в пути', 'url' => ['/freights-on-the-way']],
        ['label' => '<i class="fa fa-map-marker" aria-hidden="true"></i> Транспорт на карте', 'url' => ['/freights-on-the-way/geopos']],
        [
            'label' => 'Справочники',
            'url' => '#',
            'items' => [
                ['label' => '<i class="fa fa-truck text-info"></i> Перевозчики', 'url' => ['/ferrymen']],
                ['label' => 'Водители', 'url' => ['/ferrymen-drivers']],
                ['label' => 'Транспорт', 'url' => ['/ferrymen-transport']],
                ['label' => 'Типы техники', 'url' => ['/transport-types']],
                ['label' => 'Марки автомобилей', 'url' => ['/transport-brands']],
            ],
        ],
    ];
elseif (Yii::$app->user->can('dpc_head'))
    // Руководитель ЦОД
    $items = [
        ['label' => '<i class="fa fa-file-pdf-o"></i> Файловое хранилище', 'url' => ['/storage']],
        [
            'label' => 'Отчеты',
            'url' => '#',
            'items' => [
                ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по оборотам клиентов', 'url' => ['/reports/turnover']],
                ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по дубликатам в контрагентах', 'url' => ['/reports/ca-duplicates']],
            ],
        ]
    ];
elseif (Yii::$app->user->can('prod_department_head'))
    // Старший смены на производстве
    $items = [
        ['label' => 'Производство', 'url' => ['/production']],
        ['label' => 'Транспорт в пути', 'url' => ['/freights-on-the-way']],
    ];
elseif (Yii::$app->user->can('prod_feedback'))
    // Просмотр файлов обратной связи от производства
    $items = [
        ['label' => 'Файлы обратной связи', 'url' => ['/production-feedback-files']],
    ];
elseif (Yii::$app->user->can('head_assist'))
    // Просмотр файлов обратной связи от производства
    $items = [
        ['label' => 'Файлы обратной связи', 'url' => ['/production-feedback-files']],
        ['label' => '<i class="fa fa-briefcase"></i> Проекты', 'url' => ['/projects'], 'linkOptions' => ['title' => 'Проекты']],
        ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']],
        [
            'label' => 'Справочники',
            'url' => '#',
            'items' => [
                ['label' => '<i class="fa fa-truck text-info"></i> Перевозчики', 'url' => ['/ferrymen']],
                ['label' => 'Водители', 'url' => ['/ferrymen-drivers']],
                ['label' => 'Транспорт', 'url' => ['/ferrymen-transport']],
                ['label' => 'Типы техники', 'url' => ['/transport-types']],
                ['label' => 'Марки автомобилей', 'url' => ['/transport-brands']],
            ],
        ],
    ];
elseif (Yii::$app->user->can('licenses_upload'))
    // Загрузка файлов сканов лицензий
    $items = [
        ['label' => 'Файлы сканов', 'url' => ['/licenses-files']],
    ];
elseif (Yii::$app->user->can('pbx'))
    // Телефония
    $items = [
        ['label' => '<i class="fa fa-phone fa-lg"></i> ' . \backend\controllers\PbxCallsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\PbxCallsController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Телефония']],
    ];
elseif (Yii::$app->user->can('ecologist_head'))
    // начальник отдела экологии
    $items = [
        ['label' => '<i class="fa fa-leaf fa-lg"></i> ' . \backend\controllers\EcoProjectsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\EcoProjectsController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Проекты по экологии']],
    ];
elseif (Yii::$app->user->can('ecologist'))
    // эколог
    $items = [
        ['label' => '<i class="fa fa-leaf fa-lg"></i> ' . \backend\controllers\EcoProjectsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\EcoProjectsController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Проекты по экологии']],
    ];

$items[] = '<li>'
    . Html::beginForm(['/logout'], 'post')
    . Html::submitButton(
        '<i class="fa fa-power-off" aria-hidden="true"></i> Выход (' . Yii::$app->user->identity->username . ')',
        ['class' => 'btn btn-link logout']
    )
    . Html::endForm()
    . '</li>';
echo Nav::widget([
    'options' => ['class' => 'nav navbar-nav navbar-right'],
    'items' => $items,
    'encodeLabels' => false,
]);
NavBar::end();
?>

<div class="container-fluid main">
    <?= Breadcrumbs::widget([
        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        'encodeLabels' => false,
    ]) ?>

    <?= Alert::widget() ?>

    <?= $content ?>

</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
