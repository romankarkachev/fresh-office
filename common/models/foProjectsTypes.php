<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_SPR_PROJECT".
 *
 * @property int $ID_LIST_SPR_PROJECT
 * @property string $NAME_PROJECT
 * @property string $USE_N_PP
 *
 * @property LISTSPRSTEPSPROGECT[] $lISTSPRSTEPSPROGECTs
 */
class foProjectsTypes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CBaseCRM_Fresh_7x.dbo.LIST_SPR_PROJECT';
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
            [['NAME_PROJECT'], 'string', 'max' => 255],
            [['USE_N_PP'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID_LIST_SPR_PROJECT' => 'Id List Spr Project',
            'NAME_PROJECT' => 'Name Project',
            'USE_N_PP' => 'Use N Pp',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTSPRSTEPSPROGECTs()
    {
        return $this->hasMany(LISTSPRSTEPSPROGECT::className(), ['ID_LIST_SPR_PROJECT' => 'ID_LIST_SPR_PROJECT']);
    }
}
