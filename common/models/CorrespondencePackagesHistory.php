<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "correspondence_packages_history".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $cp_id
 * @property string $description
 *
 * @property string $createdByProfileName
 *
 * @property CorrespondencePackages $cp
 * @property User $createdBy
 * @property Profile $createdByProfile
 */
class CorrespondencePackagesHistory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'correspondence_packages_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cp_id'], 'required'],
            [['created_at', 'created_by', 'cp_id'], 'integer'],
            [['description'], 'string'],
            [['cp_id'], 'exist', 'skipOnError' => true, 'targetClass' => CorrespondencePackages::className(), 'targetAttribute' => ['cp_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'created_by' => 'Автор создания',
            'cp_id' => 'Пакет корреспонденции',
            'description' => 'Суть события',
            // вычисляемые поля
            'createdByProfileName' => 'Автор',
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
        ];
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
        return $this->hasOne(Profile::className(), ['user_id' => 'created_by']);
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
    public function getCp()
    {
        return $this->hasOne(CorrespondencePackages::className(), ['id' => 'cp_id']);
    }
}
