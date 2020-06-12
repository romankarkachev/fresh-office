<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "incoming_mail".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property int $direction Направление (1 - входящее, 2 - исходящее)
 * @property int $state_id Состояние
 * @property string $track_num Трек-номер
 * @property string $inc_num Входящий номер
 * @property string $inc_date Входящая дата
 * @property int $type_id Тип
 * @property int $org_id Получатель письма (организация)
 * @property string $description Опись вложения
 * @property string $date_complete_before Срок исполнения
 * @property int $ca_src Источник данных для поля Отправитель
 * @property int $ca_id Идентификатор контрагента-отправителя
 * @property string $ca_name Наименование контрагента-отправителя
 * @property int $receiver_id Получатель письма (физлицо)
 * @property string $comment Комментарий
 *
 * @property string $createdByProfileName
 * @property string $stateName
 * @property string $typeName
 * @property string $organizationName
 * @property string $receiverName
 *
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property ProjectsStates $state
 * @property Organizations $organization
 * @property User $receiver
 * @property Profile $receiverProfile
 * @property IncomingMailTypes $type
 * @property IncomingMailFiles[] $incomingMailFiles
 * @property ActiveDataProvider $filesAsDataProvider
 */
class IncomingMail extends \yii\db\ActiveRecord
{
    /**
     * Возможные значения для поля "Направление"
     */
    const DIRECTION_IN = 1;
    const DIRECTION_OUT = 2;

    /**
     * Возможные значения для поля "Источник" при идентификации контрагента
     */
    const CA_SOURCES_КОНТРАГЕНТЫ = 1;
    const CA_SOURCES_ПЕРЕВОЗЧИКИ = 2;
    const CA_SOURCES_FRESH_OFFICE = 3;

    /**
     * @var int виртуальное поле для подбора контрагента-отправителя
     */
    public $counteragent;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'incoming_mail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['inc_num', 'type_id'], 'required'],
            [['created_at', 'created_by', 'direction', 'state_id', 'type_id', 'org_id', 'ca_src', 'ca_id', 'receiver_id'], 'integer'],
            [['description', 'comment'], 'string'],
            [['inc_date', 'date_complete_before', 'counteragent'], 'safe'],
            [['track_num'], 'string', 'max' => 50],
            [['inc_num'], 'string', 'max' => 30],
            [['ca_name'], 'string', 'max' => 255],
            [['ca_src'], 'default', 'value' => 1],
            [['description', 'ca_name', 'comment'], 'trim'],
            [['description', 'ca_name', 'comment'], 'default', 'value' => null],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['org_id' => 'id']],
            [['receiver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['receiver_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => IncomingMailTypes::class, 'targetAttribute' => ['type_id' => 'id']],
            ['ca_src', 'validateCounteragent'],
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
            'direction' => 'Направление', // 1 - входящее, 2 - исходящее
            'state_id' => 'Состояние',
            'track_num' => 'Трек-номер',
            'inc_num' => 'Входящий номер',
            'inc_date' => 'Входящая дата',
            'type_id' => 'Тип',
            'org_id' => 'Получатель письма (организация)',
            'description' => 'Опись вложения',
            'date_complete_before' => 'Срок исполнения',
            'ca_src' => 'Источник данных для поля Отправитель', // 1 - справочник контрагентов, 2 - перевозчики, 3 - контрагент из Fresh Office
            'ca_id' => 'Идентификатор контрагента-отправителя',
            'ca_name' => 'Наименование контрагента-отправителя',
            'receiver_id' => 'Получатель письма (физлицо)',
            'comment' => 'Комментарий',
            // вирутальные поля
            'counteragent' => 'Контрагент',
            // вычисляемые поля
            'createdByProfileName' => 'Автор создания',
            'stateName' => 'Состояние',
            'typeName' => 'Тип',
            'organizationName' => 'Получатель письма (организация)',
            'receiverName' => 'Получатель письма (физлицо)',
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
     * {@inheritDoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (empty($this->state_id) && !empty($this->track_num)) {
                $this->state_id = ProjectsStates::STATE_ОТПРАВЛЕНО;
            }

            return true;
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением объекта

            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = IncomingMailFiles::find()->where(['im_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function validateCounteragent()
    {
        if (empty($this->ca_src) || empty($this->ca_id) || empty($this->ca_name)) {
            $this->addError('counteragent', 'Ошибка при выборе контрагента.');
        }
    }

    /**
     * Вычисляет следующий номер документа по шаблону из карточки организации.
     * @param string $fieldName наименование поля с шаблоном номера
     */
    public function calcNextNumber($fieldName)
    {
        $organization = $this->organization;
        if (!empty($organization) && !empty($organization->$fieldName)) {
            if (preg_match("/\[C\d+\]/i", $organization->$fieldName, $matches)) {
                if (!empty($matches[0])) {
                    $docNum = '';
                    try {
                        $count = self::find()->where(['org_id' => $this->org_id])->andWhere([
                            'between',
                            'created_at',
                            strtotime(Yii::$app->formatter->asDate(time(), 'php:Y-m-d 00:00:00')),
                            strtotime(Yii::$app->formatter->asDate(time(), 'php:Y-m-d 23:59:59')),
                        ])->count();
                        $count++;
                        $docNum = str_replace($matches[0], str_pad($count, Drivers::leaveOnlyDigits($matches[0]), '0', STR_PAD_LEFT), $organization->$fieldName);
                        $docNum = str_replace('[D]', Yii::$app->formatter->asDate(time(), 'php:d'), $docNum);
                        $docNum = str_replace('[M]', Yii::$app->formatter->asDate(time(), 'php:m'), $docNum);
                        $docNum = str_replace('[Y]', Yii::$app->formatter->asDate(time(), 'php:y'), $docNum);
                    }
                    catch (\Exception $e) {}

                    $this->inc_num = $docNum;
                }
            }
        }
    }

    /**
     * Возвращает массив источников данных для поля "Контрагент".
     * @return array
     */
    public static function fetchCaSources()
    {
        return [
            [
                'id' => self::CA_SOURCES_КОНТРАГЕНТЫ,
                'name' => 'Контрагенты',
            ],
            [
                'id' => self::CA_SOURCES_ПЕРЕВОЗЧИКИ,
                'name' => 'Перевозчики',
            ],
            [
                'id' => self::CA_SOURCES_FRESH_OFFICE,
                'name' => 'Контрагенты из Fresh Office',
            ],
        ];
    }

    /**
     * Делает выборку источников данных для поля "Контрагент" и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfCaSourcesForSelect2()
    {
        return ArrayHelper::map(self::fetchCaSources(), 'id', 'name');
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
        return !empty($this->createdByProfile) ? (!empty($this->createdByProfile->name) ? $this->createdByProfile->name : $this->createdBy->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(ProjectsStates::class, ['id' => 'state_id']);
    }

    /**
     * Возвращает наименование статуса.
     * @return string
     */
    public function getStateName()
    {
        return !empty($this->state) ? $this->state->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(IncomingMailTypes::class, ['id' => 'type_id']);
    }

    /**
     * Возвращает наименование типа входящей корреспонденции.
     * @return string
     */
    public function getTypeName()
    {
        return !empty($this->type) ? $this->type->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'org_id']);
    }

    /**
     * Возвращает наименование организации-получателя корреспонденции.
     * @return string
     */
    public function getOrganizationName()
    {
        return !empty($this->organization) ? $this->organization->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceiver()
    {
        return $this->hasOne(User::class, ['id' => 'receiver_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceiverProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'receiver_id'])->from(['receiverProfile' => 'profile']);
    }

    /**
     * Возвращает имя создателя записи.
     * @return string
     */
    public function getReceiverProfileName()
    {
        return !empty($this->receiverProfile) ? (!empty($this->receiverProfile->name) ? $this->receiverProfile->name : $this->receiver->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIncomingMailFiles()
    {
        return $this->hasMany(IncomingMailFiles::class, ['im_id' => 'id']);
    }

    /**
     * Делает выборку прикрепленных ко входящему документу файлов.
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function getFilesAsDataProvider()
    {
        $searchModel = new IncomingMailFilesSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['im_id' => $this->id]]);
        $dataProvider->setSort(['defaultOrder' => ['uploaded_at' => SORT_DESC]]);
        $dataProvider->pagination = false;

        return $dataProvider;
    }
}
