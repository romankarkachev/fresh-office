<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Форма для формирования Акта приема-передачи.
 */
class DocumentAPPGenerationForm extends Model
{
    /**
     * @var string дата акта
     */
    public $date;

    /**
     * @var integer проект, к которому генерируется АПП
     */
    public $project_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'project_id'], 'required'],
            [['date'], 'safe'],
            [['project_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date' => 'Дата акта',
            'project_id' => 'Проект',
        ];
    }
}
