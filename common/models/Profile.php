<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use dektrium\user\models\Profile as BaseProfile;

/**
 * Модель для таблицы "profile".
 *
 * @property int $user_id
 * @property string $name
 * @property int $fo_id идентификатор пользователя в CRM Fresh Office
 * @property int $limit_cp_me Лимит отправок через курьерскую службу Major Express
 * @property int $notify_when_cp Признак необходимости отправлять уведомление менеджеру, когда для него создается пакет корреспонденции
 * @property float $po_maa Максимальная сумма платежного ордера, которая может быть согласована пользователем без руководства
 * @property int $can_fod Возможность делегировать свои финансовые обязательства другому подотчетному лицу
 *
 * @property array $departments
 * @property array $poEis
 * @property array $poEiForApproving
 *
 * @property User $user
 */
class Profile extends BaseProfile
{
    /**
     * @var array отделы, к которым относится пользователь
     */
    public $departments;

    /**
     * @var array статьи расходов, доступные пользователю
     */
    public $poEis;

    /**
     * @var array статьи расходов, доступные бухгалтеру для согласования
     */
    public $poEiForApproving;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['name'], 'trim'],
            [['fo_id', 'limit_cp_me', 'can_fod'], 'integer'],
            ['notify_when_cp', 'boolean'],
            [['po_maa'], 'number'],
            [['departments', 'poEis', 'poEiForApproving'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'fo_id' => 'Пользователь Fresh Office',
            'limit_cp_me' => 'Лимит отправок Major Express',
            'notify_when_cp' => 'Уведомлять при создании пакета корр.',
            'po_maa' => 'Максимальная сумма платежного ордера, которая может быть согласована пользователем без руководства',
            'can_fod' => 'Возможность делегировать свои финансовые обязательства другому подотчетному лицу',
            // виртуальные поля
            'departments' => 'Отделы',
            'poEis' => 'Статьи бюджета для создания',
            'poEiForApproving' => 'Статьи бюджета для согласования',
        ]);
    }
}
