<?php

namespace backend\controllers;

use Yii;
use common\models\TenderFormsVarieties;
use common\models\TenderFormsVarietiesSearch;
use common\models\TenderFormsKinds;
use common\models\TenderFormsKindsSearch;
use common\models\TenderFormsVarietiesKinds;
use common\models\TenderFormsVarietiesKindsSearch;
use common\models\TenderFormsKindsFields;
use common\models\TenderFormsKindsFieldsSearch;
use common\models\TenderFormsTemplateForm;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * TenderFormsController implements the CRUD actions for TenderFormsVarieties model.
 */
class TenderFormsController extends Controller
{
    /**
     * Корневой URL, общий для всех
     */
    const URL_ROOT = 'tender-forms';

    /**
     * Разновидности пакетов форм
     */
    // URL, применяемые для сортировки и постраничного перехода
    const URL_VARIETIES_INDEX = 'varieties';
    const URL_VARIETIES_INDEX_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_VARIETIES_INDEX];
    // Название списка записей разновидностей
    const LABEL_VARIETIES = 'Разновидности пакетов форм';
    // Ссылка в хлебных крошках на список записей
    const BREADCRUMBS_VARIETIES_INDEX = [
        ['label' => ReferencesController::MAIN_MENU_LABEL, 'url' => ReferencesController::ROOT_URL_AS_ARRAY],
        self::LABEL_VARIETIES,
    ];
    const BREADCRUMBS_VARIETIES = [
        ['label' => ReferencesController::MAIN_MENU_LABEL, 'url' => ReferencesController::ROOT_URL_AS_ARRAY],
        ['label' => self::LABEL_VARIETIES, 'url' => self::URL_VARIETIES_INDEX_AS_ARRAY],
    ];
    // URL страницы создания новой разновидности набора форм
    const URL_CREATE_VARIETY = 'create-variety';
    const URL_CREATE_VARIETY_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_CREATE_VARIETY];
    // URL страницы редактирования разновидности набора форм
    const URL_UPDATE_VARIETY = 'update-variety';
    const URL_UPDATE_VARIETY_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_UPDATE_VARIETY];
    // URL страницы удаления разновидности набора форм
    const URL_DELETE_VARIETY = 'delete-variety';
    const URL_DELETE_VARIETY_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_DELETE_VARIETY];
    // URL для AJAX-валидации добавления новой формы в набор
    const URL_ADD_VALIDATE_VK = 'validate-vk';
    const URL_ADD_VALIDATE_VK_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_ADD_VALIDATE_VK];
    // URL для обработки формы добавления новой формы
    const URL_ADD_VARIETY_KIND = 'add-variety-kind';
    const URL_ADD_VARIETY_KIND_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_ADD_VARIETY_KIND];
    // URL для удаления формы
    const URL_DELETE_VARIETY_KIND = 'delete-variety-kind';
    const URL_DELETE_VARIETY_KIND_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_DELETE_VARIETY_KIND];
    // URL страницы обновления шаблона формы
    const URL_UPDATE_VK_TEMPLATE = 'update-vk-template';
    const URL_UPDATE_VK_TEMPLATE_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_UPDATE_VK_TEMPLATE];


    /**
     * Формы
     */
    // URL, применяемые для сортировки и постраничного перехода
    const URL_KINDS_INDEX = 'kinds';
    const URL_KINDS_INDEX_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_KINDS_INDEX];
    // Название списка записей форм
    const LABEL_KINDS = 'Формы для тендеров';
    // Ссылка в хлебных крошках на список записей
    const BREADCRUMBS_KINDS_INDEX = [
        ['label' => ReferencesController::MAIN_MENU_LABEL, 'url' => ReferencesController::ROOT_URL_AS_ARRAY],
        self::LABEL_KINDS,
    ];
    const BREADCRUMBS_KINDS = [
        ['label' => ReferencesController::MAIN_MENU_LABEL, 'url' => ReferencesController::ROOT_URL_AS_ARRAY],
        ['label' => self::LABEL_KINDS, 'url' => self::URL_KINDS_INDEX_AS_ARRAY],
    ];
    // URL страницы создания новой формы
    const URL_CREATE_KIND = 'create-kind';
    const URL_CREATE_KIND_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_CREATE_KIND];
    // URL страницы редактирования формы
    const URL_UPDATE_KIND = 'update-kind';
    const URL_UPDATE_KIND_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_UPDATE_KIND];
    // URL страницы удаления формы
    const URL_DELETE_KIND = 'delete-kind';
    const URL_DELETE_KIND_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_DELETE_KIND];
    // URL для AJAX-валидации добавления нового поля в форму
    const URL_ADD_VALIDATE_KF = 'validate-kf';
    const URL_ADD_VALIDATE_KF_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_ADD_VALIDATE_KF];
    // URL для обработки формы добавления нового поля в форму
    const URL_ADD_KIND_FIELD = 'add-kind-field';
    const URL_ADD_KIND_FIELD_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_ADD_KIND_FIELD];
    // URL для интерактивного удаления поля из формы
    const URL_DELETE_KIND_FIELD = 'delete-kind-field';
    const URL_DELETE_KIND_FIELD_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_DELETE_KIND_FIELD];

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
                            // разновидности наборов форм
                            self::URL_VARIETIES_INDEX, self::URL_CREATE_VARIETY, self::URL_UPDATE_VARIETY, self::URL_DELETE_VARIETY,
                            self::URL_ADD_VALIDATE_VK, self::URL_ADD_VARIETY_KIND, self::URL_DELETE_VARIETY_KIND,
                            self::URL_UPDATE_VK_TEMPLATE,
                            // формы
                            self::URL_KINDS_INDEX, self::URL_CREATE_KIND, self::URL_UPDATE_KIND, self::URL_DELETE_KIND,
                            // поля
                            self::URL_ADD_VALIDATE_KF, self::URL_ADD_KIND_FIELD, self::URL_DELETE_KIND_FIELD,
                        ],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    self::URL_DELETE_VARIETY => ['POST'],
                    self::URL_DELETE_KIND => ['POST'],
                    self::URL_DELETE_VARIETY_KIND => ['POST'],
                    self::URL_DELETE_KIND_FIELD => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Возвращает набор форм для переданной в параметрах разновидности.
     * @param $id integer идентификатор разновидности, формы которой отбираются
     * @return \yii\data\ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchVarietiesKinds($id)
    {
        $searchModel = new TenderFormsVarietiesKindsSearch();
        $result = $searchModel->search([
            $searchModel->formName() => [
                'variety_id' => $id,
            ],
        ]);
        $result->pagination = false;
        $result->sort = false;

        return $result;
    }

    /**
     * Возвращает новую модель единицы набора форм с заданной разновидностью.
     * @param $variety_id integer разновидность, к которой присоединяется форма
     * @return TenderFormsVarietiesKinds
     */
    private function createNewVkModel($variety_id)
    {
        return new TenderFormsVarietiesKinds([
            'variety_id' => $variety_id,
        ]);
    }

    /**
     * Lists all TenderFormsVarieties models.
     * @return mixed
     */
    public function actionVarieties()
    {
        $searchModel = new TenderFormsVarietiesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('varieties/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new TenderFormsVarieties model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreateVariety()
    {
        $model = new TenderFormsVarieties();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(ArrayHelper::merge(self::URL_VARIETIES_INDEX_AS_ARRAY, ['id' => $model->id]));
        }

        return $this->render('varieties/create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TenderFormsVarieties model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdateVariety($id)
    {
        $model = $this->findVarietyModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::URL_VARIETIES_INDEX_AS_ARRAY);
        }

        return $this->render('varieties/update', [
            'model' => $model,
            'dpKinds' => $this->fetchVarietiesKinds($id),
            'newVkModel' => $this->createNewVkModel($id),
        ]);
    }

    /**
     * Deletes an existing TenderFormsVarieties model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteVariety($id)
    {
        $this->findVarietyModel($id)->delete();

        return $this->redirect(self::URL_VARIETIES_INDEX_AS_ARRAY);
    }

    /**
     * Finds the TenderFormsVarieties model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TenderFormsVarieties the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findVarietyModel($id)
    {
        if (($model = TenderFormsVarieties::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    /**
     * AJAX-валидация формы добавления в набор.
     */
    public function actionValidateVk()
    {
        $model = new TenderFormsVarietiesKinds();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return Json::encode(\yii\widgets\ActiveForm::validate($model));
        }
    }

    /**
     * Выполняет интерактивное добавление формы набор к разновидности.
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAddVarietyKind()
    {
        if (Yii::$app->request->isPjax) {
            $model = new TenderFormsVarietiesKinds();

            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    return $this->render('varieties/vk_list', [
                        'dataProvider' => $this->fetchVarietiesKinds($model->variety_id),
                        'model' => $this->createNewVkModel($model->variety_id),
                        'action' => self::URL_ADD_VARIETY_KIND,
                    ]);
                }
                else {
                    return var_dump($model->errors);
                }
            }
        }

        return false;
    }

    /**
     * Выполняет удаление формы из набора.
     * @param $id integer идентификатор привязки, которую надо удалить
     * @return mixed
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteVarietyKind($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = TenderFormsVarietiesKinds::findOne($id);
            if ($model) {
                $variety_id = $model->variety_id;
                $model->delete();

                return $this->render('varieties/vk_list', [
                    'dataProvider' => $this->fetchVarietiesKinds($variety_id),
                    'model' => $this->createNewVkModel($variety_id),
                    'action' => self::URL_ADD_VARIETY_KIND,
                ]);
            }
        }

        return false;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function actionUpdateVkTemplate($id)
    {
        $model = new TenderFormsTemplateForm();
        $model->vk_id = $id;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $vk = $model->vk;
                if ($vk) {
                    $model->file = UploadedFile::getInstance($model, 'file');
                    if (!empty($model->file)) {
                        $uploadDir = TenderFormsVarietiesKinds::getUploadsFilepath($vk->variety_id);
                        $fn = $vk->kind_id . '.' . $model->file->extension;
                        $ffp = $uploadDir . '/' . $fn;
                        if ($model->file->saveAs($ffp)) {
                            Yii::$app->session->setFlash('success', 'Файл шаблона успешно обновлен.');
                            return $this->redirect(ArrayHelper::merge(self::URL_UPDATE_VK_TEMPLATE_AS_ARRAY, ['id' => $id]));
                        }
                        else {
                            // удаляем загруженный файл, возвратится false
                            unlink($ffp);
                            Yii::$app->session->setFlash('error', 'Не удалось обновить файл шаблона.');
                        }
                    }
                }
            }
        }

        return $this->render('varieties/template', ['model' => $model]);
    }

    /**
     * Возвращает набор полей переданной в параметрах формы.
     * @param $id integer идентификатор формы, поля которой отбираются
     * @return \yii\data\ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchKindFields($id)
    {
        $searchModel = new TenderFormsKindsFieldsSearch();
        $result = $searchModel->search([
            $searchModel->formName() => [
                'kind_id' => $id,
            ],
        ]);
        $result->pagination = false;
        $result->sort = false;

        return $result;
    }

    /**
     * Возвращает новую модель поля с заданной формой.
     * @param $id integer форма, к которой добавляется поле
     * @return TenderFormsKindsFields
     */
    private function createNewFieldModel($id)
    {
        return new TenderFormsKindsFields([
            'kind_id' => $id,
        ]);
    }

    /**
     * Lists all TenderFormsKinds models.
     * @return mixed
     */
    public function actionKinds()
    {
        $searchModel = new TenderFormsKindsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('kinds/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new TenderFormsKinds model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreateKind()
    {
        $model = new TenderFormsKinds();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(ArrayHelper::merge(self::URL_KINDS_INDEX_AS_ARRAY, ['id' => $model->id]));
        }

        return $this->render('kinds/create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TenderFormsKinds model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdateKind($id)
    {
        $model = $this->findKindModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(self::URL_KINDS_INDEX_AS_ARRAY);
        }

        return $this->render('kinds/update', [
            'model' => $model,
            'dpFields' => $this->fetchKindFields($id),
            'newFieldModel' => $this->createNewFieldModel($id),
        ]);
    }

    /**
     * Deletes an existing TenderFormsKinds model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteKind($id)
    {
        $this->findKindModel($id)->delete();

        return $this->redirect(self::URL_KINDS_INDEX_AS_ARRAY);
    }

    /**
     * Finds the TenderFormsKinds model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TenderFormsKinds the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findKindModel($id)
    {
        if (($model = TenderFormsKinds::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    /**
     * AJAX-валидация формы добавления поля в форму для тендера.
     */
    public function actionValidateKf()
    {
        $model = new TenderFormsKindsFields();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return Json::encode(\yii\widgets\ActiveForm::validate($model));
        }
    }

    /**
     * Выполняет интерактивное добавление поля в форму для тендера.
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAddKindField()
    {
        if (Yii::$app->request->isPjax) {
            $model = new TenderFormsKindsFields();

            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    return $this->render('kinds/_fields_list', [
                        'dataProvider' => $this->fetchKindFields($model->kind_id),
                        'model' => $this->createNewFieldModel($model->kind_id),
                        'action' => self::URL_ADD_KIND_FIELD,
                    ]);
                }
                else {
                    return var_dump($model->errors);
                }
            }
        }

        return false;
    }

    /**
     * Выполняет удаление поля из формы для тендера.
     * @param $id integer идентификатор поля, которое надо удалить
     * @return mixed
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteKindField($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = TenderFormsKindsFields::findOne($id);
            if ($model) {
                $kind_id = $model->kind_id;
                $model->delete();

                return $this->render('kinds/_fields_list', [
                    'dataProvider' => $this->fetchKindFields($kind_id),
                    'model' => $this->createNewFieldModel($kind_id),
                    'action' => self::URL_ADD_KIND_FIELD,
                ]);
            }
        }

        return false;
    }
}
