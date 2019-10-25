<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * @property string $date_finish дата окончания выборки (включительно)
 */
class ClosingInvoicesForm extends Model
{
    /**
     * Значение статуса закрытых счетов
     */
    const STATE_CLOSED = 2;

    /**
     * @var string дата, по которую включительно будут закрыты счета
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
     * Выполняет обновление значений поля "Статус" в счетах, отобранных по условию.
     */
    public function executeClosing()
    {
        $attributeState = 'ID_PRIZNZK_DOC';

        return foListDocuments::updateAll([
            $attributeState => self::STATE_CLOSED,
            'DATE_CLOSING' => new \yii\db\Expression('CONVERT(datetime, \''. Yii::$app->formatter->asDate(time(), 'php:Y-m-d\TH:i:s') .'\', 126)'),
        ],
        [
            'and',
            ['<>', $attributeState, self::STATE_CLOSED],
            ['<=', 'DATA_DOC', new \yii\db\Expression('CONVERT(datetime, \''. $this->date_finish .'T23:59:59.999\', 126)')]
        ]);
    }
}
