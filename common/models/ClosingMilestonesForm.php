<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * @property string $date_finish дата окончания выборки (включительно)
 */
class ClosingMilestonesForm extends Model
{
    /**
     * Значение статуса закрытых проектов.
     */
    const STATE_CLOSED = 9;

    /**
     * @var string дата, по которую включительно будут закрыты этапы проектов
     */
    public $date_finish;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date_finish'], 'required'],
            [['date_finish'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date_finish' => 'Конец периода',
        ];
    }

    /**
     * Выполняет обновление значений поля "Статус" в этапах, отобранных по условию.
     */
    public function executeClosing()
    {
        return foProjectsMilestones::updateAll([
            'LIST_STEPS_PROGECT_COMPANY.ID_LIST_PRIZNAK_STEP_PROGECT' => self::STATE_CLOSED,
        ],
        [
            'and',
            ['<>', 'LIST_STEPS_PROGECT_COMPANY.ID_LIST_PRIZNAK_STEP_PROGECT', self::STATE_CLOSED],
            ['<=', 'LIST_STEPS_PROGECT_COMPANY.DATE_END_STEP', new \yii\db\Expression('CONVERT(datetime, \''. $this->date_finish .'T23:59:59.999\', 126)')]
        ]);
    }
}
