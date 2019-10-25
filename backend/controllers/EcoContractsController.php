<?php

namespace backend\controllers;

use kartik\datecontrol\DateControl;
use Yii;
use common\models\EcoMc;
use common\models\EcoMcSearch;
use common\models\EcoMcTp;
use common\models\EcoMcTpSearch;
use common\models\foCompany;
use yii\base\Model;
use yii\db\Transaction;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * EcoContractsController implements the CRUD actions for EcoMc model.
 */
class EcoContractsController extends Controller
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'eco-contracts';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Договоры сопровождения по экологии';

    /**
     * Название списка записей
     */
    const ROOT_LABEL = self::MAIN_MENU_LABEL;

    /**
     * Ссылка в хлебных крошках на список записей
     */
    const ROOT_BREADCRUMB = ['label' => self::ROOT_LABEL, 'url' => self::ROOT_URL_AS_ARRAY];

    /**
     * URL для редактирования записи
     */
    const URL_UPDATE = 'update';

    const URL_RENDER_REPORT_ROW = 'render-report-row'; // для добавления нового регламентированного отчета договора (только при создании)
    const URL_CREATE_REPORT = 'create-report'; // для интерактивного добавления нового регламентированного отчета договора
    const URL_DELETE_REPORT = 'delete-report'; // для интерактивного удаления нового регламентированного отчета договора
    const URL_SUBMIT_REPORT = 'submit-report'; // для интерактивного изменения даты сдачи регламентированного отчета договора

    /**
     * URL для интерактивного вычисления наименований контрагентов
     */
    const URL_EVALUATE_CA_NAMES = 'evaluate-ca-names';
    const URL_EVALUATE_CA_NAMES_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_EVALUATE_CA_NAMES];

    const URL_RENDER_REPORT_ROW_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_RENDER_REPORT_ROW];
    const URL_CREATE_REPORT_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_CREATE_REPORT];
    const URL_DELETE_REPORT_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_REPORT];
    const URL_SUBMIT_REPORT_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_SUBMIT_REPORT];
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
                        'actions' => [
                            'index', 'create', self::URL_UPDATE, self::URL_EVALUATE_CA_NAMES,
                            self::URL_RENDER_REPORT_ROW, self::URL_CREATE_REPORT, self::URL_DELETE_REPORT, self::URL_SUBMIT_REPORT,
                        ],
                        'allow' => true,
                        'roles' => ['root', 'ecologist', 'ecologist_head'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    self::URL_DELETE_REPORT => ['POST'],
                    self::URL_SUBMIT_REPORT => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Делает выборку отчетов, включенных в договор сопровождения, переданного в параметрах.
     * @param integer $mc_id идентификатор договора
     * @return \yii\data\ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchReports($mc_id)
    {
        $searchModel = new EcoMcTpSearch();
        $dataProvider = $searchModel->search([
            $searchModel->formName() => [
                'mc_id' => $mc_id,
            ],
        ]);
        $dataProvider->sort = false;
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Возвращает новую модель отчета для включения в договор сопровождения.
     * @param integer $mc_id договор, в который включается новый отчет
     * @return EcoMcTp
     */
    private function createNewReportModel($mc_id = null)
    {
        return new EcoMcTp([
            'mc_id' => $mc_id,
        ]);
    }

    /**
     * Рендерит список отчетов, включенных в договор сопровождения.
     * @param integer $mc_id идентификатор договора
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function renderReports($mc_id)
    {
        return $this->renderAjax('_reports_list', [
            'dataProvider' => $this->fetchReports($mc_id),
            'model' => $this->createNewReportModel($mc_id),
        ]);
    }

    /**
     * Lists all EcoMc models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EcoMcSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // некоторые колонки появляются в таблице динамически
        $reportsColumns = [];
        $reports = [];

        // колонки, которые будут выводиться в итоговой таблице - это только те, которые есть в наличии
        // один отчет - одна колонка, и в ней инструменты для управления
        foreach ($dataProvider->getModels() as $model) {
            /* @var $model EcoMc */

            /**
             * $model->reports имеет примерно следующий вид:
             * 1∙4-ОС∙2019-06-27
             * 2∙2-ТП (отходы)∙2019-05-31
             * 3∙Кадастр отходов∙2019-10-10
             * каждый отчет договора с новой строки, поля разделяются знаком умножения (точкой по центру)
             */
            $contractReports = explode(chr(13), $model->reports);
            if (count($contractReports) > 0) {
                foreach ($contractReports as $contractReport) {
                    $currentReport = explode(mb_chr(0x2219, 'UTF-8'), $contractReport);
                    if (count($currentReport) > 1) {
                        // строка с отчетом должна содержать как минимум его название и идентификатор
                        $ecoMcTpId = str_replace("\n", '', $currentReport[0]);
                        $reportId = str_replace("\n", '', $currentReport[1]);
                        if (!ArrayHelper::keyExists($reportId, $reports)) {
                            $reports[$reportId] = $currentReport[2];

                            $reportsColumns[] = [
                                'sort' => $reportId,
                                'label' => $currentReport[2],
                                'format' => 'raw',
                                'options' => ['width' => '200', 'data-id' => $reportId],
                                'value' => function($model, $key, $index, $column) {
                                    /* @var $model \common\models\EcoMc */
                                    /* @var $column \yii\grid\DataColumn */

                                    $result = '';
                                    $icon = '';
                                    $addon = '';
                                    $tool = '';
                                    $contractReports = explode(chr(13), $model->reports);
                                    if (count($contractReports) > 0) {
                                        foreach ($contractReports as $contractReport) {
                                            $currentReport = explode(mb_chr(0x2219, 'UTF-8'), $contractReport);
                                            $ecoMcTpId = str_replace("\n", '', $currentReport[0]);
                                            $reportId = str_replace("\n", '', $currentReport[1]);
                                            if (count($currentReport) > 1 && $reportId == $column->options['data-id'] && isset($currentReport[3])) {
                                                $date_deadline = $currentReport[3];
                                                if (isset($currentReport[4])) {
                                                    $date_fact = $currentReport[4];
                                                    if (!empty($date_fact)) {
                                                        // отчет сдан, проверим, вовремя ли он сдан
                                                        $intime = '';
                                                        $color = '-circle text-success';
                                                        if (strtotime($date_fact . ' 00:00:00') > strtotime($date_deadline . ' 00:00:00')) {
                                                            $color = ' text-warning';
                                                            $intime = ', но не вовремя!';
                                                        }
                                                        $icon = '<i class="fa fa-check' . $color . '" aria-hidden="true" title="Отчет сдан' . $intime . '"></i><br />';
                                                        $addon = '<br />сдан ' . Yii::$app->formatter->asDate($date_fact, 'php:d.m.Y г.');
                                                        unset($color);
                                                    }
                                                }
                                                else {
                                                    // инструмент для отметки отчета как сданного и выбор даты подачи
                                                    $tool = '<br />' . Html::a('сдать', '#', ['id' => 'submitReport' . $ecoMcTpId, 'data-id' => $ecoMcTpId, 'title' => 'Щелкните, чтобы отметить отчет как поданный и выбрать дату подачи', 'class' => 'link-ajax']) .
                                                        Html::tag('div', DateControl::widget([
                                                            'id' => 'submitDate' . $ecoMcTpId,
                                                            'name' => 'submit-date' . $ecoMcTpId,
                                                            'value' => null,
                                                            'type' => DateControl::FORMAT_DATE,
                                                            'displayFormat' => 'php:d.m.Y',
                                                            'saveFormat' => 'php:Y-m-d',
                                                            'widgetOptions' => [
                                                                'layout' => '{input}{picker}',
                                                                'size' => DateControl::SIZE_SMALL,
                                                                'options' => [
                                                                    'placeholder' => '- выберите -',
                                                                    'title' => 'Выберите дату фактической подачи отчета',
                                                                    'autocomplete' => 'off',
                                                                ],
                                                                'pluginOptions' => [
                                                                    'weekStart' => 1,
                                                                    'autoclose' => true,
                                                                ],
                                                                'pluginEvents' => [
                                                                    'changeDate' => 'function(e) {
if (confirm("Будет установлена дата фактической подачи отчета в контролирующие органы. Продолжить?")) submitDate(' . $ecoMcTpId . ', e.format("yyyy-mm-dd"));
}',
                                                                ],
                                                            ],
                                                        ]), ['id' => 'block-submitDate' . $ecoMcTpId, 'class' => 'collapse']);
                                                    // отчет еще не сдан, проверим, есть ли еще время его сдать
                                                    if (strtotime($date_deadline . ' 00:00:00') > strtotime(date('Y-m-d', time()) . ' 00:00:00')) {
                                                        // время сдать еще есть
                                                        $icon = '<i class="fa fa-clock-o text-info" aria-hidden="true" title="Отчет не сдан, но еще есть время"></i><br />';
                                                    }
                                                    else {
                                                        // сдачу отчета мы просрочили
                                                        $icon = '<i class="fa fa-exclamation-triangle text-danger" aria-hidden="true" title="Подача отчета в контролирующий орган просрочена!"></i><br />';
                                                    }
                                                }

                                                $result .= $icon . 'до ' . Yii::$app->formatter->asDate($date_deadline, 'php:d.m.Y г.') . $addon . $tool;
                                            }
                                        }
                                    }

                                    return $result;
                                },
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ];
                        }
                    }
                }
            }
        }

        // сортируем динамические колонки
        ArrayHelper::multisort($reportsColumns, 'sort');
        foreach ($reportsColumns as $index => $column) {
            // удаляем колонку, предназначенную для сортировки
            unset($reportsColumns[$index]['sort']);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'reportsColumns' => $reportsColumns,
        ]);
    }

    /**
     * Creates a new EcoMc model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $model = new EcoMc();
        $modelReports = [];

        if ($model->load(Yii::$app->request->post())) {
            $success = true;

            // загрузим модели отчетов к договорам
            if (isset(Yii::$app->request->post($model->formName())['crudeReports'])) {
                $reportsUnique = [];
                foreach (Yii::$app->request->post($model->formName())['crudeReports'] as $i => $data) {
                    $newModel = new EcoMcTp();
                    $newModel->load($data, '');

                    if (!in_array($data['report_id'], $reportsUnique)) {
                        $reportsUnique[] = $data['report_id'];
                    }
                    else {
                        $success = false;
                        $newModel->addError('report_id', 'Дублирование отчетов недопустимо.');
                    }

                    $modelReports[$i] = $newModel;
                    unset($newModel);
                }
                unset($reportsUnique);
            }

            if ($success) {
                $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);

                try {
                    $valid = $model->validate();
                    $valid = Model::validateMultiple($modelReports) && $valid;
                    if ($valid) {
                        // основная модель успешно прошла валидацию, запишем ее
                        $model->save(false);

                        // создаем отчеты к договору
                        foreach ($modelReports as $newModel) {
                            // не менять на updateAttributes() потому что модель еще не записана
                            $newModel->mc_id = $model->id;
                            //$newModel->save(false) ? null : $success = false;
                            $newModel->validate(null, false) && $newModel->save(false) ? null : $success = false;
                        }

                        if ($success) $transaction->commit(); else $transaction->rollBack();

                        return $this->redirect(self::ROOT_URL_AS_ARRAY);
                    } else {
                        $transaction->rollBack();
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw new BadRequestHttpException($e->getMessage(), 0, $e);
                }
            }

            $model->crudeReports = $modelReports;
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing EcoMc model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) return $this->redirect(self::ROOT_URL_AS_ARRAY);
        }
        else {
            $model->crudeReports = $this->fetchReports($id);
        }

        return $this->render('update', [
            'model' => $model,
            'newReportModel' => $this->createNewReportModel($id),
        ]);
    }

    /**
     * Deletes an existing EcoMc model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }

    /**
     * Finds the EcoMc model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return EcoMc the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EcoMc::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Вычисляет наименования контрагентов и отдает их в текстовом виде.
     * evaluate-ca-names
     * @param $ids string идентификаторы контрагентов, которых необходимо идентифицировать
     * @return mixed
     */
    public function actionEvaluateCaNames($ids)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        return foCompany::find()->select([
            'id' => 'ID_COMPANY',
            'name' => 'COMPANY_NAME',
        ])->where(['ID_COMPANY' => $ids])->asArray()->all();
    }

    /**
     * Рендерит строку табличной части "Регламентированные отчеты".
     * render-report-row
     * @param integer $counter номер строки по порядку
     * @return mixed
     */
    public function actionRenderReportRow($counter)
    {
        if (Yii::$app->request->isAjax) {
            $model = $this->createNewReportModel();

            return $this->renderAjax('_row_report_fields', [
                'model' => $model,
                'parentModel' => new EcoMc(),
                'form' => new \yii\bootstrap\ActiveForm(),
                'counter' => (intval($counter) + 1),
            ]);
        }

        return false;
    }

    /**
     * Выполняет интерактивное добавление регламентировааного отчета в договор сопровождения.
     * create-report
     * @return mixed
     * @throws \Throwable
     */
    public function actionCreateReport()
    {
        if (Yii::$app->request->isPjax) {
            $model = new EcoMcTp();

            if ($model->load(Yii::$app->request->post())) {
                $exist = EcoMcTp::findOne(['mc_id' => $model->mc_id, 'report_id' => $model->report_id]);
                if (empty($exist)) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if ($model->save()) {
                            $transaction->commit();
                            return $this->renderReports($model->mc_id);
                        }
                    }
                    catch (\Exception $e) {
                        $transaction->rollBack();
                        throw $e;
                    } catch (\Throwable $e) {
                        $transaction->rollBack();
                        throw $e;
                    }
                    $transaction->rollBack();
                }
                else {
                    return $this->renderReports($model->mc_id);
                }
            }
        }

        return false;
    }

    /**
     * Выполняет удаление регламентированного отчета из договора.
     * delete-report
     * @param $id integer идентификатор регламентированного отчета договора, который необходимо удалить
     * @return mixed
     * @throws \Throwable
     */
    public function actionDeleteReport($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = EcoMcTp::findOne($id);
            if ($model) {
                $mc_id = $model->mc_id;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->delete()) {
                        $transaction->commit();
                        return $this->renderReports($mc_id);
                    }
                }
                catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                $transaction->rollBack();
            }
        }
    }

    /**
     * Выполняет интерактивное изменение даты подачи отчета в контролирующий орган.
     * submit-report
     * @return bool
     */
    public function actionSubmitReport()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $crModel = EcoMcTp::findOne(Yii::$app->request->post('id'));
        if ($crModel) {
            $crModel->updateAttributes([
                'date_fact' => Yii::$app->request->post('date'),
            ]);
            return true;
        }

        return false;
    }
}
