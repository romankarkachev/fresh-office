<?php

namespace backend\controllers;

use common\models\ProductsImport;
use Yii;
use common\models\Products;
use common\models\ProductsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use moonland\phpexcel\Excel;

/**
 * ProductsController implements the CRUD actions for Products model.
 */
class ProductsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update', 'delete', 'list-nf'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Products models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Creates a new Products model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Products();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/products']);
        } else {
            $model->author_id = Yii::$app->user->id;
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Products model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/products']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Products model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/products']);
    }

    /**
     * Finds the Products model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Products the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Products::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Функция выполняет поиск номенклатуры по части наименования, переданной в параметрах.
     * @param string $q
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionListNf($q, $counter = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = Products::find()->select([
            'id',
            'text' => 'name',
            'fkko',
            'unit',
            $counter . ' AS `counter`',
        ])
            ->limit(100)
            ->andFilterWhere(['like', 'name', $q]);

        return ['results' => $query->asArray()->all()];
    }

    /**
     * Импорт из Excel.
     * @inheritdoc
     */
    public function actionImport()
    {
        $model = new ProductsImport();

        if (Yii::$app->request->isPost) {
            $model->importFile = UploadedFile::getInstance($model, 'importFile');
            $filename = Yii::getAlias('@uploads').'/'.Yii::$app->security->generateRandomString().'.'.$model->importFile->extension;
            if ($model->upload($filename)) {
                $model->load(Yii::$app->request->post());
                // если файл удалось успешно загрузить на сервер
                // выбираем все данные из файла в массив
//                $data = Excel::import($filename, [
//                    'setFirstRecordAsKeys' => true,
//                    'setIndexSheetByName' => false,
//                    'getOnlySheet' => 0,
//                ]);
                $data = Excel::import($filename);
                if (count($data) > 0) {
                    // выборка существующих позиций номенклатуры для исключения создания дубликатов
                    // в процессе выполнения цикла пополняется
                    $exists_nom = Products::find()->select('name')->orderBy('name')->column();

                    // перебираем массив и создаем новые элементы
                    $errors_import = array(); // массив для ошибок при импорте
                    $row_number = 1; // 0-я строка - это заголовок
                    foreach ($data as $row) {
                        // проверяем обязательные поля, если хоть одно не заполнено, пропускаем строку
                        switch ($model->type) {
                            case ProductsImport::PRODUCT_TYPE_WASTE:
                                if (trim($row['id']) == '' || trim($row['date']) == '' || trim($row['fkko']) == '' || trim($row['name']) == '') {
                                    $errors_import[] = 'В строке '.$row_number.' одно из обязательных полей не заполнено!';
                                    $row_number++;
                                    continue;
                                }
                                break;
                            case ProductsImport::PRODUCT_TYPE_PRODUCT:
                                if (trim($row['id']) == '' || trim($row['name']) == '') {
                                    $errors_import[] = 'В строке '.$row_number.' одно из обязательных полей не заполнено!';
                                    $row_number++;
                                    continue;
                                }
                                break;
                        }

                        // преобразуем наименование в человеческий вид
                        $name = trim($row['name']);
                        $name = str_replace(chr(194).chr(160), '', $name);
                        $name = str_replace('   ', ' ', $name);
                        $name = str_replace('  ', ' ', $name);
                        //$name = mb_strtolower($name);
                        //$name = ProductsImport::ucFirstRu($name);

                        // проверка на существование
                        if (in_array($name, $exists_nom)) {
                            $errors_import[] = 'Обнаружен дубликат: ' . $name . '. Пропущен.';
                            $row_number++;
                            //continue;
                        }

                        // пустые наименования и бессмысленные пропускаем
                        if ($name == '' || $name == '...') {
                            $row_number++;
                            continue;
                        }

                        $new_record = new Products();
                        $new_record->type = $model->type;
                        $new_record->author_id = Yii::$app->user->id;
                        $new_record->is_deleted = 0;
                        if ($new_record->type == ProductsImport::PRODUCT_TYPE_WASTE) {
                            $new_record->fo_id = trim($row['id']);
                            $new_record->fkko_date = trim($row['date']);
                            $new_record->fo_name = trim($row['name']);

                            $new_record->fo_fkko = trim($row['fkko']);
                            $fkko = $row['fkko'];
                            $fkko = str_replace(chr(194).chr(160), '', $fkko);
                            $fkko = str_replace(' ', '', $fkko);
                            $new_record->fkko = $fkko;

                            $new_record->name = $name;

                            // класс опасности
                            $new_record->dc = ProductsImport::DangerClassRep(substr(trim($fkko), -1));
                        } elseif ($new_record->type == ProductsImport::PRODUCT_TYPE_PRODUCT) {
                            // единица измерения
                            $new_record->unit = trim($row['unit']);
                            // класс опасности
                            $new_record->dc = ProductsImport::DangerClassRep(trim($row['dc']));
                            // способ утилизации
                            $new_record->uw = trim($row['uw']);
                            $new_record->name = $name;
                        }

                        if (!$new_record->save()) {
                            $details = '';
                            foreach ($new_record->errors as $error)
                                foreach ($error as $detail)
                                    $details .= '<p>'.$detail.'</p>';
                            $errors_import[] = 'В строке '.$row_number.' не удалось сохранить новый элемент.'.$details;
                        }
                        else $exists_nom[] = $new_record->name;

                        $row_number++;
                    }; // foreach

                    // зафиксируем ошибки, чтобы показать
                    if (count($errors_import) > 0) {
                        $errors = '';
                        foreach ($errors_import as $error)
                            $errors .= '<p>'.$error.'</p>';
                        Yii::$app->getSession()->setFlash('error', $errors);
                    };

                }; // count > 0

                // удаляем файл
                unlink($filename);

                return $this->redirect(['/products']);
            }
        };

        $model->type = 1;
        return $this->render('import', [
            'model' => $model,
        ]);
    }
}
