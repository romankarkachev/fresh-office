<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "edf_states_history".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $ed_id
 * @property string $description
 *
 * @property string $createdByProfileName
 * @property string $roleName роль пользователя, который создал запись в истории изменения статусов
 *
 * @property Edf $ed
 * @property User $createdBy
 * @property Profile $createdByProfile
 */
class EdfStatesHistory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'edf_states_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ed_id'], 'required'],
            [['created_at', 'created_by', 'ed_id'], 'integer'],
            [['description'], 'string'],
            [['ed_id'], 'exist', 'skipOnError' => true, 'targetClass' => Edf::className(), 'targetAttribute' => ['ed_id' => 'id']],
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
            'ed_id' => 'Электронный документ',
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
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEd()
    {
        return $this->hasOne(Edf::className(), ['id' => 'ed_id']);
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
     * Делает выборку ролей автора записи.
     * @return \yii\db\ActiveQuery
     */
    public function getUserRoles()
    {
        return $this->hasMany(AuthAssignment::className(), ['user_id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'item_name'])
            ->via('userRoles');
    }

    /**
     * Возвращает наименование роли пользователя.
     * @return string
     */
    public function getRoleName()
    {
        return $this->role != null ? $this->role->name : '';
    }
}
