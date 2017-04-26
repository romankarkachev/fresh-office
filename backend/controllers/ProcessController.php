<?php

namespace backend\controllers;

use common\models\DirectMSSQLQueries;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use moonland\phpexcel\Excel;
use backend\models\FreightsPaymentsImport;

/**
 * Контроллер для обработок
 */
class ProcessController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['freights-payments'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Выполняет чтение данных из файла Excel, делает выборку проектов, их перебор,
     * и если сумма совпадает, то выполняет обновление поле Оплата рейса в соответствии с прочитанным значением.
     */
    public function actionFreightsPayments()
    {
        $model = new FreightsPaymentsImport();

        if (Yii::$app->request->isPost) {
            // дурацкое действие, но что поделать, когда зубная паста недоступна
            $model->date_payment = Yii::$app->request->post()[$model->formName()]['date_payment'];

            $model->importFile = UploadedFile::getInstance($model, 'importFile');
            $filename = Yii::getAlias('@uploads').'/'.Yii::$app->security->generateRandomString().'.'.$model->importFile->extension;
            if ($model->upload($filename)) {
                $model->load(Yii::$app->request->post());
                // если файл удалось успешно загрузить на сервер
                // выбираем все данные из файла в массив
                $data = Excel::import($filename, [
                    'setFirstRecordAsKeys' => false,
                ]);
                if (count($data) > 0) {
                    // если удалось прочитать, сразу удаляем файл
                    unlink($filename);

                    // берем массив в отдельную переменную, чтобы обработать его и получить в результате
                    // только идентификаторы проектов, которые присутствуют в файле
                    $project_ids = $data;
                    // если первая строка - заголовок таблицы, удалим ее
                    if (!is_numeric($project_ids[1]['A'])) unset($project_ids[1]);
                    // берем только колонку id, раскладываем массив в строку, из строки убираем в конце запятую
                    $project_ids = trim(implode(',', ArrayHelper::getColumn($project_ids, 'A')), ',');
                    $projects = DirectMSSQLQueries::fetchProjectsForDatePayment($project_ids);

                    // перебираем массив и создаем новые элементы
                    $success_records_count = 0; // массив успешно созданных записей
                    $errors_import = array(); // массив для ошибок при импорте
                    $row_number = 1; // 0-я строка - это заголовок
                    foreach ($data as $row) {
                        // проверяем обязательные поля, если первое поле не заполнено, останавливаем процесс
                        if (trim($row['A']) == '') break;

                        // это может быть еще заголовок таблицы, пропускаем его
                        if (!is_numeric(trim($row['A']))) continue;

                        $project_id = trim($row['A']);

                        // приведем в человеческий вид сумму
                        $amount = trim(trim($row['G']));
                        $amount = preg_replace("/[^0-9\.]/", '', $amount);
                        $amount = floatval($amount);

                        $key = array_search($project_id, array_column($projects, 'id'));
                        if ($key !== false) {
                            if ($amount == $projects[$key]['cost']) {
                                if (!DirectMSSQLQueries::updateProjectsAddOplata($project_id, $model->date_payment))
                                    $errors_import[] = 'Не удалось обновить дату оплаты проекта ' . $project_id . '.';
                                else
                                    $success_records_count++;
                            }
                            else
                                $errors_import[] = 'Сумма не совпадает в строке ' . $row_number . ', проект ' . $project_id . ': ' . $amount . ' != ' . $projects[$key]['cost'] . '.';
                        }

                        $row_number++;
                    }; // foreach

                    // зафиксируем ошибки, чтобы показать
                    if (count($errors_import) > 0) {
                        $errors = '';
                        foreach ($errors_import as $error)
                            $errors .= '<p>'.$error.'</p>';
                        Yii::$app->getSession()->setFlash('error', $errors);
                    } else {
                        $addition = '';
                        if ($success_records_count > 0)
                            $addition = ' Обновлено записей: ' . $success_records_count . '.';
                        Yii::$app->getSession()->setFlash('success', 'Импорт завершен.' . $addition);
                    }

                }; // count > 0

                //return $this->redirect(['freights-payments']);
            }
        };

        $model->date_payment = date('Y-m-d');

        return $this->render('freightspayments', [
            'model' => $model,
        ]);
    }
}
