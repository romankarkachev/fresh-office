<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\Transaction;

/**
 * Форма для передачи долга от одного подотчетного лица другому.
 *
 * @property string $senderProfileName
 * @property string $receiverProfileName
 */
class FinanceObligationDelegationForm extends Model
{
    /**
     * @var integer отправитель и получатель
     */
    public $sender_id;
    public $receiver_id;

    /**
     * @var double сумма задолженности и сумма передаваемых обязательств
     */
    public $amount;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receiver_id', 'amount'], 'required'],
            [['sender_id', 'receiver_id'], 'integer'],
            ['amount', 'number', 'min' => .01], // передать можно минимум 1 копейку
            [['sender_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['sender_id' => 'id']],
            [['receiver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['receiver_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'sender_id' => 'Отправитель',
            'receiver_id' => 'Получатель',
            'amount' => 'Сумма',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_VALIDATE => ['sender_id'],
                ],
            ],
        ];
    }

    /**
     * Возвращает имя отправителя.
     * @return string
     */
    public function getSenderProfileName()
    {
        $model = Profile::findOne($this->sender_id);
        if ($model) {
            return $model->name;
        }
        else {
            return '';
        }
    }

    /**
     * Возвращает имя получателя.
     * @return string
     */
    public function getReceiverProfileName()
    {
        $model = Profile::findOne($this->receiver_id);
        if ($model) {
            return $model->name;
        }
        else {
            return '';
        }
    }

    /**
     * Выполняет передачу денежных средств и обязательств по ним между пользователями.
     * @return array
     * @throws \Throwable
     */
    public function delegateFinanceObligations()
    {
        $success = true;
        $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
        try {
            $senderName = $this->senderProfileName;
            $receiverName = $this->receiverProfileName;

            // одна операция на отправителя средств
            (new FinanceTransactions([
                'user_id' => $this->sender_id,
                'operation' => FinanceTransactions::OPERATION_ВОЗВРАТ_ПОДОТЧЕТА,
                'amount' => $this->amount,
                //'src_id' => 'Источник средств', // 1 - наличные, 2 - карта, 3 - расчетный счет
                'comment' => 'Делегирование финансовых обязательств' .
                    (!empty($receiverName) ? ' пользователю ' . $receiverName . ' (ID ' . $this->receiver_id . ')' : '') .
                    '. Задолженность до передачи: ' . Yii::$app->formatter->asDecimal($this->amount) . ' руб.',
            ]))->save() ? null : $success = false;

            if ($success) {
                // и одна операция на получателя средств
                (new FinanceTransactions([
                    'user_id' => $this->receiver_id,
                    'operation' => FinanceTransactions::OPERATION_ВЫДАЧА_ПОДОТЧЕТ,
                    'amount' => $this->amount,
                    //'src_id' => '',
                    'comment' => 'Приняты финансовые обязательства' . (!empty($senderName) ? ' от пользователя ' . $senderName . ' (ID ' . $this->sender_id . ')' : '') . '.',
                ]))->save() ? null : $success = false;
            }

            $success ? $transaction->commit() : $transaction->rollBack();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        if ($success) {
            return [
                'result' => true,
                'message' => 'Передача финансовых обязательств в сумме ' .
                    Yii::$app->formatter->asDecimal($this->amount) . ' руб. от пользователя ' . $senderName . ' пользователю ' .
                    $receiverName . ' выполнена успешно.',
            ];
        }
        else {
            return [
                'result' => false,
                'message' => 'Не удалось передать ' . Yii::$app->formatter->asDecimal($this->amount) .
                    ' руб. от пользователя ' . $senderName . ' пользователю ' . $receiverName . '.',
            ];
        }
    }
}
