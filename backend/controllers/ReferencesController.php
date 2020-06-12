<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

use common\models\ProductionSites;

/**
 * AdvanceHoldersController implements the CRUD actions for FinanceAdvanceHolders model.
 */
class ReferencesController extends Controller
{
    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'references';

    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Справочники';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = self::MAIN_MENU_LABEL;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Доступные в системе справочники.
     * @return array
     */
    private function fetchReferences()
    {
        return [
            // вернуть когда-нибудь может быть:
            //['label' => '<i class="fa fa-file-text-o fa-lg"></i>', 'url' => ['/documents'], 'linkOptions' => ['title' => 'Документы']],
            [
                'header' => 'Основные',
                'items' => [
                    ['label' => '<i class="fa fa-cube text-info"></i> Организации', 'url' => ['/organizations']],
                    ['label' => '<i class="fa fa-cubes text-info"></i> Подразделения', 'url' => \backend\controllers\DepartmentsController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-building-o text-info"></i> Контрагенты', 'url' => CompaniesController::ROOT_URL_AS_ARRAY],
                    ['label' => '<i class="fa fa-building-o text-success"></i> Контрагенты CRM', 'url' => CounteragentsCrmController::URL_ROOT_AS_ARRAY, 'linkOptions' => ['class' => 'text-success']],
                    ['label' => '<i class="fa fa-users text-info"></i> Пользователи', 'url' => ['/users']],
                ],
            ],
            [
                'header' => 'Вспомогательные',
                'items' => [
                    ['label' => '<i class="fa fa-user-secret text-info"></i> Источники обращения', 'url' => ['/appeal-sources']],
                    ['label' => '<i class="fa fa-trash-o text-info"></i> ФККО', 'url' => ['/fkko']],
                    ['label' => '<i class="fa fa-recycle text-info"></i> Виды обращения', 'url' => ['/handling-kinds']],
                    ['label' => 'Типы контента загружаемых файлов', 'url' => ['/uploading-files-meanings']],
                    ['label' => 'Типы техники', 'url' => ['/transport-types'], 'description' => 'Разновидности транспортных средств'],
                    ['label' => 'Виды упаковки', 'url' => ['/packing-types'], 'description' => 'Упаковка отходов перед вывозом'],
                    ['label' => 'Единицы измерения', 'url' => ['/units']],
                ],
            ],
            [
                'header' => 'Тендеры',
                'items' => [
                    ['label' => \backend\controllers\WasteEquipmentController::MAIN_MENU_LABEL, 'url' => \backend\controllers\WasteEquipmentController::ROOT_URL_AS_ARRAY],
                    ['label' => \backend\controllers\TendersPlatformsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\TendersPlatformsController::ROOT_URL_AS_ARRAY],
                    ['label' => \backend\controllers\TendersApplicationsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\TendersApplicationsController::ROOT_URL_AS_ARRAY],
                    ['label' => \backend\controllers\TendersKindsController::MAIN_MENU_LABEL, 'url' => \backend\controllers\TendersKindsController::ROOT_URL_AS_ARRAY],
                    ['label' => TendersLossReasonsController::ROOT_LABEL, 'url' => TendersLossReasonsController::URL_ROOT_AS_ARRAY],
                    ['label' => TenderFormsController::LABEL_VARIETIES, 'url' => TenderFormsController::URL_VARIETIES_INDEX_AS_ARRAY, 'description' => 'Роснефти, Росатомы'],
                    ['label' => TenderFormsController::LABEL_KINDS, 'url' => TenderFormsController::URL_KINDS_INDEX_AS_ARRAY, 'description' => 'Формы для участия в тендерах'],
                ],
            ],
            [
                'header' => 'Платежные ордеры',
                'items' => [
                    ['label' => 'Группы статей', 'url' => \backend\controllers\PoEigController::ROOT_URL_AS_ARRAY],
                    ['label' => 'Статьи расходов', 'url' => \backend\controllers\PoEiController::ROOT_URL_AS_ARRAY],
                    ['label' => 'Свойства статей расходов', 'url' => \backend\controllers\PoPropertiesController::ROOT_URL_AS_ARRAY],
                    ['label' => \backend\controllers\PoTemplatesController::MAIN_MENU_LABEL, 'url' => \backend\controllers\PoTemplatesController::ROOT_URL_AS_ARRAY],
                ],
            ],
            [
                'header' => 'Задачи',
                'items' => [
                    ['label' => \backend\controllers\TasksTypesController::ROOT_LABEL, 'url' => \backend\controllers\TasksTypesController::ROOT_URL_AS_ARRAY, 'description' => 'Разновидности (например, &laquo;Напоминание&raquo;, &laquo;Встреча&raquo;)'],
                    ['label' => \backend\controllers\TasksStatesController::ROOT_LABEL, 'url' => \backend\controllers\TasksStatesController::ROOT_URL_AS_ARRAY, 'description' => 'Примеры: &laquo;В процессе&raquo;, &laquo;Выполнена&raquo;'],
                    ['label' => \backend\controllers\TasksPrioritiesController::ROOT_LABEL, 'url' => \backend\controllers\TasksPrioritiesController::ROOT_URL_AS_ARRAY, 'description' => ''],
                ],
            ],
            [
                'items' => [
                    ['label' => '<i class="fa fa-remove text-info"></i> Исключения номенклатуры', 'url' => ['/products-excludes']],
                    ['label' => '<i class="fa fa-user-circle text-info"></i> Подстановка ответственных', 'url' => ['/responsible-substitutes']],
                    ['label' => '<i class="fa fa-user-circle-o text-info"></i> Ответственные для отказа', 'url' => ['/responsible-refusal']],
                    ['label' => '<i class="fa fa-user-plus text-info"></i> Ответственные для новых', 'url' => ['/responsible-fornewca']],
                    ['label' => 'Ответственные по типам проектов', 'url' => ['/responsible-by-project-types']],
                    ['label' => 'Получатели корреспонденции от производства', 'url' => ['/responsible-for-production']],
                    ['label' => 'Получатели уведомлений по просроченным проектам', 'url' => ['/notifications-receivers-sncflt']],
                    ['label' => 'Получатели оповещений по проектам', 'url' => ['/notifications-receivers-sncbt']],
                    ['label' => IncomingMailTypesController::MAIN_MENU_LABEL, 'url' => IncomingMailTypesController::URL_ROOT_AS_ARRAY],
                    ['label' => ProductionSites::LABEL_ROOT, 'url' => ProductionSites::URL_ROOT_ROUTE_AS_ARRAY],
                    ['label' => DesktopWidgetsController::ROOT_LABEL, 'url' => DesktopWidgetsController::URL_ROOT_AS_ARRAY],
                    ['label' => OutdatedObjectsReceiversController::ROOT_LABEL, 'url' => OutdatedObjectsReceiversController::URL_ROOT_AS_ARRAY],
                    ['label' => FerrymenPricesController::ROOT_LABEL, 'url' => FerrymenPricesController::URL_ROOT_AS_ARRAY],
                ],
            ],
        ];
    }

    /**
     * Lists all FinanceAdvanceHolders models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index', ['items' => $this->fetchReferences()]);
    }
}
