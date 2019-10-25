<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "cp_bl_contact_emails".
 *
 * @property int $id
 * @property int $created_at Дата и время отписки
 * @property int $fo_ca_id Контрагент
 * @property string $email Адрес E-mail
 */
class CpBlContactEmails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cp_bl_contact_emails';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'fo_ca_id'], 'integer'],
            [['email'], 'required'],
            [['email'], 'string', 'max' => 255],
            [['email'], 'safe'],
            [['email'], 'trim'],
            [['email'], 'email'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время отписки',
            'fo_ca_id' => 'Контрагент',
            'email' => 'Адрес E-mail',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }
}
