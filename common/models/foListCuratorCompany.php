<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_CURATOR_COMPANY".
 *
 * @property int $ID_LIST_CURATOR_COMPANY
 * @property int $ID_COMPANY
 * @property int $ID_MANAGER
 * @property string $DATE_CREATE
 * @property string $PRIM_CURATOR
 */
class foListCuratorCompany extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LIST_CURATOR_COMPANY';
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
            [['ID_COMPANY', 'ID_MANAGER'], 'integer'],
            [['DATE_CREATE'], 'safe'],
            [['PRIM_CURATOR'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID_LIST_CURATOR_COMPANY' => 'Id List Curator Company',
            'ID_COMPANY' => 'Id Company',
            'ID_MANAGER' => 'Id Manager',
            'DATE_CREATE' => 'Date Create',
            'PRIM_CURATOR' => 'Prim Curator',
        ];
    }
}
