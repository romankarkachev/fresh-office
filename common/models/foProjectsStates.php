<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "LIST_SPR_PRIZNAK_PROJECT".
 *
 * @property integer $ID_PRIZNAK_PROJECT
 * @property string $PRIZNAK_PROJECT
 * @property string $color_status
 * @property string $FINAL
 */
class foProjectsStates extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CBaseCRM_Fresh_7x.dbo.LIST_SPR_PRIZNAK_PROJECT';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_mssql');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['PRIZNAK_PROJECT', 'color_status', 'FINAL'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_PRIZNAK_PROJECT' => 'ID',
            'PRIZNAK_PROJECT' => 'Наименование',
            'color_status' => 'Color Status',
            'FINAL' => 'Final',
        ];
    }

    /**
     * Делает выборку статусов проектов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->select(['id' => 'ID_PRIZNAK_PROJECT', 'name' => 'PRIZNAK_PROJECT'])->orderBy('PRIZNAK_PROJECT')->asArray()->all(), 'id', 'name');
    }
}
