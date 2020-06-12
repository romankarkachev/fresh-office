<?php

namespace common\models;

use Yii;
use yii\base\Model;

use backend\controllers\CompaniesController;
use backend\controllers\CounteragentsCrmController;
use backend\controllers\EdfController;
use backend\controllers\EcoContractsController;
use backend\controllers\EcoMilestonesController;
use backend\controllers\EcoProjectsController;
use backend\controllers\EcoReportsKindsController;
use backend\controllers\EcoTypesController;
use backend\controllers\TendersController;
use backend\controllers\PoController;
use backend\controllers\AdvanceHoldersController;
use backend\controllers\AdvanceReportsController;
use backend\controllers\IncomingMailController;
use backend\controllers\OutcomingMailController;
use backend\controllers\PoTemplatesController;

/**
 * Главное меню.
 * @author Roman Karkachev <post@romankarkachev.ru>
 */
class MainMenu extends Model
{
    /**
     * Возвращает набор пунктов меню для пользователю с ролью, переданной в параметрах.
     * @param string $roleName наименование роли пользователя, для которого строится меню
     * @return array
     */
    public static function getItems($roleName)
    {
        switch ($roleName) {
            case AuthItem::ROLE_ACCOUNTANT:
                // бухгалтер
                return [
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
            case AuthItem::ROLE_ACCOUNTANT_B:
                // бухгалтер по бюджету
                return [
                    ['label' => '<i class="fa fa-building-o text-primary"></i> Контрагенты', 'url' => CompaniesController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-truck text-info"></i> Перевозчики', 'url' => ['/payment-orders']],
                    ['label' => '<i class="fa fa-money text-success"></i> Бюджет', 'url' => PoController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-id-card-o"></i> ' . AdvanceReportsController::MAIN_MENU_LABEL, 'url' => AdvanceReportsController::URL_ROOT_AS_ARRAY],
                    ['label' => 'Подотчет', 'url' => AdvanceHoldersController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-file-pdf-o text-warning"></i> Файловое хранилище', 'url' => ['/storage']],
                    ['label' => PoTemplatesController::MAIN_MENU_LABEL, 'url' => PoTemplatesController::ROOT_URL_AS_ARRAY],
                    ['label' => 'Перевозчики', 'url' => ['/ferrymen']],
                ];
            case AuthItem::ROLE_ACCOUNTANT_SALARY:
                // бухгалтер по зарплате
                return [
                    ['label' => '<i class="fa fa-money text-success"></i> Платежные ордеры', 'url' => PoController::ROOT_URL_AS_ARRAY],
                ];
            case AuthItem::ROLE_ASSISTANT:
                // помощник, административный отдел
                return [
                    ['label' => 'Контрагенты', 'url' => CompaniesController::ROOT_URL_AS_ARRAY],
                    ['label' => PoController::ROOT_LABEL, 'url' => PoController::ROOT_URL_AS_ARRAY],
                    ['label' => IncomingMailController::ROOT_LABEL, 'url' => IncomingMailController::URL_ROOT_AS_ARRAY],
                    ['label' => OutcomingMailController::MAIN_MENU_LABEL, 'url' => OutcomingMailController::URL_ROOT_AS_ARRAY],
                ];
            case AuthItem::ROLE_CUSTOMER:
                return [];
            case AuthItem::ROLE_DPC_HEAD:
                // руководитель ЦОД
                return [
                    ['label' => '<i class="fa fa-envelope"></i> Пакеты корреспонденции', 'url' => ['/correspondence-packages']],
                    ['label' => '<i class="fa fa-file-pdf-o"></i> Файловое хранилище', 'url' => ['/storage']],
                    ['label' => '<i class="fa fa-truck fa-lg"></i>', 'url' => ['/transport-requests'], 'linkOptions' => ['title' => 'Запросы на транспорт']],
                    ['label' => '<i class="fa fa-file-text-o"></i>', 'url' => ['/licenses-requests'], 'linkOptions' => ['title' => 'Запросы лицензий']],
                    [
                        'label' => 'Отчеты',
                        'url' => '#',
                        'items' => [
                            ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по дубликатам в контрагентах', 'url' => ['/reports/ca-duplicates']],
                        ],
                    ]
                ];
            case AuthItem::ROLE_ECOLOGIST:
                // эколог
                return [
                    ['label' => '<i class="fa fa-building-o text-info"></i> Контрагенты', 'url' => CompaniesController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-money"></i> Бюджет', 'url' => PoController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-leaf fa-lg text-success"></i> ' . EcoProjectsController::MAIN_MENU_LABEL, 'url' => EcoProjectsController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Проекты по экологии']],
                    ['label' => '<i class="fa fa-file-pdf-o"></i> Хранилище', 'url' => ['/storage'], 'linkOptions' => ['title' => 'Файловое хранилище']],
                    ['label' => '<i class="fa fa-file-word-o"></i> Документооборот', 'url' => EdfController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-envelope"></i> Пакеты корреспонденции', 'url' => ['/correspondence-packages']],
                    ['label' => '<i class="fa fa-leaf fa-lg"></i> ' . EcoContractsController::MAIN_MENU_LABEL, 'url' => EcoContractsController::ROOT_URL_AS_ARRAY],
                ];
            case AuthItem::ROLE_ECOLOGIST_HEAD:
                // начальник отдела экологии
                return [
                    [
                        'label' => '<i class="fa fa-leaf"></i>',
                        'linkOptions' => ['title' => 'Экология'],
                        'url' => '#',
                        'items' => [
                            ['label' => EcoProjectsController::MAIN_MENU_LABEL, 'url' => EcoProjectsController::ROOT_URL_AS_ARRAY],
                            '<li class="dropdown-header">Сопровождение</li>',
                            ['label' => EcoContractsController::MAIN_MENU_LABEL, 'url' => EcoContractsController::ROOT_URL_AS_ARRAY],
                            ['label' => EcoReportsKindsController::MAIN_MENU_LABEL, 'url' => EcoReportsKindsController::ROOT_URL_AS_ARRAY],
                            '<li class="dropdown-header">Справочники</li>',
                            ['label' => EcoMilestonesController::MAIN_MENU_LABEL, 'url' => EcoMilestonesController::ROOT_URL_AS_ARRAY],
                            ['label' => EcoTypesController::MAIN_MENU_LABEL, 'url' => EcoTypesController::ROOT_URL_AS_ARRAY],
                        ],
                    ],
                    ['label' => '<i class="fa fa-building-o text-info"></i> Контрагенты', 'url' => CompaniesController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-file-pdf-o"></i> Хранилище', 'url' => ['/storage'], 'linkOptions' => ['title' => 'Файловое хранилище']],
                    ['label' => '<i class="fa fa-file-word-o"></i> Документооборот', 'url' => EdfController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-envelope"></i> Пакеты корреспонденции', 'url' => ['/correspondence-packages']],
                    ['label' => '<i class="fa fa-money"></i> Бюджет', 'url' => PoController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-gavel"></i> ' . TendersController::ROOT_LABEL, 'url' => TendersController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Тендеры']],
                ];
            case AuthItem::ROLE_EDF:
                // электронный документооборот
                return [
                    ['label' => '<i class="fa fa-file-word-o"></i> Документооборот', 'url' => EdfController::ROOT_URL_AS_ARRAY],
                ];
            case AuthItem::ROLE_FERRYMAN:
                return [];
            case AuthItem::ROLE_FOREIGNDRIVER:
                return [];
            case AuthItem::ROLE_HEAD_ASSIST:
                // просмотр файлов обратной связи от производства
                return [
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
            case AuthItem::ROLE_LICENSES_UPLOAD:
                // загрузка файлов сканов лицензий
                return [
                    ['label' => 'Файлы сканов', 'url' => ['/licenses-files']],
                ];
            case AuthItem::ROLE_LOGIST:
                // логисты
                return [
                    [
                        'label' => '<i class="fa fa-briefcase"></i>',
                        //'linkOptions' => ['title' => 'Подсказка'],
                        'url' => '#',
                        'items' => [
                            ['label' => '<i class="fa fa-building-o text-info"></i> Добавить контрагента', 'url' => CompaniesController::URL_CREATE_AS_ARRAY],
                            ['label' => '<i class="fa fa-briefcase"></i> Проекты', 'url' => ['/projects'], 'linkOptions' => ['title' => 'Проекты']],
                            ['label' => 'Прикрепление файлов производства', 'url' => ['/production/attach-files']],
                            ['label' => 'Файлы обратной связи', 'url' => ['/production-feedback-files']],
                            ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']],
                            ['label' => 'Подбор перевозчиков', 'url' => ['/projects/ferrymen-casting']],
                            ['label' => '<i class="fa fa-money" aria-hidden="true"></i> Платежные ордеры', 'url' => ['/payment-orders']],
                            ['label' => 'Транспорт в пути', 'url' => ['/freights-on-the-way']],
                            ['label' => '<i class="fa fa-map-marker" aria-hidden="true"></i> Транспорт на карте', 'url' => ['/freights-on-the-way/geopos']],
                            ['label' => 'Оценки проектов', 'url' => ['/projects/ratings']],
                            ['label' => 'Файловое хранилище', 'url' => ['/storage']],
                            ['label' => 'Массовый импорт ТТН', 'url' => ['/storage/bulk-import']],
                        ],
                    ],
                    ['label' => '<i class="fa fa-cog"></i> Производство', 'url' => ['/production']],
                    ['label' => ProductionShipment::LABEL_ROOT, 'url' => ProductionShipment::URL_ROOT_ROUTE_AS_ARRAY],
                    ['label' => '<i class="fa fa-money"></i> Бюджет', 'url' => PoController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-envelope-o"></i> ' . IncomingMailController::MAIN_MENU_LABEL, 'url' => IncomingMailController::URL_ROOT_AS_ARRAY],
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
            case AuthItem::ROLE_OPERATOR:
                // оператор
                return [
                    ['label' => '<i class="fa fa-fax fa-lg"></i> Добавить обращение', 'url' => ['/appeals/create']],
                    ['label' => '<i class="fa fa-volume-control-phone fa-lg"></i> Обращения', 'url' => ['/appeals'], 'linkOptions' => ['title' => 'Обращения']],
                    ['label' => 'Пакеты документов', 'url' => ['/correspondence-packages']],
                ];
            case AuthItem::ROLE_OPERATOR_HEAD:
                // старший оператор
                $result = [
                    ['label' => '<i class="fa fa-fax fa-lg"></i> Добавить обращение', 'url' => ['/appeals/create']],
                    ['label' => '<i class="fa fa-volume-control-phone fa-lg"></i> Обращения', 'url' => ['/appeals'], 'linkOptions' => ['title' => 'Обращения']],
                    ['label' => '<i class="fa fa-file-text-o fa-lg"></i>', 'url' => ['/documents'], 'linkOptions' => ['title' => 'Документы']],
                    ['label' => '<i class="fa fa-phone fa-lg"></i> ' . \backend\controllers\PbxCallsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\PbxCallsController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Телефония']],
                    ['label' => '<i class="fa fa-envelope"></i> Пакеты документов', 'url' => ['/correspondence-packages']],
                    ['label' => '<i class="fa fa-file-word-o"></i> Документооборот', 'url' => EdfController::ROOT_URL_AS_ARRAY],
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
                    [
                        'label' => 'Обработки',
                        'url' => '#',
                        'items' => [
                            ['label' => '<i class="fa fa-cogs"></i> Импорт счетов из FO', 'url' => ['/process/import-invoices']],
                        ],
                    ],
                ];

                if (Yii::$app->user->id == 55) {
                    // только для одного из старших операторов добавляем возможность формировать архив файлов контрагентов для скачивания
                    $result[5]['items'][] = ['label' => 'Скачивание архива', 'url' => ['/storage/files-extraction']];
                }
                return $result;
            case AuthItem::ROLE_PBX:
                // телефония
                return [
                    ['label' => '<i class="fa fa-phone fa-lg"></i> ' . \backend\controllers\PbxCallsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\PbxCallsController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Телефония']],
                ];
            case AuthItem::ROLE_PROD_DEPARTMENT_HEAD:
                // старший смены на производстве
                return [
                    ['label' => 'Производство', 'url' => ['/production']],
                    ['label' => ProductionShipment::LABEL_ROOT, 'url' => ProductionShipment::URL_ROOT_ROUTE_AS_ARRAY],
                    ['label' => 'Транспорт в пути', 'url' => ['/freights-on-the-way']],
                    ['label' => '<i class="fa fa-building-o text-info"></i> ' . CompaniesController::ROOT_LABEL, 'url' => CompaniesController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-money"></i> Бюджет', 'url' => PoController::ROOT_URL_AS_ARRAY],
                ];
            case AuthItem::ROLE_PROD_FEEDBACK:
                // просмотр файлов обратной связи от производства
                return [
                    ['label' => 'Файлы обратной связи', 'url' => ['/production-feedback-files']],
                ];
            case AuthItem::ROLE_ROLE_DOCUMENTS:
                // документы (изначальное задание)
                return [
                    ['label' => '<i class="fa fa-file-text-o fa-lg"></i>', 'url' => ['/documents'], 'linkOptions' => ['title' => 'Документы']],
                ];
            case AuthItem::ROLE_ROOT:
                // полные права
                return [
                    ['label' => '<i class="fa fa-headphones fa-lg text-primary"></i>', 'url' => ['/appeals'], 'linkOptions' => ['title' => 'Обращения']],
                    [
                        'label' => 'Справочники',
                        'url' => '#',
                        'items' => [
                            ['label' => '<i class="fa fa-users text-info"></i> Пользователи', 'url' => ['/users']],
                            '<li class="divider"></li>',
                            ['label' => '<i class="fa fa-cube text-info"></i> Организации', 'url' => ['/organizations']],
                            ['label' => '<i class="fa fa-cubes text-info"></i> Подразделения', 'url' => \backend\controllers\DepartmentsController::ROOT_URL_AS_ARRAY],
                            ['label' => '<i class="fa fa-building-o text-info"></i> Контрагенты', 'url' => CompaniesController::ROOT_URL_AS_ARRAY],
                            ['label' => '<i class="fa fa-building-o text-success"></i> Контрагенты CRM', 'url' => CounteragentsCrmController::URL_ROOT_AS_ARRAY, 'linkOptions' => ['class' => 'text-success']],
                            '<li class="divider"></li>',
                            ['label' => 'Все', 'url' => ['/references']],
                            /*
                            '<li class="dropdown-header">Основные</li>',
                            ['label' => '<i class="fa fa-cube text-info"></i> Организации', 'url' => ['/organizations']],
                            ['label' => '<i class="fa fa-cubes text-info"></i> Подразделения', 'url' => \backend\controllers\DepartmentsController::ROOT_URL_AS_ARRAY],
                            ['label' => '<i class="fa fa-building-o text-info"></i> Контрагенты', 'url' => CompaniesController::ROOT_URL_AS_ARRAY],
                            ['label' => '<i class="fa fa-building-o text-success"></i> Контрагенты CRM', 'url' => CounteragentsCrmController::URL_ROOT_AS_ARRAY, 'linkOptions' => ['class' => 'text-success']],
                            ['label' => '<i class="fa fa-user-secret text-info"></i> Источники обращения', 'url' => ['/appeal-sources']],
                            ['label' => '<i class="fa fa-trash-o text-info"></i> ФККО', 'url' => ['/fkko']],
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
                            ['label' => '' . IncomingMailTypesController::MAIN_MENU_LABEL, 'url' => IncomingMailTypesController::URL_ROOT_AS_ARRAY],
                            '<li class="dropdown-header">Тендеры</li>',
                            ['label' => \backend\controllers\WasteEquipmentController::MAIN_MENU_LABEL, 'url' => \backend\controllers\WasteEquipmentController::ROOT_URL_AS_ARRAY],
                            ['label' => \backend\controllers\TendersPlatformsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\TendersPlatformsController::ROOT_URL_AS_ARRAY],
                            ['label' => \backend\controllers\TendersApplicationsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\TendersApplicationsController::ROOT_URL_AS_ARRAY],
                            ['label' => \backend\controllers\TendersKindsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\TendersKindsController::ROOT_URL_AS_ARRAY],
                            '<li class="dropdown-header">Платежные ордеры</li>',
                            ['label' => 'Группы статей', 'url' => \backend\controllers\PoEigController::ROOT_URL_AS_ARRAY],
                            ['label' => 'Статьи расходов', 'url' => \backend\controllers\PoEiController::ROOT_URL_AS_ARRAY],
                            ['label' => 'Свойства статей расходов', 'url' => \backend\controllers\PoPropertiesController::ROOT_URL_AS_ARRAY],
                            '<li class="divider"></li>',
                            ['label' => '<i class="fa fa-users text-info"></i> Пользователи', 'url' => ['/users']],
            */
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
                            ['label' => 'Перевозчики', 'url' => ['/ferrymen']],
                            ['label' => 'Пустые перевозчики', 'url' => ['/ferrymen/empty']],
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
                        'label' => '<i class="fa fa-money"></i>',
                        'title' => 'Платежные ордеры',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Перевозчики', 'url' => ['/payment-orders']],
                            ['label' => 'Бюджет', 'url' => PoController::ROOT_URL_AS_ARRAY],
                            '<li class="dropdown-header">Подотчет</li>',
                            ['label' => AdvanceReportsController::MAIN_MENU_LABEL, 'url' => AdvanceReportsController::URL_ROOT_AS_ARRAY],
                            ['label' => AdvanceHoldersController::ROOT_LABEL, 'url' => AdvanceHoldersController::ROOT_URL_AS_ARRAY],
                        ],
                    ],
                    [
                        'label' => '<i class="fa fa-file-pdf-o"></i>',
                        'title' => 'Хранилище',
                        'url' => '#',
                        'items' => [
                            ['label' => '<i class="fa fa-file-word-o"></i> Документооборот', 'url' => EdfController::ROOT_URL_AS_ARRAY],
                            '<li class="dropdown-header">Файловое хранилище</li>',
                            ['label' => 'Файлы', 'url' => ['/storage']],
                            ['label' => 'Парсинг', 'url' => ['/storage/scan-directory']],
                            ['label' => 'Скачивание архива', 'url' => ['/storage/files-extraction']],
                            '<li class="divider"></li>',
                            ['label' => 'Массовый импорт ТТН', 'url' => ['/storage/bulk-import']],
                            ['label' => 'Обязательные ТТН', 'url' => ['/storage-ttn-required']],
                        ],
                    ],
                    [
                        'label' => '<i class="fa fa-briefcase"></i>',
                        //'linkOptions' => ['title' => 'Подсказка'],
                        'url' => '#',
                        'items' => [
                            ['label' => '<i class="fa fa-envelope"></i> Пакеты корреспонденции', 'url' => ['/correspondence-packages']],
                            ['label' => '<i class="fa fa-envelope-o"></i> ' . IncomingMailController::MAIN_MENU_LABEL, 'url' => IncomingMailController::URL_ROOT_AS_ARRAY],
                            ['label' => '<i class="fa fa-envelope-o"></i> ' . OutcomingMailController::MAIN_MENU_LABEL, 'url' => OutcomingMailController::URL_ROOT_AS_ARRAY],
                            ['label' => '<i class="fa fa-clock-o"></i> Задачи', 'url' => ['/tasks']],
                            ['label' => 'Тендеры', 'url' => TendersController::ROOT_URL_AS_ARRAY],
                            '<li class="dropdown-header"><i class="fa fa-file-text-o"></i> Лицензии</li>',
                            ['label' => '<i class="fa fa-magic text-primary"></i> Мастер обработки запросов лицензий', 'url' => ['/licenses-requests/wizard']],
                            ['label' => 'Запросы лицензий', 'url' => ['/licenses-requests']],
                            ['label' => 'Файлы сканов', 'url' => ['/licenses-files']],
                            //['label' => '<i class="fa fa-file-pdf-o"></i> Файловое хранилище', 'url' => ['/storage']],
                            '<li class="dropdown-header">Личный кабинет клиента</li>',
                            ['label' => 'Отправка письма для оценки', 'url' => ['/projects/send-rating-proposal'], 'title' => 'Отправка на E-mail клиента приглашения поставить оценку качества услуг'],
                            ['label' => 'Оценки проектов', 'url' => ['/projects/ratings']],
                            ['label' => 'Отправить приглашение', 'url' => ['/invite-customer']],
                        ],
                    ],
                    [
                        'label' => '<i class="fa fa-cog"></i>',
                        'url' => '#',
                        'items' => [
                            '<li class="dropdown-header">Проекты</li>',
                            ['label' => 'Проекты', 'url' => ['/projects']],
                            ['label' => 'Матрица статусов проектов', 'url' => ['/projects/states-matrix']],
                            '<li class="dropdown-header"><i class="fa fa-cog"></i> Производство</li>',
                            ['label' => 'Производство', 'url' => ['/production']],
                            ['label' => 'Прикрепление файлов логистами', 'url' => ['/production/attach-files']],
                            ['label' => 'Файлы обратной связи', 'url' => ['/production-feedback-files']],
                            ['label' => ProductionShipment::LABEL_ROOT, 'url' => ProductionShipment::URL_ROOT_ROUTE_AS_ARRAY],
                            '<li class="dropdown-header"><i class="fa fa-cog"></i> Документы</li>',
                            ['label' => 'Генерация ТТН', 'url' => ['/documents/generate-ttn']],
                            ['label' => 'Генерация АПП', 'url' => ['/documents/generate-app']],
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
                            '<li class="dropdown-header">Транскрибация</li>',
                            ['label' => 'Очередь на распознавание', 'url' => \backend\controllers\PbxCallsController::URL_RECOGNITION_QUEUE_AS_ARRAY],
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
                        'label' => '<i class="fa fa-leaf"></i>',
                        'linkOptions' => ['title' => 'Экология'],
                        'url' => '#',
                        'items' => [
                            ['label' => EcoProjectsController::MAIN_MENU_LABEL, 'url' => EcoProjectsController::ROOT_URL_AS_ARRAY],
                            ['label' => EcoMilestonesController::MAIN_MENU_LABEL, 'url' => EcoMilestonesController::ROOT_URL_AS_ARRAY],
                            ['label' => EcoTypesController::MAIN_MENU_LABEL, 'url' => EcoTypesController::ROOT_URL_AS_ARRAY],
                            '<li class="dropdown-header">Сопровождение</li>',
                            ['label' => EcoContractsController::MAIN_MENU_LABEL, 'url' => EcoContractsController::ROOT_URL_AS_ARRAY],
                            ['label' => EcoReportsKindsController::MAIN_MENU_LABEL, 'url' => EcoReportsKindsController::ROOT_URL_AS_ARRAY],
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
                            ['label' => '<i class="fa fa-pie-chart text-primary"></i> Анализ документооборота', 'url' => ['/reports/edf-analytics']],
                            ['label' => '<i class="fa fa-bar-chart text-primary"></i> Статистика по хранилищу', 'url' => ['/reports/file-storage-stats']],
                            ['label' => '<i class="fa fa-line-chart text-primary"></i> Свод по бюджету', 'url' => ['/reports/po-analytics']],
                            '<li class="divider"></li>',
                            ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по оборотам клиентов', 'url' => ['/reports/turnover']],
                            ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по пустым клиентам', 'url' => ['/reports/emptycustomers']],
                            ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по клиентам без оборотов', 'url' => ['/reports/nofinances']],
                            ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по дубликатам в контрагентах', 'url' => ['/reports/ca-duplicates']],
                            ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по клиентам без оплаты транспорта', 'url' => ['/reports/no-transport-has-projects']],
                            ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по кураторам', 'url' => ['/reports/curators']],
                            '<li class="dropdown-header">Обработки</li>',
                            ['label' => '<i class="fa fa-cogs"></i> Оплата рейсов', 'url' => ['/process/freights-payments']],
                            ['label' => '<i class="fa fa-cogs"></i> Замена видов упаковки', 'url' => ['/transport-requests/packing-type-mass-replace']],
                            ['label' => '<i class="fa fa-cogs"></i> Закрытие этапов', 'url' => ['/process/closing-milestones']],
                            ['label' => '<i class="fa fa-cogs"></i> Закрытие счетов', 'url' => ['/process/closing-invoices']],
                            ['label' => '<i class="fa fa-key"></i> Замена паролей', 'url' => ['/process/replace-passwords']],
                            ['label' => '<i class="fa fa-cogs"></i> Импорт счетов из FO', 'url' => ['/process/import-invoices']],
                            //['label' => 'Парсинг файлов для хранилища', 'url' => ['/storage/scan-directory']],
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
            case AuthItem::ROLE_SALES_DEPARTMENT_HEAD:
                // руководитель отдела продаж
                return [
                    ['label' => '<i class="fa fa-building-o text-success"></i> Контрагенты CRM', 'url' => CounteragentsCrmController::URL_ROOT_AS_ARRAY, 'linkOptions' => ['class' => 'text-success']],
                    ['label' => '<i class="fa fa-magic fa-lg text-success"></i> Мастер обработки обращений', 'url' => ['/appeals/wizard']],
                    ['label' => '<i class="fa fa-magic fa-lg text-primary"></i> Мастер обработки запросов лицензий', 'url' => ['/licenses-requests/wizard']],
                    ['label' => '<i class="fa fa-headphones fa-lg"></i>', 'url' => ['/appeals'], 'linkOptions' => ['title' => 'Обращения']],
                    ['label' => '<i class="fa fa-phone fa-lg"></i>', 'url' => \backend\controllers\PbxCallsController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Звонки']],
                    ['label' => '<i class="fa fa-truck fa-lg"></i>', 'url' => ['/transport-requests'], 'linkOptions' => ['title' => 'Запросы на транспорт']],
                    ['label' => '<i class="fa fa-file-text-o fa-lg"></i>', 'url' => ['/licenses-requests'], 'linkOptions' => ['title' => 'Запросы лицензий']],
                    ['label' => '<i class="fa fa-gavel fa-lg"></i>', 'url' => TendersController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Тендеры']],
                    ['label' => '<i class="fa fa-file-pdf-o fa-lg"></i>', 'url' => ['/storage'], 'linkOptions' => ['title' => 'Файловое хранилище']],
                    ['label' => '<i class="fa fa-leaf fa-lg text-success"></i>', 'url' => EcoProjectsController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Проекты по экологии']],
                    ['label' => '<i class="fa fa-file-word-o"></i>', 'url' => EdfController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Документооборот']],
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
            case AuthItem::ROLE_SALES_DEPARTMENT_MANAGER:
                // менеджер по продажам
                return [
                    ['label' => '<i class="fa fa-building-o"></i>', 'url' => CounteragentsCrmController::URL_ROOT_AS_ARRAY, 'linkOptions' => ['title' => 'Контрагенты CRM']],
                    ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']],
                    [
                        'label' => '<i class="fa fa-file-text-o"></i>',
                        'linkOptions' => ['title' => 'Запросы лицензий'],
                        'url' => '#',
                        'items' => [
                            ['label' => 'Создать запрос лицензии', 'url' => ['/licenses-requests/create']],
                            ['label' => 'Запросы лицензий', 'url' => ['/licenses-requests']],
                            '<li class="dropdown-header"><i class="fa fa-cog"></i> Документы</li>',
                            ['label' => 'Генерация ТТН', 'url' => ['/documents/generate-ttn']],
                            ['label' => 'Генерация АПП', 'url' => ['/documents/generate-app']],
                        ],
                    ],
                    ['label' => '<i class="fa fa-file-word-o"></i>', 'url' => EdfController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Документооборот']],
                    ['label' => '<i class="fa fa-gavel"></i>', 'url' => TendersController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Тендеры']],
                    ['label' => '<i class="fa fa-money"></i>', 'url' => PoController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Платежные ордеры (бюджет)']],
                    ['label' => '<i class="fa fa-envelope"></i>', 'url' => ['/correspondence-packages'], 'linkOptions' => ['title' => 'Пакеты корреспонденции']],
                    ['label' => '<i class="fa fa-file-pdf-o"></i>', 'url' => ['/storage'], 'linkOptions' => ['title' => 'Файловое хранилище']],
                    ['label' => '<i class="fa fa-clock-o"></i>', 'url' => ['/tasks'], 'linkOptions' => ['title' => 'Задачи']],
                    ['label' => '<i class="fa fa-leaf fa-lg text-success"></i>', 'url' => EcoProjectsController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Проекты по экологии']],
                ];
            case AuthItem::ROLE_TENDERS_MANAGER:
                // специалист отдела тендеров
                return [
                    ['label' => '<i class="fa fa-gavel"></i>', 'url' => TendersController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Тендеры']],
                    ['label' => '<i class="fa fa-file-text-o"></i>', 'url' => ['/licenses-requests'], 'linkOptions' => ['title' => 'Запросы лицензий']],
                    ['label' => '<i class="fa fa-file-pdf-o"></i>', 'url' => ['/storage'], 'linkOptions' => ['title' => 'Файловое хранилище']],
                    ['label' => '<i class="fa fa-money"></i>', 'url' => PoController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Бюджет']],
                    ['label' => '<i class="fa fa-building-o text-info"></i>', 'url' => CompaniesController::ROOT_URL_AS_ARRAY, 'linkOptions' => ['title' => 'Контрагенты']],
                    ['label' => '<i class="fa fa-file-word-o"></i> Документооборот', 'url' => EdfController::ROOT_URL_AS_ARRAY],
                ];
        }

        return [];
    }
}
