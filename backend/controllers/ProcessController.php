<?php

namespace backend\controllers;

use common\models\foManagers;
use common\models\Profile;
use common\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use moonland\phpexcel\Excel;
use backend\models\FreightsPaymentsImport;
use common\models\ClosingMilestonesForm;
use common\models\DirectMSSQLQueries;
use common\models\ReportCaDuplicates;
use common\models\foCompany;
use common\models\ReplacePasswordsForm;

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
                        'actions' => ['freights-payments', 'closing-milestones', 'merge-customers', 'replace-passwords'],
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

    /**
     * Отображает форму закрытия этапов в проектах.
     * @return mixed
     */
    public function actionClosingMilestones()
    {
        $model = new ClosingMilestonesForm();

        if ($model->load(Yii::$app->request->post())) {
            $count = $model->executeClosing();
            Yii::$app->session->setFlash('info', 'Успешно закрыто этапов: ' . $count . '.');
            return $this->redirect(['/process/closing-milestones']);
        }

        return $this->render('closing_milestones', ['model' => $model]);
    }

    /**
     * Выполняет замену значения в поле "Контрагент" таблицы, наименование которой передается в параметрах.
     * @param $tableName
     * @param $oldCompanyId
     * @param $newCompanyId
     * @return mixed
     */
    public static function replaceCompany($tableName, $oldCompanyId, $newCompanyId)
    {
        return Yii::$app->db_mssql->createCommand()->update($tableName, [
            'ID_COMPANY' => $newCompanyId,
        ], [
            'ID_COMPANY' => $oldCompanyId,
        //])->getRawSql();
        ])->execute();
    }

    /**
     * Отображает форму объединения карточек проектов.
     * @return mixed
     * @throws BadRequestHttpException если пользователь просто запрашивает эту страницу, без параметров
     */
    public function actionMergeCustomers()
    {
        if (Yii::$app->request->isPost) {
            $customers = Yii::$app->request->post('MergeCustomers');
            if (count($customers) > 0) {
                $newCompany = foCompany::findOne(intval(Yii::$app->request->post('radioButtonSelection')));
                if (!empty($newCompany)) {
                    // главный контрагент должен существовать

                    // массив E-mail адресов менеджеров, которые должны получить, сразу поместим в него менеджера главной карточки:
                    $managersToNotify = ['at@st77.ru', $newCompany->managerEmail];

                    // примечания складываются в отдельный массив, а после переноса дописываются в примечание главного контрагента
                    $comments = [];

                    $result[$newCompany->ID_COMPANY] = [
                        'id' => $newCompany->ID_COMPANY,
                        'name' => $newCompany->COMPANY_NAME,
                        'active' => true,
                        'actions' => [
                            'Данный контрагент отмечен как основной. В эту карточку будут перенесены другие данные.',
                        ],
                    ];
                    foreach ($customers as $customer) {
                        // главного сразу пропускаем
                        if ($newCompany->ID_COMPANY == $customer) continue;

                        // выполняем перебор в цикле, на каждой итерации выполняем перенос данных
                        $model = foCompany::findOne($customer);
                        $result[$customer] = [
                            'id' => $customer,
                            'name' => $model->COMPANY_NAME,
                        ];

                        // фиксируем примечание, если оно есть, потом добавим в главную карточку
                        if (!empty($model->DOP_INF)) $comments[] = trim($model->DOP_INF);

                        // в данном цикле производится перенос данных, список затрагиваемых таблиц в массиве:
                        foreach (ReportCaDuplicates::fetchCompanyReplaceChapters() as $chapter) {
                            if ($chapter['active']) {
                                // перенос по этой таблице разрешен, сделаем запись в логах:
                                $result[$customer]['actions'][] = $chapter['actionRep'];
                                foreach ($chapter['tableNames'] as $tableName) {
                                    $result[$customer]['actions'][] = 'Замена в таблице ' . $tableName . ', строк затронуто: ' .
                                        self::replaceCompany($tableName, intval($customer), $newCompany->ID_COMPANY) . '.';
                                }
                            }
                        }

                        // помечаем на удаление текущего контрагента
                        if ($model->updateAttributes([
                            'DATE_TRASH' => new \yii\db\Expression('GETDATE()'),
                            'TRASH' => true
                        ]) == 1) {
                            $result[$customer]['actions'][] = 'Контрагент помечен на удаление.';
                            // отправим E-mail ответственному менеджеру о том, что необходимо быть внимательнее
                            if (!empty($model->managerEmail) && !in_array(trim($model->managerEmail), $managersToNotify)) {
                                $managersToNotify[] = trim($model->managerEmail);
                            }
                        }
                    }

                    // обновим примечания
                    if (count($comments) > 0) {
                        $oneBigComment = $newCompany->DOP_INF . chr(13);
                        foreach ($comments as $comment) $oneBigComment .= chr(13) . $comment;
                        $newCompany->updateAttributes(['DOP_INF' => $oneBigComment]);
                    }

                    try {
                        // один раз подготовим письмо
                        $letter = Yii::$app->mailer->compose([
                            'html' => 'mergeCustomersAccountingQuality-html',
                        ], [
                            'headliner' => $newCompany,
                            'customersAffected' => $result,
                        ])
                            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderIvanIV']])
                            ->setSubject('Обратите внимание на качество ведения учета в CRM!');

                        /*
                        $letter->setTo('post@romankarkachev.ru');
                        $letter->send();
                        */

                        // и несколько раз его отправим
                        foreach ($managersToNotify as $receiver) {
                            $letter->setTo($receiver);
                            $letter->send();
                        }
                    }
                    catch (\Exception $exception) {}

                    return $this->render('merge_customers_result', [
                        'runtimeLog' => $result,
                    ]);
                }
            };
        }
        else {
            $field = Yii::$app->request->get('field');
            $criteria = Yii::$app->request->get('criteria');
            if (!empty($field)) {
                $searchModel = new ReportCaDuplicates();
                $dataProvider = $searchModel->searchDuplicates($field, $criteria);
                return $this->render('merging_customers', [
                    'dataProvider' => $dataProvider,
                    'field' => $field,
                    'criteria' => $criteria,
                ]);
            }
            else throw new BadRequestHttpException('Инструментом можно пользоваться только из отчета по дубликатам контрагентов.');
        }
    }

    /**
     * @return mixed
     */
    public function actionReplacePasswords()
    {
        if (Yii::$app->request->isPost) {
            $selection = Yii::$app->request->post('selection');
            $usersAffected = [];
            foreach (Yii::$app->request->post('ReplaceUsersPasswords') as $key => $item) {
                if (in_array($key, $selection)) {
                    $user = User::findOne($key);
                    if ($user) {
                        $user->password = $item;
                        $user->save();

                        if (!empty($user->profile->fo_id)) {
                            foManagers::updateAll(['PASWORD' => $item], ['ID_MANAGER' => $user->profile->fo_id]);
                        }

                        $usersAffected[] = [
                            'id' => $key,
                            'fo_id' => $user->profile->fo_id,
                            'login' => $user->username,
                            'name' => $user->profile->name,
                            'password' => $item,
                        ];
                    }
                }
            }

            $letter = Yii::$app->mailer->compose([
                'html' => 'replaceUsersPasswords-html',
            ], [
                'iterator' => 1,
                'usersAffected' => $usersAffected,
            ])
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderSaveliy']])
                ->setTo(Yii::$app->params['receiverEmail'])
                ->setSubject('Новые пароли пользователей');

            if ($letter->send()) Yii::$app->session->setFlash('success', 'Письмо с новыми паролями успешно отправлено. Пользователей затронуто: ' . count($usersAffected) . '.');
        }

        $model = new ReplacePasswordsForm();
        $model->passwordLength = 6;
        return $this->render('replace_passwords', [
            'model' => $model,
            'dataProvider' => $model->search(Yii::$app->request->queryParams),
        ]);
    }
}
