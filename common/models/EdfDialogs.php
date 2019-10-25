<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "edf_dialogs".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property int $ed_id Документ
 * @property string $message Текст сообщения
 * @property int $read_at Прочитано
 *
 * @property string $createdByProfileName
 *
 * @property Edf $ed
 * @property User $createdBy
 * @property Profile $createdByProfile
 */
class EdfDialogs extends \yii\db\ActiveRecord
{
    /**
     * @var string роль автора сообщения
     */
    public $roleName;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'edf_dialogs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ed_id', 'message'], 'required'],
            [['created_at', 'created_by', 'ed_id', 'read_at'], 'integer'],
            [['message'], 'string'],
            [['ed_id'], 'exist', 'skipOnError' => true, 'targetClass' => Edf::className(), 'targetAttribute' => ['ed_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
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
            'ed_id' => 'Документ',
            'message' => 'Текст сообщения',
            'read_at' => 'Прочитано',
            // для вычисляемых полей
            'createdByProfileName' => 'Автор',
            'roleName' => 'Роль',
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
}
