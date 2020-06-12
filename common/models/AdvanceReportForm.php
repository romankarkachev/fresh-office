<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Форма для принятия от подотчетного лица Авансовых отчетов
 *
 * @property array $crudePos авансовые отчеты
 */
class AdvanceReportForm extends Model
{
    /**
     * @var string дата, по которую включительно будут закрыты этапы проектов
     */
    public $crudePos;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['crudePos'], 'required'],
            [['crudePos'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'crudePos' => 'Авансовые отчеты',
        ];
    }
}
