<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "call_comment".
 *
 * @property integer $id
 * @property integer $call_id
 * @property string $added_timestamp
 * @property string $contents
 * @property integer $user_id
 *
 * @property string $createdByProfileName
 *
 * @property Profile $createdByProfile
 */
class pbxCallsComments extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'call_comment';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_asterisk');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['call_id', 'added_timestamp', 'contents', 'user_id'], 'required'],
            [['call_id', 'user_id'], 'integer'],
            [['added_timestamp'], 'safe'],
            [['contents'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'call_id' =>  'Звонок',
            'added_timestamp' => 'Добавлен',
            'contents' => 'Содержание',
            'user_id' => 'Автор',
            // вычисляемые поля
            'createdByProfileName' => 'Автор',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'user_id']);
    }

    /**
     * Возвращает имя пользователя из его профиля.
     * @return string
     */
    public function getCreatedByProfileName()
    {
        return !empty($this->createdByProfile) ? $this->createdByProfile->name : '';
    }
}
