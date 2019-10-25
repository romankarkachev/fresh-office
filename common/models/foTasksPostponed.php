<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_HISTORY_RETIME_CONTACT".
 *
 * @property int $ID
 * @property int $ID_CONTACT
 * @property string $DATA_CONTACT_FINAL
 */
class foTasksPostponed extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LIST_HISTORY_RETIME_CONTACT';
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
            [['ID_CONTACT'], 'integer'],
            [['DATA_CONTACT_FINAL'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'ID_CONTACT' => 'Id  Contact',
            'DATA_CONTACT_FINAL' => 'Data  Contact  Final',
        ];
    }
}
