<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "notif_receivers_sncflt".
 *
 * @property integer $id
 * @property string $receiver
 */
class NotifReceiversStatesNotChangedTodayForALongTime extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notif_receivers_sncflt';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receiver'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'receiver' => 'E-mail',
        ];
    }
}
