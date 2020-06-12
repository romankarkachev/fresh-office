<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "LIST_SPR_INFORM_IN_COMPANY".
 *
 * @property int $ID_LIST_SPR_INFORM_IN_COMPANY
 * @property string $INFORM_IN_COMPANY
 * @property string $PRIM_IN_COMPANY
 * @property double $SUM_ZATRAT
 */
class foSources extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LIST_SPR_INFORM_IN_COMPANY';
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
            [['SUM_ZATRAT'], 'number'],
            [['INFORM_IN_COMPANY', 'PRIM_IN_COMPANY'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID_LIST_SPR_INFORM_IN_COMPANY' => 'Id List Spr Inform In Company',
            'INFORM_IN_COMPANY' => 'Inform In Company',
            'PRIM_IN_COMPANY' => 'Prim In Company',
            'SUM_ZATRAT' => 'Sum Zatrat',
        ];
    }

    /**
     * Делает выборку источников обращения и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * Поле name в идентификаторе и наименовании!
     * @return array
     */
    public static function arrayMapNamesForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'INFORM_IN_COMPANY', 'INFORM_IN_COMPANY');
    }
}
