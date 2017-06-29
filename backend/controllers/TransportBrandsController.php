<?php

namespace backend\controllers;

use Yii;
use common\models\TransportBrands;
use common\models\TransportBrandsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * TransportBrandsController implements the CRUD actions for TransportBrands model.
 */
class TransportBrandsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['root'],
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
     * Lists all TransportBrands models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TransportBrandsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new TransportBrands model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TransportBrands();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/transport-brands']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing TransportBrands model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/transport-brands']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing TransportBrands model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/transport-brands']);
    }

    /**
     * Finds the TransportBrands model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TransportBrands the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TransportBrands::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }
    }

    /**
     * Парсит названия марок грузовых автомобилей с сайта-донора.
     * При повторном запуске проверка на дубли не выполняется!
     * Выполнение происходит без дополнительного подтверждения!
     */
    public function actionParseData()
    {
        $url = 'http://gruzovoy.ru/catalog/technic/type/gryzovie_avtomobili_gryzoviki';
        try {
            $html = \keltstr\simplehtmldom\SimpleHTMLDom::file_get_html($url);
            if ($html->innertext != '') {
                $dom = 'div.allbrands > ul > li';
                if (count($html->find($dom)) > 0) {
                    $result = [];
                    // перебираем результат выборки DOM
                    foreach ($html->find($dom) as $element) {
                        $name = $element->plaintext;
                        if (!in_array($name, $result)) {
                            $result[] = $name;
                            $brand = new TransportBrands();
                            $brand->name = $name;
                            $brand->save();
                        }
                    }
                }
            }
        }
        catch (\Exception $exception) {
            echo '<p>Не удалось выполнить запрос!</p>' . $exception;
        }
    }
}
