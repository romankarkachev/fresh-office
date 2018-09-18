<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "websites".
 *
 * @property integer $id
 * @property string $name
 *
 * @property pbxCalls[] $calls
 * @property pbxExternalPhoneNumber[] $externalPhoneNumbers
 */
class pbxWebsites extends \yii\db\ActiveRecord
{
    /**
     * @var integer виртуальное поле, содержащее количество номеров телефонов
     */
    public $phonesCount;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'websites';
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
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Адрес сайта',
            // вычисляемые поля
            'phonesCount' => 'Телефонов',
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getCalls()->count() > 0)
            return true;
        elseif ($this->getExternalPhoneNumbers()->count() > 0)
            return true;

        return false;
    }

    /**
     * Делает выборку сайтов-источников лидов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCalls()
    {
        return $this->hasMany(pbxCalls::className(), ['website_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExternalPhoneNumbers()
    {
        return $this->hasMany(pbxExternalPhoneNumber::className(), ['website_id' => 'id']);
    }
}
