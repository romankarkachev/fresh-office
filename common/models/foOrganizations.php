<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ADD_SPR_ispoln".
 *
 * @property int $ID
 * @property string $NAME
 */
class foOrganizations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ADD_SPR_ispoln';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_mssql');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['NAME'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'NAME' => 'Name',
        ];
    }

    /**
     * Делает выборку исполнителей работ (организаций) и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * Поле name в идентификаторе и наименовании!
     * @return array
     */
    public static function arrayMapNamesForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'NAME', 'NAME');
    }
}
