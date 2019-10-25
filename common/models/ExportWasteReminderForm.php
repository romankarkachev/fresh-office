<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * @property integer $project_id идентификатор проекта
 * @property string $date предполагаемая дата вывоза отходов
 * @property string $email получатель уведомления
 * @property string $transport_info информация о транспортном средстве
 * @property string $driver_info информация о водителе
 * @property array $files файлы, которые необходимо приаттачить к письму
 */
class ExportWasteReminderForm extends Model
{
    public $project_id;
    public $project;
    public $date;
    public $email;
    public $transport_info;
    public $driver_info;
    public $files;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'date', 'email', 'transport_info', 'driver_info'], 'required'],
            [['project_id'], 'integer'],
            [['email'], 'email'],
            [['date', 'transport_info', 'driver_info'], 'safe'],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'project_id' => 'Проект',
            'email' => 'Получатель',
            'date' => 'Дата вывоза',
            'transport_info' => 'Транспорт',
            'driver_info' => 'Водитель',
            'files' => 'Файлы с ТТН и АПП',
        ];
    }
}
