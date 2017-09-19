<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mailing_projects".
 *
 * @property integer $id
 * @property integer $sent_at
 * @property integer $type
 * @property integer $project_id
 * @property string $email_receiver
 */
class MailingProjects extends \yii\db\ActiveRecord
{
    /**
     * Типы рассылки.
     */
    const MAILING_TYPE_ZAPIER = 1; // проекты с типами Фото/видео, Выездные работы, Осмотр объекта, которые в html-формате рассылаются ответственным
    const MAILING_TYPE_PDF = 2; // проекты с типами Заказ (оба), Вывоз, Самопривоз, которые рассылаются в pdf-файлах

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
            [['sent_at', 'type', 'project_id'], 'integer'],
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
            'type' => 'Тип рассылки',
            'project_id' => 'Проект',
            'email_receiver' => 'E-mail получателя',
        ];
    }
}
