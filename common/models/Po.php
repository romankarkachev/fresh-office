<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use backend\controllers\AdvanceReportsController;
use backend\controllers\PoController;

/**
 * This is the model class for table "po".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property int $is_deleted Пометка удаления
 * @property int $state_id Текущий статус
 * @property int $company_id Контрагент
 * @property int $ei_id Статья расходов
 * @property string $amount Сумма
 * @property int $approved_at Дата и время согласования ордера
 * @property int $paid_at Дата и время оплаты
 * @property int $fo_project_id ID проекта из CRM Fresh Office
 * @property int $fo_ca_id ID контрагента из CRM Fresh Office
 * @property string $comment Комментарий
 *
 * @property string $modelRep
 * @property string $createdByProfileName
 * @property string $createdByProfileEmail
 * @property string $stateName
 * @property string $companyName
 * @property string $eiName
 * @property string $eiRepHtml
 * @property string $eiRep
 *
 * @property PoEi $ei
 * @property PoEig $eiGroup
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property AuthAssignment $createdByRoles
 * @property PaymentOrdersStates $state
 * @property Companies $company
 * @property PoFiles[] $poFiles
 * @property PoStatesHistory[] $poStatesHistories
 * @property PoPop[] $poPops
 */
class Po extends \yii\db\ActiveRecord
{
    const DOM_IDS  = [
        'FORM_PO_ID' => 'frmPo',
        'BUTTON_REJECT_ID' => 'btnReject',
        'BUTTON_SUBMIT_REJECT_ID' => 'btnSubmitReject',
        'btnApproveAdvanceReportId' => 'btnApproveAdvanceReport',
        'btnRejectAdvanceReportId' => 'btnRejectAdvanceReport',
        'btnApproveAdvanceReportLabel' => '<i class="fa fa-check-circle" aria-hidden="true"></i> Принять',
        'btnRejectAdvanceReportLabel' => '<i class="fa fa-times" aria-hidden="true"></i> Отклонить',
    ];

    /**
     * Информационное предупреждение о том, что статья расходов не содержит приаттаченных свойств
     */
    const PROMPT_EMPTY_PROPERTIES = 'Свойства не назначены.';

    /**
     * @var array массив свойств и значений свойств статьи расходов
     */
    public $propertiesValues;

    /**
     * @var array массив файлов при создании авансового отчета
     */
    public $files;

    /**
     * @var EcoProjects проект по экологии
     */
    public $ep;

    /**
     * @var boolean признак, являлся ли платежный ордер авансовым отчетов в свое время (разумеется, виртуальное поле)
     */
    public $isAdvancedReportInThePast;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'po';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['state_id', 'company_id', 'ei_id'], 'required'],
            [['created_at', 'created_by', 'state_id', 'company_id', 'ei_id', 'approved_at', 'paid_at', 'fo_project_id', 'fo_ca_id', 'ep'], 'integer'],
            ['is_deleted', 'boolean'],
            [['amount'], 'number'],
            [['comment'], 'string'],
            [['propertiesValues', 'files'], 'safe'],
            [['comment'], 'default', 'value' => null],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Companies::class, 'targetAttribute' => ['company_id' => 'id']],
            [['ei_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoEi::class, 'targetAttribute' => ['ei_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentOrdersStates::class, 'targetAttribute' => ['state_id' => 'id']],
            // собственные правила валидации
            ['state_id', 'validateState'],
            ['ei_id', 'validateEi'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'created_by' => 'Автор создания',
            'is_deleted' => 'Пометка удаления',
            'state_id' => 'Статус',
            'company_id' => 'Контрагент',
            'ei_id' => 'Статья расходов',
            'amount' => 'Сумма',
            'approved_at' => 'Дата и время согласования ордера',
            'paid_at' => 'Дата и время оплаты',
            'fo_project_id' => 'ID проекта из CRM Fresh Office',
            'fo_ca_id' => 'Контрагент из CRM Fresh Office',
            'comment' => 'Комментарий',
            // виртуальные поля
            'files' => 'Файлы',
            'ep' => 'Проект по экологии',
            // вычисляемые поля
            'createdByProfileName' => 'Автор создания',
            'stateName' => 'Текущий статус',
            'companyName' => 'Контрагент',
            'eiName' => 'Статья расходов',
            'eiRepHtml' => 'Статья расходов',
            'eiRep' => 'Статья расходов',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
                'preserveNonEmptyValues' => true,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            (new PoStatesHistory([
                'po_id' => $this->id,
                'state_id' => $this->state_id,
                'description' => 'Объект создан в статусе ' . $this->stateName,
            ]))->save();
        }
        else {
            if (isset($changedAttributes['state_id'])) {
                // проверим, изменился ли статус платежного ордера
                if ($changedAttributes['state_id'] != $this->state_id) {
                    switch ($this->state_id) {
                        case PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН:
                            // ордер утвержден, отметим это в соответствующем поле
                            if (empty($this->approved_at)) {
                                $this->updateAttributes(['approved_at' => time()]);
                            }

                            break;
                        case PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН:
                            $paidAt = time();
                            // статус изменился на "Оплачено", проставим текущее время в соответствующее поле
                            if (empty($this->paid_at)) {
                                $this->updateAttributes(['paid_at' => $paidAt]);
                            }

                            // если ордер переведен в статус "Оплачено"
                            // ставим в CRM дату оплаты по проекту, если таковой задан
                            if (!empty($this->fo_project_id)) {
                                DirectMSSQLQueries::updateProjectsAddOplata($this->fo_project_id, Yii::$app->formatter->asDate($paidAt, 'php:Y-m-d'));
                            }

                            // если ордер переведен в статус "Оплачено" из статуса "Авансовый отчет", то необходимо
                            // скорректировать сумму взаимаорсчетов с подотчетным лицом
                            if ($changedAttributes['state_id'] == PaymentOrdersStates::PAYMENT_STATE_АВАНСОВЫЙ_ОТЧЕТ) {
                                // создаем финансовую транзакцию
                                (new FinanceTransactions([
                                    'user_id' => $this->created_by,
                                    'operation' => FinanceTransactions::OPERATION_АВАНСОВЫЙ_ОТЧЕТ,
                                    'amount' => $this->amount,
                                    //'src_id' => '',
                                    'comment' => 'Авансовый отчет № ' . $this->id . ' от ' . Yii::$app->formatter->asDate($this->created_at, 'php:d.m.Y г.'),
                                ]))->save();
                            }

                            // для платежных ордеров, в которых пользователь отметил выбрал статью расходов "Благодарности" и
                            // контрагента, создаем запись в финансах данного контрагента
                            if ($this->ei_id == PoEi::СТАТЬЯ_БЛАГОДАРНОСТИ && !empty($this->fo_ca_id)) {
                                $manager_id = null;
                                try {
                                    if (!empty(Yii::$app->user->identity->profile)) {
                                        $manager_id = Yii::$app->user->identity->profile->fo_id;
                                    }
                                }
                                catch (\Exception $e) {}
                                (new foFinances([
                                    'ID_COMPANY' => $this->fo_ca_id,
                                    'ID_PRIZNAK_MANY' => 1,
                                    'ID_SUB_PRIZNAK_MANY' => FreshOfficeAPI::FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ,
                                    'ID_NAPR' => FreshOfficeAPI::FINANCES_DIRECTION_РАСХОД,
                                    'ID_VALUTA' => 118,
                                    'SUM_VALUTA' => $this->amount,
                                    'SUM_RUB' => $this->amount,
                                    'DATE_MANY' => Yii::$app->formatter->asDate($this->created_at, 'php:Y-m-d H:i:s'),
                                    'ID_MANAGER' => $manager_id,
                                ]))->save();
                                unset($manager_id);
                            }

                            break;
                        case PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ:
                            // статус изменился на "Отказ", отправим автору ордера соответствующее уведомление
                            $receiver = $this->createdByProfileEmail;
                            if (!empty($receiver)) {
                                $letter = Yii::$app->mailer->compose([
                                    'html' => 'poReject-html',
                                ], ['model' => $this])
                                    ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                                    ->setTo($receiver)
                                    ->setSubject('По платежному ордеру получен отказ');
                                $letter->send();
                            }
                            unset($receiver);

                            break;
                    }

                    $oldStateName = '';
                    $oldState = PaymentOrdersStates::findOne($changedAttributes['state_id']);
                    if ($oldState != null) $oldStateName = ' с ' . $oldState->name;

                    (new PoStatesHistory([
                        'created_by' => Yii::$app->user->id,
                        'po_id' => $this->id,
                        'state_id' => $this->state_id,
                        'description' => 'Изменение статуса' . $oldStateName . ' на ' . $this->stateName,
                    ]))->save();
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением объекта

            // удаляем записи в журнале событий
            PoStatesHistory::deleteAll(['po_id' => $this->id]);

            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = PoFiles::find()->where(['po_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            // удаляем свойства и значения статьи расходов
            PoPop::deleteAll(['po_id' => $this->id]);

            // удаляем привязку проекта по экологии
            PoEp::deleteAll(['po_id' => $this->id]);

            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function validateState()
    {
        if ($this->state_id == PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ && empty($this->comment)) {
            $this->addError('comment', 'Заполнение этого поля обязательно!');
        }

        if (
            !$this->isNewRecord && $this->oldAttributes['state_id'] == PaymentOrdersStates::PAYMENT_STATE_АВАНСОВЫЙ_ОТЧЕТ &&
            $this->state_id == PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН &&
            empty($this->paid_at)
        ) {
            $this->addError('paid_at', 'Заполнение этого поля обязательно!');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function validateEi()
    {
        // условие изменено ввиду ошибочности интерпретации и рассогласованности
        //if (!empty($this->ei_id) && ($this->ei->group_id == PoEig::ГРУППА_ТРАНСПОРТ) && empty($this->fo_project_id)) {
        if (!empty($this->ei_id) && ($this->ei_id == PoEi::СТАТЬЯ_ПЕРЕВОЗЧИКИ) && empty($this->fo_project_id)) {
            // если выбрана статья расходов из группы "Транспорт", то заполнение поля "ID проекта из CRM Fresh Office" обязательно
            $this->addError('fo_project_id', 'Заполнение этого поля обязательно!');
        }
    }

    /**
     * Рендерит необходимый комплект кнопок для управления статусом платежного ордера в его форме.
     * @return mixed
     */
    public function renderSubmitButtons()
    {
        $controlButtons = [
            'index' => Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . PoController::ROOT_LABEL, PoController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']),
            'back' => Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . PoController::ROOT_LABEL, (!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : PoController::ROOT_URL_AS_ARRAY), ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']),
            'ar_index' => Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . AdvanceReportsController::ROOT_LABEL, (!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : AdvanceReportsController::URL_ROOT_AS_ARRAY), ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']),
            'create' => Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']),
            'save' => Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']),
            'history' => Html::a('<i class="fa fa-history"></i> История', ['#block-logs'], ['class' => 'btn btn-default btn-lg', 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'block-logs']),
            'claim' => Html::submitButton('Сохранить и отправить на согласование <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>', ['class' => 'btn btn-success btn-lg', 'name' => 'order_ready', 'title' => 'Создать и сразу отправить на согласование']),
            'approve' => Html::submitButton('Согласовать', ['class' => 'btn btn-success btn-lg', 'name' => 'order_approve', 'title' => 'Согласовать и сразу отправить на оплату']),
            'reject' => Html::submitButton('<i class="fa fa-times" aria-hidden="true"></i> Отказать', ['id' => self::DOM_IDS['BUTTON_REJECT_ID'], 'class' => 'btn btn-danger btn-lg', 'name' => 'order_reject', 'title' => 'Отказать в согласовании (обязательно нужно будет указать причину согласования)']),
            'paid' => Html::submitButton('Оплачено', ['class' => 'btn btn-success btn-lg', 'name' => 'order_paid', 'title' => 'Установить признак "Оплачено"']),
            'repeat' => Html::submitButton('<i class="fa fa-refresh" aria-hidden="true"></i> Подать повторно', ['class' => 'btn btn-default btn-lg', 'name' => 'order_repeat', 'title' => 'Подать отклоненный ордер на согласование повторно']),
            'draft' => Html::submitButton('<i class="fa fa-undo" aria-hidden="true"></i> Вернуть в черновики', ['class' => 'btn btn-default btn-lg', 'name' => 'order_repeat', 'title' => 'Вернуть ордер в черновики']),
            'ar_approve' => Html::submitButton(self::DOM_IDS['btnApproveAdvanceReportLabel'], ['id' => self::DOM_IDS['btnApproveAdvanceReportId'], 'class' => 'btn btn-success btn-lg', 'name' => 'ar_approve', 'title' => 'Принять авансовый отчет и провести его в оплату']),
            'ar_reject' => Html::submitButton(self::DOM_IDS['btnRejectAdvanceReportLabel'], ['id' => self::DOM_IDS['btnRejectAdvanceReportId'], 'class' => 'btn btn-danger btn-lg', 'name' => 'ar_reject', 'title' => 'Отказать в принятии авансового отчета (обязательно нужно будет указать причину согласования)']),
        ];

        $index = 'back';
        if (in_array($this->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_АВАНСОВЫЕ_ОТЧЕТЫ)) {
            $index = 'ar_index';
        }
        $result = $controlButtons[$index] . ' ' . ($this->isNewRecord ? '' : $controlButtons['history'] . ' ');

        switch ($this->state_id) {
            case null:
                $result .= $controlButtons['create'];
                break;
            case PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК:
                $result .= $controlButtons['save'] . ' ' . $controlButtons['claim'];
                break;
            case PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ:
                if (!Yii::$app->user->can(AuthItem::ROLE_ACCOUNTANT_SALARY)) {
                    $result .= $controlButtons['draft'];
                }

                if (Yii::$app->user->can('root')) {
                    $result .= ' ' . $controlButtons['approve'] . ' ' . $controlButtons['reject'];
                }
                if (Yii::$app->user->can('accountant_b') || Yii::$app->user->can(AuthItem::ROLE_ACCOUNTANT_SALARY)) {
                    $result .= ' ' . $controlButtons['save'];
                }
                break;
            case PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН:
                if (Yii::$app->user->can('root') || Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b')) {
                    $result .= $controlButtons['paid'];
                    if (Yii::$app->user->can('root')) {
                        $result .= ' ' . $controlButtons['reject'];
                    }
                    if (Yii::$app->user->can('accountant_b')) {
                        $result .= ' ' . $controlButtons['save'];
                    }
                }
                break;
            case PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ:
                //if (Yii::$app->user->can('root') || Yii::$app->user->can('logist')) {
                // выводить из отказа может любой пользователь
                    $result = $controlButtons['repeat'];
                //}
                if (Yii::$app->user->can('accountant_b')) {
                    $result .= ' ' . $controlButtons['save'];
                }
                break;
            case PaymentOrdersStates::PAYMENT_STATE_АВАНСОВЫЙ_ОТЧЕТ:
                if (Yii::$app->user->can('root') || Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b')) {
                    $result .= $controlButtons['ar_approve'] . ' ' . $controlButtons['ar_reject'];
                }

                break;
        }

        return $result;
    }

    /**
     * Делает выборку прикрепленных к платежному ордеру файлов.
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function getFilesAsDataProvider()
    {
        $searchModel = new PoFilesSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['po_id' => $this->id]]);
        $dataProvider->setSort(['defaultOrder' => ['uploaded_at' => SORT_DESC]]);
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Делает выборку истории изменений статусов платежного ордера.
     * @return \yii\data\ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function getLogsAsDataProvider()
    {
        $searchModel = new PoStatesHistorySearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['po_id' => $this->id]]);
        $dataProvider->setSort(['defaultOrder' => ['created_at' => SORT_DESC]]);
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Делает выборку свойств и значений свойств, которыми описана статья расходов, переданная в параметрах.
     * SELECT * FROM `po_values` WHERE `property_id` IN (SELECT `property_id` FROM `po_eip` WHERE `ei_id` = 5)
     * @param $ei PoEi
     * @return array
     */
    public static function fetchExpenditureItemPropertiesValuesAsArray($ei)
    {
        return PoValues::find()
            ->select([
                PoValues::tableName() . '.id',
                PoValues::tableName() . '.name',
                'property_id',
                'propertyName' => PoProperties::tableName() . '.name',
            ])
            ->where(['property_id' => PoEip::find()->select('property_id')->where(['ei_id' => $ei->id])])
            ->leftJoin(PoProperties::tableName(), PoProperties::tableName() . '.id = ' . PoValues::tableName() . '.property_id')
            ->orderBy(PoProperties::tableName() . '.name,' . PoValues::tableName() . '.id')
            ->asArray()->all();
    }

    /**
     * Делает выборку свойств и значений свойств платежного ордера в ввиде массива, с целью возможного редактирования
     * их состава.
     * @return array
     */
    public function getPropertiesAsFilledArray()
    {
        // свойства, которые в настоящий момент привязаны к статье расходов
        $pureProperties = self::fetchExpenditureItemPropertiesValuesAsArray($this->ei);

        // свойства, которые фактически привязаны к статье расходов конкретного платежного ордера
        foreach (PoPop::find()->joinWith(['property', 'value'])->where(['po_id' => $this->id])->all() as $property) {
            /* @var $property PoPop*/

            $propertyFound = false;
            foreach ($pureProperties as $index => $pureProperty) {
                if ($pureProperty['property_id'] == $property->property_id && $pureProperty['id'] == $property->value_id) {
                    $pureProperties[$index]['selected'] = true;
                    $pureProperties[$index]['link_id'] = $property->id;
                    $propertyFound = true;
                    break;
                }
            }

            if (!$propertyFound) {
                $pureProperties[] = [
                    'id' => $property['value_id'],
                    'name' => $property['valueName'],
                    'property_id' => $property['property_id'],
                    'propertyName' => $property['propertyName'],
                    'selected' => true,
                    'link_d' => null,
                ];
            }
        }

        return $pureProperties;
    }

    /**
     * Делает выборку свойств и значений свойств платежного ордера.
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function getPropertiesAsDataProvider()
    {
        $searchModel = new PoPopSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['po_id' => $this->id]]);
        $dataProvider->pagination = false;
        $dataProvider->sort = false;
        return $dataProvider;
    }

    /**
     * Выполняет загрузку файлов из post-параметров, а также сохранение их имен в базу данных.
     * Каждый файл прикрепляется к текущему платежному ордеру.
     * @return bool
     * @throws \yii\base\Exception
     */
    public function upload()
    {
        $success = true;

        // путь к папке, куда будет загружен файл
        $uploadsPath = PoFiles::getUploadsFilepath($this->id);

        foreach ($this->files as $file) {
            // имя и полный путь к файлу полноразмерного изображения
            $fileAttachedFn = strtolower(Yii::$app->security->generateRandomString() . '.' . $file->extension);
            $fileAttachedFfp = $uploadsPath . '/' . $fileAttachedFn;

            if ($file->saveAs($fileAttachedFfp)) {
                // заполняем поля записи в базе о загруженном успешно файле
                if (!(new PoFiles([
                    'po_id' => $this->id,
                    'ffp' => $fileAttachedFfp,
                    'fn' => $fileAttachedFn,
                    'ofn' => $file->name,
                    'size' => filesize($fileAttachedFfp),
                ]))->save()) {
                    $success = false;
                    unlink($fileAttachedFfp);
                }
            }
        }

        return $success;
    }

    /**
     * Возвращает представление модели.
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getModelRep()
    {
        return '№ ' . $this->id . ' от ' . Yii::$app->formatter->asDate($this->created_at, 'php:d.m.Y г.');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByRoles()
    {
        return $this->hasOne(AuthAssignment::class, ['user_id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'created_by'])->from(['createdByProfile' => 'profile']);
    }

    /**
     * Возвращает имя создателя записи.
     * @return string
     */
    public function getCreatedByProfileName()
    {
        return $this->createdByProfile != null ? ($this->createdByProfile->name != null ? $this->createdByProfile->name : $this->createdBy->username) : '';
    }

    /**
     * Возвращает E-mail автор платежного ордера.
     * @return string
     */
    public function getCreatedByProfileEmail()
    {
        return !empty($this->createdByProfile) ? (!empty($this->createdByProfile->public_email) ? $this->createdByProfile->public_email : '') : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(PaymentOrdersStates::class, ['id' => 'state_id']);
    }

    /**
     * Возвращает наименование текущего статуса платежного ордера.
     * @return string
     */
    public function getStateName()
    {
        return !empty($this->state) ? $this->state->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Companies::class, ['id' => 'company_id']);
    }

    /**
     * Возвращает наименование контрагента.
     * @return string
     */
    public function getCompanyName()
    {
        return !empty($this->company) ? $this->company->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEi()
    {
        return $this->hasOne(PoEi::class, ['id' => 'ei_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEiGroup()
    {
        return $this->hasOne(PoEig::class, ['id' => 'group_id'])->via('ei');
    }

    /**
     * Возвращает наименование статьи расходов.
     * @return string
     */
    public function getEiName()
    {
        return !empty($this->ei) ? $this->ei->name : '';
    }

    /**
     * Возвращает представление статьи расходов в формате html.
     * @return string
     */
    public function getEiRepHtml()
    {
        return !empty($this->ei) ? $this->eiGroup->name . ' &rarr; ' . $this->ei->name : '';
    }

    /**
     * Возвращает представление статьи расходов.
     * @return string
     */
    public function getEiRep()
    {
        return !empty($this->ei) ? $this->eiGroup->name . ' - ' . $this->ei->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoProject()
    {
        return $this->hasOne(PoEi::class, ['id' => 'ei_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoFiles()
    {
        return $this->hasMany(PoFiles::class, ['po_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoStatesHistories()
    {
        return $this->hasMany(PoStatesHistory::class, ['po_id' => 'id']);
    }
}
