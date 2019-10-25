<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "edf_states".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Edf[] $edfs
 */
class EdfStates extends \yii\db\ActiveRecord
{
    const STATE_ЧЕРНОВИК = 1;
    const STATE_ЗАЯВКА = 2;
    const STATE_СОГЛАСОВАНИЕ = 3;
    const STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА = 4;
    const STATE_ПОДПИСАН_РУКОВОДСТВОМ = 5;
    const STATE_НА_ПОДПИСИ_У_ЗАКАЗЧИКА = 6;
    const STATE_ОТПРАВЛЕН = 7;
    const STATE_ДОСТАВЛЕН = 8;
    const STATE_ЗАВЕРШЕНО = 9;
    const STATE_ОТКАЗ = 10;
    const STATE_ОТКАЗ_КЛИЕНТА = 11;
    const STATE_УТВЕРЖДЕНО = 12;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'edf_states';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
        ];
    }

    /**
     * Делает выборку статусов документов в делопроизводстве и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEdfs()
    {
        return $this->hasMany(Edf::className(), ['state_id' => 'id']);
    }
}
