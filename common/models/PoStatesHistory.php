<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "po_states_history".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property int $po_id Платежный ордер
 * @property int $state_id Статус
 * @property string $description Суть события
 *
 * @property string $createdByProfileName
 * @property string $stateName
 *
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property Po $po
 * @property PaymentOrdersStates $state
 */
class PoStatesHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'po_states_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['po_id', 'state_id'], 'required'],
            [['created_at', 'created_by', 'po_id', 'state_id'], 'integer'],
            [['description'], 'string'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['po_id'], 'exist', 'skipOnError' => true, 'targetClass' => Po::class, 'targetAttribute' => ['po_id' => 'id']],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentOrdersStates::class, 'targetAttribute' => ['state_id' => 'id']],
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
            'po_id' => 'Платежный ордер',
            'state_id' => 'Статус',
            'description' => 'Суть события',
            // вычисляемые поля
            'createdByProfileName' => 'Автор создания',
            'stateName' => 'Статус',
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
        return $this->hasOne(Profile::class, ['user_id' => 'created_by'])->from(['createdProfile' => 'profile']);
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
     * @return \yii\db\ActiveQuery
     */
    public function getPo()
    {
        return $this->hasOne(Po::className(), ['id' => 'po_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(PaymentOrdersStates::className(), ['id' => 'state_id']);
    }

    /**
     * Возвращает наименование статуса.
     * @return string
     */
    public function getStateName()
    {
        return !empty($this->state) ? $this->state->name : '';
    }
}
