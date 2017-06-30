<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mailing_projects".
 *
 * @property integer $id
 * @property integer $sent_at
 * @property integer $project_id
 * @property string $email_receiver
 */
class MailingProjects extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mailing_projects';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sent_at', 'project_id', 'email_receiver'], 'required'],
            [['sent_at', 'project_id'], 'integer'],
            [['email_receiver'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sent_at' => 'Дата и время отправки',
            'project_id' => 'Проект',
            'email_receiver' => 'E-mail получателя',
        ];
    }
}
