<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "finance_advance_holders".
 *
 * @property int $id
 * @property int $user_id Пользователь системы-подотчетник
 * @property float $balance Баланс (остаток, состояние взаиморасчетов)
 *
 * @property string $userProfileName
 * @property string $lastTransactionRep
 *
 * @property User $user
 * @property Profile $userProfile
 * @property FinanceTransactions $lastTransaction
 */
class FinanceAdvanceHolders extends \yii\db\ActiveRecord
{
    /**
     * @var string виртуальное поле для информации о последней транзакции
     */
    public $lastTransaction;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'finance_advance_holders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'balance'], 'required'],
            [['user_id'], 'integer'],
            [['balance'], 'number'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь системы-подотчетник',
            'balance' => 'Баланс (остаток, состояние взаиморасчетов)',
        ];
    }

    /**
     * Делает выборку подотчетников (пользователей веб-приложения) и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfHoldersForSelect2()
    {
        return ArrayHelper::map(self::find()->select([
            'id' => self::tableName() . '.`user_id`',
            'name' => Profile::tableName() . '.`name`',
        ])->leftJoin(Profile::tableName(), Profile::tableName() . '.`user_id` = ' . self::tableName() . '.`user_id`')
            ->orderBy(Profile::tableName() . '.`name`')->where([self::tableName() . '.`user_id`' => self::find()->select('user_id')->groupBy('user_id')])->asArray()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'user_id']);
    }

    /**
     * Возвращает имя пользователя системы, которому принадлежат координаты.
     * @return string
     */
    public function getUserProfileName()
    {
        return !empty($this->userProfile) ? (!empty($this->userProfile->name) ? $this->userProfile->name : $this->user->username) : '';
    }

    /**
     * Возвращает представление последней проведенной с участием данного подотчетника транзакции.
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getLastTransactionRep()
    {
        $lastTransaction = explode(mb_chr(0x2219, 'UTF-8'), $this->lastTransaction);
        $result = '';
        if (!empty($lastTransaction)) {
            if (isset($lastTransaction[0])) {
                switch ($lastTransaction[0]) {
                    case FinanceTransactions::OPERATION_ВЫДАЧА_ПОДОТЧЕТ:
                        $result .= 'Получил ';
                        break;
                    case FinanceTransactions::OPERATION_ВОЗВРАТ_ПОДОТЧЕТА:
                        $result .= 'Вернул ';
                        break;
                    case FinanceTransactions::OPERATION_АВАНСОВЫЙ_ОТЧЕТ:
                        $result .= 'Отчитался за ';
                        break;
                }
            }

            if (isset($lastTransaction[1])) {
                $result .= FinanceTransactions::getPrettyAmount($lastTransaction[1], 'html');
            }

            if (isset($lastTransaction[2])) {
                // вывод даты и времени
                $dateFormat = '';
                $createdAt = $lastTransaction[2];
                $now = time();
                if (Yii::$app->formatter->asDate($createdAt, 'php:d.m.Y') == Yii::$app->formatter->asDate($now, 'php:d.m.Y')) {
                    // операция проведена сегодня
                    $dateFormat = 'php: в H:i';
                }
                elseif (Yii::$app->formatter->asDate($createdAt, 'php:m.Y') == Yii::$app->formatter->asDate($now, 'php:m.Y')) {
                    // операция проведена в этом месяце
                    $dateFormat = 'php: d числа в H:i';
                }
                elseif (Yii::$app->formatter->asDate($createdAt, 'php:Y') == Yii::$app->formatter->asDate($now, 'php:Y')) {
                    // операция проведена в этом году
                    $dateFormat = 'php: d.m в H:i';
                }
                else {
                    // операция проведена в другое время
                    $dateFormat = 'php: d.m.y в H:i';
                }

                if (!empty($dateFormat)) {
                    $result .= Yii::$app->formatter->asDate($createdAt, $dateFormat);
                }
            }
        }

        return $result;
    }
}
