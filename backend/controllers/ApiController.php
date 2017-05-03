<?php

namespace backend\controllers;

use common\models\AppealSources;
use common\models\DirectMSSQLQueries;
use common\models\FreshOfficeAPI;
use common\models\ResponsibleRefusal;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Appeals;
use common\models\ExternalAppealForm;

/**
 * ApiController содержит методы для добавления обращения из форм снаружи, отслеживания изменения финансового состояния
 * контрагентов.
 */
class ApiController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['process-form', 'check-finances', 'update-counteragents-names'],
                'rules' => [
                    [
                        'allow' => true,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'process-form' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($action->id === 'process-form') {
            // для этого экшна отключаем проверку на доступ извне
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Выполняет создание обращения, а затем его анализ: идентификация контрагента, установка его статуса
     * в зависимости от финансов, установка статуса обращения, отправка необходимых задач пользователям CRM
     * через API Fresh Office.
     * Снаружи приходят такие данные (пример):
     * referrer: http://wastelogistic.ru/kontakty
     * hostInfo: http://31.148.12.5:8081,
     * hostName: 31.148.12.5
     * serverName: 31.148.12.5
     * url: /api/process-form
     * userAgent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36 OPR/43.0.2442.1144,
     * userHost:
     * userIP: 185.154.73.19
     * Конец примера.
     */
    public function actionProcessForm()
    {
        if (Yii::$app->request->isPost) {
            $form = new ExternalAppealForm();
            if ($form->load(Yii::$app->request->post())) {
                // возвращаем пользователя на ту страницу, откуда он пришел
                // либо на страницу, которая задана в POST-параметрах
                if (Yii::$app->request->post('redirect') != null)
                    $this->redirect(Yii::$app->request->post('redirect'));
                else
                    $this->redirect(Yii::$app->request->referrer);

                // выборка источников обращения в виде массива: id | name
                $appealSources = AppealSources::find()->select(['id', 'search_field'])->asArray()->all();

                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                // СОЗДАНИЕ ОБРАЩЕНИЯ
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                $model = new Appeals();

                // поля формы
                $model->form_company = $form->company;
                $model->form_username = $form->name;
                $model->form_region = $form->region;
                // в номере телефона оставляем только цифры
                $model->form_phone = preg_replace("/[^0-9]/", '', $form->phone);
                $model->form_email = $form->email;
                $model->form_message = $form->message;

                // поля post-запроса
                $model->request_referrer = Yii::$app->request->referrer;
                $model->request_user_agent = Yii::$app->request->userAgent;
                $model->request_user_ip = Yii::$app->request->userIP;

                // попытаемся идентифицировать источник обращения по доменному имени
                $presumable_domain = parse_url(Yii::$app->request->referrer, PHP_URL_HOST);
                if ($presumable_domain !== false && $presumable_domain != '') {
                    $key = array_search($presumable_domain, array_column($appealSources, 'search_field'));
                    if (false !== $key) $model->as_id = $appealSources[$key]['id'];
                }

                // сохраняем обращение
                $model->save();
                // пытаемся идентифицировать контрагента и заполняем статусы клиента и обращения
                $model->fillStates($model->tryToIdentifyCounteragent());
                // сохраняем измененные статусы
                $model->save();
            }
        }
    }

    /**
     * Модуль проверки состояния финансов контрагентов.
     * Алгоритм работы: выполняется выборка контрагентов из обращений со статусом Ожидает оплаты.
     * Контрагенты в обращениях сворачиваются до неповторяющихся.
     * При переборе результирующей выборки меняются статусы обращений:
     * На "Отказ", если ответственный у контрагента стал "Банк" или "Банк входящие".
     * На "Конверсия", если появились записи в разделе Финансы.
     * Если ни одно из двух вышеперечисленных условий не выполнилось, обращение откладывается до следующего раза.
     */
    public function actionCheckFinances()
    {
        // сделаем выборку обращений, которые нуждаются в обработке
        // это обращения в статусе "Ожидает оплаты"
        $appeals = Appeals::find()->where(['state_id' => Appeals::APPEAL_STATE_PAYMENT])->all();
        // соберем уникальные идентификаторы контрагентов в этих обращениях
        // distinct не подходит, потому что обращения нужно брать все, сворачивать нельзя
        $ca_ids = [];
        foreach ($appeals as $appeal)
            /* @var $appeal Appeals */
            if (!in_array($appeal->fo_id_company, $ca_ids)) $ca_ids[] = $appeal->fo_id_company;

        // если в результате сбора идентификаторов контрагентов массив не пустой, то приступим к выборке из MS SQL
        if (count($ca_ids) > 0) {
            $query_text = '
SELECT COMPANY.ID_COMPANY AS id, ID_MANAGER AS manager_id, ISNULL(COUNT_FINANCE, 0) AS financeCount
FROM COMPANY
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_MANY) AS COUNT_FINANCE
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . FreshOfficeAPI::FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ . ' AND
	      ID_NAPR = ' . FreshOfficeAPI::FINANCES_DIRECTION_ПРИХОД . '
	GROUP BY ID_COMPANY
) AS FINANCES ON FINANCES.ID_COMPANY = COMPANY.ID_COMPANY
WHERE COMPANY.ID_COMPANY IN (' . implode(',', $ca_ids) . ')';

            $cas = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
            if (count($cas) > 0) {
                // выборка ответственных-отказников
                // это такие ответственные, при обнаружении которых обращение падает в статус "Отказ"
                $responsibleRefusal = ResponsibleRefusal::find()->select('responsible_id')->asArray()->column();
                foreach ($appeals as $appeal) {
                    foreach ($cas as $ca) {
                        if ($ca['id'] == $appeal->fo_id_company) {
                            // если текущий менеджер находится в списке отказников, то обращение получает статус "Отказ"
                            if (in_array($ca['manager_id'], $responsibleRefusal)) {
                                $appeal->state_id = Appeals::APPEAL_STATE_REJECT;
                                $appeal->save();
                            }

                            // если количество финансов больше нуля, то обращение получает статус "Конверсия"
                            if ($ca['finance_count'] > 0) {
                                $appeal->state_id = Appeals::APPEAL_STATE_SUCCESS;
                                $appeal->save();
                            }

                            // больше не будем перебирать контрагентов, сразу переходим к следующему обращению
                            continue 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Модуль обновления наименований контрагентов, созданных вручную из веб-приложения.
     * Запускается по заданию, делает выборку обращений, созданных не более недели назад.
     * Если наименование контрагента изменилось, то выполняется его обновление.
     */
    public function actionUpdateCounteragentsNames()
    {
        // вычислим дату неделю назад
        $date = new \DateTime(date('Y-m-d'));
        $date->modify('-7 day');
        $week_ago = strtotime($date->format('Y-m-d'));

        $query = Appeals::find()
            // не старше недели
            ->where('created_at >= ' . $week_ago)
            // только с новыми контрагентами
            ->andWhere(['ca_state_id' => Appeals::CA_STATE_NEW]);
        $appeals = $query->select(['id', 'ca_id' => 'fo_id_company', 'ca_old_name' => 'fo_company_name'])->asArray()->all();
        $distinct_counteragents = $query->select('fo_id_company')->distinct()->column();
        $appeals = DirectMSSQLQueries::fillAppealsArrayWithNames($appeals, implode(',', $distinct_counteragents));

        foreach ($appeals as $appeal) {
            if ($appeal['ca_old_name'] != $appeal['ca_name']) {
                //print '<p>У контрагента ' . $appeal['ca_id'] . ' изменилось наименование: ' . $appeal['ca_old_name'] . ' -> ' . $appeal['ca_name'] . '!</p>';
                $appeal_model = Appeals::findOne($appeal['id']);
                if ($appeal_model != null) {
                    $appeal_model->fo_company_name = $appeal['ca_name'];
                    $appeal_model->save();
                }
            }
        }
    }
}
