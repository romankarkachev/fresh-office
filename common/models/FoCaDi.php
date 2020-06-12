<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "fo_ca_di".
 *
 * @property int $id
 */
class FoCaDi extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fo_ca_di';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
        ];
    }
}
