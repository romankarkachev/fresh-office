<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "external_phone_number".
 *
 * @property integer $id
 * @property string $phone_number
 * @property string $note
 * @property integer $website_id
 *
 * @property pbxWebsites $website
 */
class pbxExternalPhoneNumber extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'external_phone_number';
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
            [['phone_number'], 'required'],
            [['website_id'], 'integer'],
            [['phone_number'], 'string', 'max' => 20],
            [['note'], 'string', 'max' => 500],
            [['website_id'], 'exist', 'skipOnError' => true, 'targetClass' => pbxWebsites::className(), 'targetAttribute' => ['website_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone_number' => 'Номер телефона',
            'note' => 'Примечание',
            'website_id' => 'Сайт',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWebsite()
    {
        return $this->hasOne(pbxWebsites::className(), ['id' => 'website_id']);
    }
}
