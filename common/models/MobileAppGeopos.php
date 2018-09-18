<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "mobile_app_geopos".
 *
 * @property integer $id
 * @property integer $arrived_at
 * @property integer $user_id
 * @property double $coord_lat
 * @property double $coord_long
 *
 * @property string $userProfileName
 *
 * @property User $user
 * @property Profile $userProfile
 */
class MobileAppGeopos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mobile_app_geopos';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'coord_lat', 'coord_long'], 'required'],
            [['arrived_at', 'user_id'], 'integer'],
            [['coord_lat', 'coord_long'], 'number'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'arrived_at' => 'Дата и время отправки координат',
            'user_id' => 'Чьи координаты',
            'coord_lat' => 'Широта',
            'coord_long' => 'Долгота',
            // вычисляемые поля
            'userProfileName' => 'Имя пользователя',
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
                    ActiveRecord::EVENT_BEFORE_INSERT => ['arrived_at'],
                ],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'user_id']);
    }

    /**
     * Возвращает имя пользователя системы, которому принадлежат координаты.
     * @return string
     */
    public function getUserProfileName()
    {
        return $this->userProfile != null ? ($this->userProfile->name != null ? $this->userProfile->name : $this->user->username) : '';
    }
}
