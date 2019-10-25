<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_STATUS_CONTACT".
 *
 * @property int $ID_LIST_STATUS_CONTACT
 * @property string $STATUS_CONTACT
 * @property resource $IMAGE_STATUS_CONTACT
 * @property string $color_status
 * @property string $URL_IMG
 */
class foTasksPriorities extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LIST_STATUS_CONTACT';
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
            [['STATUS_CONTACT', 'IMAGE_STATUS_CONTACT', 'color_status', 'URL_IMG'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID_LIST_STATUS_CONTACT' => 'Id  List  Status  Contact',
            'STATUS_CONTACT' => 'Status  Contact',
            'IMAGE_STATUS_CONTACT' => 'Image  Status  Contact',
            'color_status' => 'Color Status',
            'URL_IMG' => 'Url  Img',
        ];
    }
}
