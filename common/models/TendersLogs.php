<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tenders_logs".
 *
 * @property int $id
 * @property int $created_at Дата и время изменения
 * @property int $created_by Автор изменений
 * @property int $tender_id Тендер
 * @property int $type Тип журнала (1 - внутренняя запись, 2 - запись с сайта закупок)
 * @property string $description Суть события
 *
 * @property string $createdByProfileName
 * @property string $typeName
 *
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property Tenders $tender
 */
class TendersLogs extends \yii\db\ActiveRecord
{
    /**
     * Возможные значения для поля type
     */
    const TYPE_ВНУТРЕННЯЯ = 1;
    const TYPE_ИСТОЧНИК = 2;

    /**
     * Идентификаторы элементов страницы
     */
    const DOM_IDS = [
        // форма для интерактивного отбора по журналу событий
        'PJAX_SEARCH_FORM_ID' => 'frmSearchLogs',
        // таблица с записями журнала
        'GRIDVIEW_ID' => 'gwLogs',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tender_id'], 'required'],
            [['created_at', 'created_by', 'tender_id', 'type'], 'integer'],
            [['description'], 'string'],
            ['type', 'default', 'value' => TendersLogs::TYPE_ВНУТРЕННЯЯ],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['tender_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tenders::class, 'targetAttribute' => ['tender_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время изменения',
            'created_by' => 'Автор изменений',
            'tender_id' => 'Тендер',
            'type' => 'Тип журнала', // 1 - внутренняя запись, 2 - запись с сайта закупок
            'description' => 'Суть события',
            // вычисляемые поля
            'createdByProfileName' => 'Инициатор',
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
                'preserveNonEmptyValues' => true,
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
     * Возвращает массив с возможными интерпретациями значений поля "Источник".
     * @return array
     */
    private function fetchTypes()
    {
        return [
            [
                'id' => self::TYPE_ВНУТРЕННЯЯ,
                'name' => 'Внутренняя запись',
            ],
            [
                'id' => self::TYPE_ИСТОЧНИК,
                'name' => 'Запись с сайта закупок',
            ],
        ];
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
    public function getTender()
    {
        return $this->hasOne(Tenders::class, ['id' => 'tender_id']);
    }


    /**
     * Возвращает наименование источника записи (внутренний или внешний).
     * @return string
     */
    public function getTypeName()
    {
        $sourceTable = $this->fetchTypes();
        $key = array_search($this->type, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '';
    }
}
