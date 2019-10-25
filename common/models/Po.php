<?php

namespace common\models;

use backend\controllers\PoController;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * This is the model class for table "po".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property int $state_id Текущий статус
 * @property int $company_id Контрагент
 * @property int $ei_id Статья расходов
 * @property string $amount Сумма
 * @property int $approved_at Дата и время согласования ордера
 * @property int $paid_at Дата и время оплаты
 * @property int $fo_project_id ID проекта с CRM Fresh Office
 * @property string $comment Комментарий
 *
 * @property string $modelRep
 * @property string $createdByProfileName
 * @property string $createdByProfileEmail
 * @property string $stateName
 * @property string $companyName
 * @property string $eiName
 * @property string $eiRepHtml
 *
 * @property PoEi $ei
 * @property PoEig $eiGroup
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property PaymentOrdersStates $state
 * @property Companies $company
 * @property PoFiles[] $poFiles
 * @property PoStatesHistory[] $poStatesHistories
 * @property PoPop[] $poPops
 */
class Po extends \yii\db\ActiveRecord
{
    /**
     * Информационное предупреждение о том, что статья расходов не содержит приаттаченных свойств
     */
    const PROMPT_EMPTY_PROPERTIES = 'Свойства не назначены.';

    /**
     * @var array массив свойств и значений свойств статьи расходов
     */
    public $propertiesValues;

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
            [['created_at', 'created_by', 'state_id', 'company_id', 'ei_id', 'approved_at', 'paid_at', 'fo_project_id'], 'integer'],
            [['amount'], 'number'],
            [['comment'], 'string'],
            [['propertiesValues'], 'safe'],
            [['comment'], 'default', 'value' => null],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Companies::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['ei_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoEi::className(), 'targetAttribute' => ['ei_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentOrdersStates::className(), 'targetAttribute' => ['state_id' => 'id']],
            // собственные правила валидации
            ['ei_id', 'validateEiId'],
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
            'state_id' => 'Статус',
            'company_id' => 'Контрагент',
            'ei_id' => 'Статья расходов',
            'amount' => 'Сумма',
            'approved_at' => 'Дата и время согласования ордера',
            'paid_at' => 'Дата и время оплаты',
            'fo_project_id' => 'ID проекта с CRM Fresh Office',
            'comment' => 'Комментарий',
            // вычисляемые поля
            'createdByProfileName' => 'Автор создания',
            'stateName' => 'Текущий статус',
            'companyName' => 'Контрагент',
            'eiName' => 'Статья расходов',
            'eiRepHtml' => 'Статья расходов',
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
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);

        if (!$insert) {
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

                            break;
                        case PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ:
                            // статус изменился на "Отказ", отправим автору ордера соответствующее уведомление
                            $receiver = $this->createdByProfileEmail;
                            if (!empty($receiver)) {
                                $letter = Yii::$app->mailer->compose([
                                    'html' => 'poReject-html',
                                ], ['po' => $this])
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

            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = PoFiles::find()->where(['po_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            PoPop::deleteAll(['po_id' => $this->id]);

            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function validateEiId()
    {
        if (!empty($this->ei_id) && ($this->ei->group_id == PoEig::ГРУППА_ТРАНСПОРТ) && empty($this->fo_project_id)) {
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
            'create' => Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']),
            'save' => Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить черновик', ['class' => 'btn btn-primary btn-lg']),
            'claim' => Html::submitButton('Сохранить и отправить на согласование <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>', ['class' => 'btn btn-success btn-lg', 'name' => 'order_ready', 'title' => 'Создать и сразу отправить на согласование']),
            'approve' => Html::submitButton('Согласовать', ['class' => 'btn btn-success btn-lg', 'name' => 'order_approve', 'title' => 'Согласовать и сразу отправить на оплату']),
            'reject' => Html::submitButton('<i class="fa fa-times" aria-hidden="true"></i> Отказать', ['class' => 'btn btn-danger btn-lg', 'name' => 'order_reject', 'title' => 'Отказать в согласовании (обязательно нужно будет указать причину согласования)']),
            'paid' => Html::submitButton('Оплачено', ['class' => 'btn btn-success btn-lg', 'name' => 'order_paid', 'title' => 'Установить признак "Оплачено"']),
            'repeat' => Html::submitButton('<i class="fa fa-refresh" aria-hidden="true"></i> Подать повторно', ['class' => 'btn btn-default btn-lg', 'name' => 'order_repeat', 'title' => 'Подать отклоненный ордер на согласование повторно']),
            'draft' => Html::submitButton('<i class="fa fa-undo" aria-hidden="true"></i> Вернуть в черновики', ['class' => 'btn btn-default btn-lg', 'name' => 'order_repeat', 'title' => 'Вернуть ордер в черновики']),
        ];

        $result = $controlButtons['index'] . ' ';

        switch ($this->state_id) {
            case null:
                $result .= $controlButtons['create'];
                break;
            case PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК:
                $result .= $controlButtons['save'] . ' ' . $controlButtons['claim'];
                break;
            case PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ:
                $result .= $controlButtons['draft'] . ' ';
                if (Yii::$app->user->can('root')) {
                    $result .= $controlButtons['approve'] . ' ' . $controlButtons['reject'];
                }
                break;
            case PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН:
                if (Yii::$app->user->can('root') || Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b')) {
                    $result .= $controlButtons['paid'];
                    if (Yii::$app->user->can('root')) {
                        $result .= ' ' . $controlButtons['reject'];
                    }
                }
                break;
            case PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ:
                if (Yii::$app->user->can('root') || Yii::$app->user->can('logist')) {
                    $result = $controlButtons['repeat'];
                }
                break;
        }

        return $result;
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
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'created_by'])->from(['createdByProfile' => 'profile']);
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
        return $this->hasOne(PaymentOrdersStates::className(), ['id' => 'state_id']);
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
        return $this->hasOne(Companies::className(), ['id' => 'company_id']);
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
        return $this->hasOne(PoEi::className(), ['id' => 'ei_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEiGroup()
    {
        return $this->hasOne(PoEig::className(), ['id' => 'group_id'])->via('ei');
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
     * @return \yii\db\ActiveQuery
     */
    public function getPoFiles()
    {
        return $this->hasMany(PoFiles::className(), ['po_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoStatesHistories()
    {
        return $this->hasMany(PoStatesHistory::className(), ['po_id' => 'id']);
    }
}
