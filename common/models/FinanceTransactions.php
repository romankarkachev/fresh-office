<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "finance_transactions".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property int $user_id Пользователь системы-подотчетник
 * @property int $operation Тип операции (1 - выдача подотчет, 2 - возврат подотчета, 3 - авансовый отчет)
 * @property float $amount Сумма
 * @property int $src_id Источник средств (1 - наличные, 2 - карта, 3 - расчетный счет)
 * @property string $comment Комментарий
 *
 * @property string $createdByProfileName
 * @property string $userProfileName
 * @property string $operationName
 * @property string $sourceName
 *
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property User $user
 * @property Profile $userProfile
 */
class FinanceTransactions extends \yii\db\ActiveRecord
{
    /**
     * Возможные значения для поля "Тип операции"
     */
    const OPERATION_ВЫДАЧА_ПОДОТЧЕТ = 1;
    const OPERATION_ВОЗВРАТ_ПОДОТЧЕТА = 2;
    const OPERATION_АВАНСОВЫЙ_ОТЧЕТ = 3;

    /**
     * Возможные значения для поля "Источник средств"
     */
    const SOURCE_НАЛИЧНЫЕ = 1;
    const SOURCE_КАРТА = 2;
    const SOURCE_РАСЧЕТНЫЙ_СЧЕТ = 3;
    const SOURCE_ВТОРСЫРЬЕ = 4;
    const SOURCE_АРЕНДА_ЖИЛЬЯ = 5;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'finance_transactions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'amount'], 'required'],
            [['created_at', 'created_by', 'user_id', 'operation', 'src_id'], 'integer'],
            [['amount'], 'number'],
            [['comment'], 'string'],
            [['comment'], 'trim'],
            [['comment'], 'default', 'value' => null],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
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
            'created_at' => 'Дата и время создания',
            'created_by' => 'Автор создания',
            'user_id' => 'Пользователь системы-подотчетник',
            'operation' => 'Тип операции', // 1 - выдача подотчет, 2 - возврат подотчета, 3 - авансовый отчет
            'amount' => 'Сумма',
            'src_id' => 'Источник средств', // 1 - наличные, 2 - карта, 3 - расчетный счет
            'comment' => 'Комментарий',
            // вычисляемые поля
            'createdByProfileName' => 'Автор',
            'userProfileName' => 'Подотчетное лицо',
            'operationName' => 'Операция',
            'sourceName' => 'Источник средств',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
                'preserveNonEmptyValues' => true,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            switch ($this->operation) {
                case FinanceTransactions::OPERATION_ВОЗВРАТ_ПОДОТЧЕТА:
                case FinanceTransactions::OPERATION_АВАНСОВЫЙ_ОТЧЕТ:
                    // данные операции проводятся со знаком минус
                    $this->amount = -$this->amount;
                    break;
            }

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // пересчитаем баланс подотчетника и сделаем об этом запись
        $balance = $this->user->advanceBalanceCalculated;
        if (false === $balance) {
            // вообще нет транзакций по этому пользователю, просто берем сумму текущей операции
            $balance = $this->amount;
        }

        // если в таблице подотчетников такого пользователя еще нет, то конечно же создаем
        if ($balance !== null) {
            $fah = FinanceAdvanceHolders::findOne(['user_id' => $this->user_id]);
            if ($fah) {
                // пользователь ранее получал авансы, обновляем его баланс
                $fah->updateAttributes(['balance' => $balance]);
            }
            else {
                // пользователь получает деньги подотчет первый раз, создаем запись и назначаем в ней баланс
                (new FinanceAdvanceHolders([
                    'user_id' => $this->user_id,
                    'balance' => $balance,
                ]))->save();
            }
        }
    }

    /**
     * Делает сумму приятной глазу. Если копеек нет, возвращается целое число. Присутствует разделитель разрядов.
     * @param int $amount число, которое необходимо разукрасить
     * @param string $addRub добавлять ли значок рубля в конец
     * @return string
     */
    public static function getPrettyAmount($amount, $addRub = null)
    {
        $addon = '';
        if (!empty($addRub)) {
            switch ($addRub) {
                case 'html':
                    $addon = ' &#8381;';
                    break;
                case 'fontawesome':
                    $addon = ' <i class="fa fa-rub"></i>';
                    break;
                case 'name':
                    $addon = ' руб.';
                    break;
            }
        }

        return str_replace(',00', '', Yii::$app->formatter->asDecimal(abs($amount), 2)) . $addon;
    }

    /**
     * Возвращает массив типов финансовых операций.
     * @return array
     */
    public static function fetchOperations()
    {
        return [
            [
                'id' => self::OPERATION_ВЫДАЧА_ПОДОТЧЕТ,
                'name' => 'Выдача подотчет',
            ],
            [
                'id' => self::OPERATION_ВОЗВРАТ_ПОДОТЧЕТА,
                'name' => 'Возврат подотчета',
            ],
            [
                'id' => self::OPERATION_АВАНСОВЫЙ_ОТЧЕТ,
                'name' => 'Авансовый отчет',
            ],
        ];
    }

    /**
     * Возвращает массив источников средств.
     * @return array
     */
    public static function fetchSources()
    {
        return [
            [
                'id' => self::SOURCE_НАЛИЧНЫЕ,
                'name' => 'Наличные',
            ],
            [
                'id' => self::SOURCE_КАРТА,
                'name' => 'Банковская карта',
            ],
            [
                'id' => self::SOURCE_РАСЧЕТНЫЙ_СЧЕТ,
                'name' => 'Расчетный счет',
            ],
            [
                'id' => self::SOURCE_ВТОРСЫРЬЕ,
                'name' => 'Продажа вторсырья',
            ],
            [
                'id' => self::SOURCE_АРЕНДА_ЖИЛЬЯ,
                'name' => 'Средства от аренды квартир',
            ],
        ];
    }

    /**
     * Делает выборку типов операций и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfOperationsTypesForSelect2()
    {
        $result = self::fetchOperations();
        unset($result[2]);
        return ArrayHelper::map($result, 'id', 'name');
    }

    /**
     * Делает выборку источников средств и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfFundsSourcesForSelect2()
    {
        return ArrayHelper::map(self::fetchSources(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'created_by'])->from(['createdProfile' => 'profile']);
    }

    /**
     * Возвращает имя создателя записи.
     * @return string
     */
    public function getCreatedByProfileName()
    {
        return !empty($this->createdByProfile) ? (!empty($this->createdByProfile->name) ? $this->createdByProfile->name : $this->createdBy->username) : '';
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
        return $this->hasOne(Profile::class, ['user_id' => 'user_id'])->from(['userProfile' => 'profile']);
    }

    /**
     * Возвращает имя пользователя системы, которому принадлежат координаты.
     * @return string
     */
    public function getUserProfileName()
    {
        return $this->userProfile != null ? ($this->userProfile->name != null ? $this->userProfile->name : $this->user->username) : '';
    }

    /**
     * Возвращает наименование операции взаиморасчетов с подотчетынм лицом.
     * @return string
     */
    public function getOperationName()
    {
        $sourceTable = self::fetchOperations();
        $key = array_search($this->operation, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '';
    }

    /**
     * Возвращает наименование источника средств.
     * @return string
     */
    public function getSourceName()
    {
        $sourceTable = self::fetchSources();
        $key = array_search($this->src_id, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '';
    }

}
