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
     * Таблица в MS SQL - LIST_SPR_PRIZNAK_PROJECT.
     */
    const STATE_СЧЕТ_ОЖИДАЕТ_ОПЛАТЫ = 4;
    const STATE_ОПЛАЧЕНО = 5;
    const STATE_СОГЛАСОВАНИЕ_ВЫВОЗА = 6;
    const STATE_НЕВОСТРЕБОВАНО = 7;
    const STATE_ВЫВОЗ_ЗАВЕРШЕН = 13;
    const STATE_ОДОБРЕНО_ПРОИЗВОДСТВОМ = 14;
    const STATE_НЕСОВПАДЕНИЕ = 15;
    const STATE_ЗАКРЫТИЕ_СЧЕТА = 17;
    const STATE_ОТДАНО_НА_ОТПРАВКУ = 18; // бухгалтер отнес менеджеру первичку
    const STATE_ОТПРАВЛЕНО = 19;
    const STATE_ДОСТАВЛЕНО = 20;
    const STATE_ОЖИДАЕТ_ПРИСУТСТВИЕ_ФОТО_ВИДЕО = 23;
    const STATE_ЗАВЕРШЕНО = 25;
    const STATE_ОТКАЗ_КЛИЕНТА = 26;
    const STATE_НЕВЕРНОЕ_ОФОРМЛЕНИЕ_ЗАЯВКИ = 27;
    const STATE_САМОПРИВОЗ_ОДОБРЕН = 28;
    const STATE_ТРАНСПОРТ_ЗАКАЗАН = 30;
    const STATE_ЕДЕТ_К_ЗАКАЗЧИКУ = 31;
    const STATE_У_ЗАКАЗЧИКА = 32;
    const STATE_ЕДЕТ_НА_СКЛАД = 33;
    const STATE_НА_СКЛАДЕ = 34;
    const STATE_ДЕЖУРНЫЙ_МЕНЕДЖЕР = 36;
    const STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ = 38; // документы ушли от бухгалтера, но менеджер с ними еще не разбирался
    const STATE_ОТЛОЖЕНО_КЛИЕНТОМ = 40;
    const STATE_ОЖИДАЕТ_ОТПРАВКИ = 43; // менеджер разобрался с документами, чуть ли не вложил в конверты пакеты документов
    const STATE_ОПАНЬКИ = 44;
    const STATE_СЭД = 45;
    const STATE_ОТДАНО_НА_ПОДПИСЬ = 46;
    const STATE_ДОКУМЕНТЫ_НА_СОГЛАСОВАНИИ_У_КЛИЕНТА = 47;
    const STATE_ИСПОЛНЕН = 50;

    const НАБОР_ОПЕРАТОРА = [
        self::STATE_ОЖИДАЕТ_ОТПРАВКИ,
        self::STATE_ОТПРАВЛЕНО,
        self::STATE_ДОСТАВЛЕНО,
        self::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ,
    ];

    const НАБОР_ПОЛНЫЙ = [
        self::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ,
        self::STATE_ОЖИДАЕТ_ОТПРАВКИ,
        self::STATE_ОТПРАВЛЕНО,
        self::STATE_ДОСТАВЛЕНО,
        self::STATE_СЧЕТ_ОЖИДАЕТ_ОПЛАТЫ,
        self::STATE_ОПЛАЧЕНО,
        self::STATE_ЗАКРЫТИЕ_СЧЕТА,
        self::STATE_ЗАВЕРШЕНО,
    ];

    const НАБОР_ДОПУСТИМЫХ_СТАТУСОВ_ПРОИЗВОДСТВО = [
        self::STATE_САМОПРИВОЗ_ОДОБРЕН,
        self::STATE_ТРАНСПОРТ_ЗАКАЗАН,
        self::STATE_ЕДЕТ_К_ЗАКАЗЧИКУ,
        self::STATE_У_ЗАКАЗЧИКА,
        self::STATE_ЕДЕТ_НА_СКЛАД,
        self::STATE_НА_СКЛАДЕ,
    ];

    /**
     * /services/notify-about-projects-outdated-by-custom-time
     */
    const НАБОР_ИСКЛЮЧЕНИЙ_ДЛЯ_ОПОВЕЩЕНИЯ_О_ПРОСРОЧЕННЫХ_ПРОЕКТАХ = [
        self::STATE_ЗАВЕРШЕНО,
        self::STATE_ОТКАЗ_КЛИЕНТА,
        self::STATE_ОТЛОЖЕНО_КЛИЕНТОМ,
        self::STATE_НЕВЕРНОЕ_ОФОРМЛЕНИЕ_ЗАЯВКИ,
        self::STATE_ОЖИДАЕТ_ПРИСУТСТВИЕ_ФОТО_ВИДЕО,
    ];

    /**
     * /reports/pbx-calls-has-tasks-assigned
     */
    const НАБОР_СТАТУСОВ_НЕЗАВЕРШЕННЫЕ_ПРОЕКТЫ = [
        self::STATE_ЗАВЕРШЕНО,
        self::STATE_ОТКАЗ_КЛИЕНТА,
        self::STATE_ОТЛОЖЕНО_КЛИЕНТОМ,
        self::STATE_НЕВЕРНОЕ_ОФОРМЛЕНИЕ_ЗАЯВКИ,
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
        return ArrayHelper::map(self::find()->where(['in', 'id', self::НАБОР_ПОЛНЫЙ])->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorrespondencePackages()
    {
        return $this->hasMany(CorrespondencePackages::className(), ['state_id' => 'id']);
    }
}
