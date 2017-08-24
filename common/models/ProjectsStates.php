<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "projects_states".
 *
 * @property integer $id
 * @property string $name
 *
 * @property CorrespondencePackages[] $correspondencePackages
 */
class ProjectsStates extends \yii\db\ActiveRecord
{
    /**
     * Статусы проектов.
     */
    const STATE_СЧЕТ_ОЖИДАЕТ_ОПЛАТЫ = 4;
    const STATE_ОПЛАЧЕНО = 5;
    const STATE_СОГЛАСОВАНИЕ_ВЫВОЗА = 6;
    const STATE_ЗАКРЫТИЕ_СЧЕТА = 17;
    const STATE_ОТДАНО_НА_ОТПРАВКУ = 18; // бухгалтер отнес менеджеру первичку
    const STATE_ОТПРАВЛЕНО = 19;
    const STATE_ДОСТАВЛЕНО = 20;
    const STATE_ЗАВЕРШЕНО = 25;
    const STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ = 38; // документы ушли от бухгалтера, но менеджер с ними еще не разбирался
    const STATE_ОЖИДАЕТ_ОТПРАВКИ = 43; // менеджер разобрался с документами, чуть ли не вложил в конверты пакеты документов

    const НАБОР_ОПЕРАТОРА = [
        self::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ,
        self::STATE_ОЖИДАЕТ_ОТПРАВКИ,
        self::STATE_ОТПРАВЛЕНО,
        self::STATE_СОГЛАСОВАНИЕ_ВЫВОЗА,
        self::STATE_ДОСТАВЛЕНО,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'projects_states';
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
     * Делает выборку статусов проектов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForOperatorForSelect2()
    {
        return ArrayHelper::map(self::find()->where(['in', 'id', self::НАБОР_ОПЕРАТОРА])->all(), 'id', 'name');
    }

    /**
     * Делает выборку статусов проектов и возвращает в виде массива.
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
    public function getCorrespondencePackages()
    {
        return $this->hasMany(CorrespondencePackages::className(), ['state_id' => 'id']);
    }
}
