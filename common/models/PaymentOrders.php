<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "payment_orders".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $creation_type
 * @property integer $state_id
 * @property integer $ferryman_id
 * @property string $projects
 * @property string $cas
 * @property string $vds
 * @property string $amount
 * @property integer $pd_type
 * @property integer $pd_id
 * @property string $payment_date
 * @property integer $emf_sent_at
 * @property integer $approved_at
 * @property string $comment
 *
 * @property string $modelRep
 * @property string $createdByProfileName
 * @property string $stateName
 * @property string $ferrymanName
 * @property string $pdTypeName
 *
 * @property Ferrymen $ferryman
 * @property User $createdBy
 * @property User $createdByProfile
 * @property PaymentOrdersStates $state
 * @property PaymentOrdersFiles[] $paymentOrdersFiles
 */
class PaymentOrders extends \yii\db\ActiveRecord
{
    /**
     * Способы расчетов с перевозчиком.
     */
    const PAYMENT_DESTINATION_ACCOUNT = 1;
    const PAYMENT_DESTINATION_CARD = 2;

    /**
     * Разновидности способов создания ордеров.
     */
    const PAYMENT_ORDER_CREATION_TYPE_ВРУЧНУЮ = 1;
    const PAYMENT_ORDER_CREATION_TYPE_ИМПОРТ_ИЗ_EXCEL = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_orders';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ferryman_id', 'projects'], 'required'],
            [['created_at', 'created_by', 'creation_type', 'state_id', 'ferryman_id', 'pd_type', 'pd_id', 'emf_sent_at', 'approved_at'], 'integer'],
            [['projects', 'cas', 'vds', 'comment'], 'string'],
            [['amount'], 'number'],
            [['payment_date'], 'safe'],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentOrdersStates::className(), 'targetAttribute' => ['state_id' => 'id']],
            // собственные правила валидации
            ['projects', 'validateProjects'],
            ['state_id', 'validateState'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'created_by' => 'Автор создания',
            'creation_type' => 'Способ создания', // 1 - создано вручную, 2 - импорт из файла Excel
            'state_id' => 'Статус',
            'ferryman_id' => 'Перевозчик',
            'projects' => 'Проекты',
            'cas' => 'Контрагенты из проектов',
            'vds' => 'Даты вывоза из проектов',
            'amount' => 'Сумма',
            'pd_type' => 'Способ расчетов', // 1 - банковский счет, 2 - перевод на карту
            'pd_id' => 'Ссылка на банковский счет (номер карты)',
            'payment_date' => 'Дата оплаты',
            'emf_sent_at' => 'Дата и время отправки письма перевозчику',
            'approved_at' => 'Дата и время согласования ордера',
            'comment' => 'Комментарий',
            // вычисляемые поля
            'createdByProfileName' => 'Автор создания',
            'stateName' => 'Статус',
            'ferrymanName' => 'Перевозчик',
        ];
    }

    /**
     * @inheritdoc
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
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function validateProjects()
    {
        $totalAmount = 0;
        $errors = '';
        $projects = explode(',', $this->projects);

        // проверка состоит в том, чтобы определить, верно ли вообще заполнено поле, соответствует ли перевозчик,
        // укзанный в проекте тому, который выбран пользователем, а также заполнено ли поле ТТН
        foreach ($projects as $project) {
            $project_id = trim($project);
            if (is_numeric($project_id) === false) {
                $this->addError('projects', 'Поле заполнено некорректно.');
                return true;
            }

            $subErrors = ''; // возможные ошибки по текущему проекту
            $project_id = intval($project_id);

            // проект должен существовать
            $object = DirectMSSQLQueries::fetchProjectsData($project_id);
            if (count($object) > 0) {
                // проект не должен быть использован ранее
                $query = PaymentOrders::find()->where(['like', 'projects', $project_id]);
                // для существующей записи исключаем из выборки самого себя
                if (!$this->isNewRecord) $query->andWhere('payment_orders.id <> ' . $this->id);
                $existingProjects = $query->all();
                if (count($existingProjects) > 0)
                    $subErrors .= ($subErrors != '' ? ', ' : '') . 'проект уже оплачен ранее';
                unset($query);

                // наименование выбранного перевозчика должно совпадать с наименованием перевозчика в проекте
                if ($object['ferryman'] != $this->ferryman->name_crm)
                    $subErrors .= ($subErrors != '' ? ', ' : '') . 'не соответствует перевозчик';

                // поле ТТН в проекте не должно быть пустым
                if ($object['ttn'] == null || $object['ttn'] == '')
                    $subErrors .= ($subErrors != '' ? ', ' : '') . 'не заполнена ТТН';

                // сумма себестоимости должна совпадать с введенной, ведем подсчет итоговой суммы
                $totalAmount += $object['cost'];
            }
            else $subErrors .= ($subErrors != '' ? ', ' : '') . 'проект не существует';

            if ($subErrors != '') $errors .= ($errors != '' ? ', ' : '') . $project_id . ': ' . $subErrors;
        }

        if ($errors != '') {
            $this->addError('projects', $errors);
        }

        if ($totalAmount > 0 && $totalAmount != $this->amount) {
            $this->addError('amount', 'Введенная сумма не совпадает с себестоимостью всех проектов!');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateState()
    {
        if ($this->state_id == PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ && ($this->comment == null || trim($this->comment == '')))
            $this->addError('comment', 'При отказе ввод причины обязателен.');

        // для сохраненных платежных ордеров с видом оплаты на банковский счет невозможно перевести их в статус Оплачено при отсутствии файлов
        if (!$this->isNewRecord && $this->state_id == PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ &&
            $this->getPaymentOrdersFiles()->count() == 0 && $this->pd_type == self::PAYMENT_DESTINATION_ACCOUNT)
            $this->addError('comment', 'Невозможно отправить на согласование ордер без файлов.');
    }

    /**
     * Возвращает массив со .
     * @return array
     */
    public static function fetchPaymentDestinations()
    {
        return [
            [
                'id' => self::PAYMENT_DESTINATION_ACCOUNT,
                'name' => 'Банковский счет',
            ],
            [
                'id' => self::PAYMENT_DESTINATION_CARD,
                'name' => 'Перевод на карту',
            ],
        ];
    }

    /**
     * Делает выборку способов расчетов с перевозчиками и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::fetchPaymentDestinations(), 'id', 'name');
    }

    /**
     * Выполняет подсчет общей суммы по проектам.
     */
    public function calculateProjectsTotalAmount()
    {
        $projects = explode(',', $this->projects);
        if (count($projects) > 0) {
            $totalAmount = 0;
            foreach ($projects as $project) {
                $project_id = trim($project);
                if (is_numeric($project_id) === false) {
                    // вообще ничего не считаем, если есть хоть одна ошибка
                    $this->amount = 0;
                    return;
                }

                $project_id = intval($project_id);

                // проект должен существовать
                $object = DirectMSSQLQueries::fetchProjectsData($project_id);
                if (count($object) > 0) $totalAmount += $object['cost'];
            }

            $this->amount = $totalAmount;
        }
    }

    /**
     * Собирает наименования контрагентов по идентификаторам проектов.
     */
    public function collectCasVd()
    {
        $projects = explode(',', $this->projects);
        if (count($projects) > 0) {
            $poCas = [];
            $poVds = [];
            foreach ($projects as $project) {
                $project_id = trim($project);
                if (is_numeric($project_id) === false) {
                    // вообще ничего не считаем, если есть хоть одна ошибка
                    $this->amount = 0;
                    return;
                }

                $project_id = intval($project_id);

                // проект должен существовать
                $object = DirectMSSQLQueries::fetchProjectsData($project_id);
                if (count($object) > 0) {
                    if (!in_array($object['ca_name'], $poCas)) $poCas[] = $object['ca_name'];
                    if ($object['vivozdate'] != null) {
                        $date = Yii::$app->formatter->asDate($object['vivozdate'], 'php:d.m.Y');
                        if (!in_array($date, $poVds)) $poVds[] = $date;
                    }
                }
            }

            $this->cas = implode(', ', $poCas);
            $this->vds = implode(', ', $poVds);
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate() {
        if ($this->state_id == PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК && $this->creation_type == PaymentOrders::PAYMENT_ORDER_CREATION_TYPE_ВРУЧНУЮ)
            $this->collectCasVd();

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением объекта

            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = PaymentOrdersFiles::find()->where(['po_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($this->state_id == PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН && $this->payment_date == null)
                $this->payment_date = date('Y-m-d');

            // отмечаем дату и время согласования для будущих подсчетов
            if ($this->state_id == PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ) $this->approved_at = time();

            if ($insert) {
                // выполним отправку письма перевозчику
                // только при создании ордера и только если производится импорт
                if ($this->creation_type == PaymentOrders::PAYMENT_ORDER_CREATION_TYPE_ИМПОРТ_ИЗ_EXCEL &&
                    // сумма по ордеру должна быть положительной
                    $this->amount > 0 &&
                    // у перевозчика должен стоять признак, разрешающий отправлять ему письма
                    $this->ferryman->notify_when_payment_orders_created
                ) {
                    $letter = Yii::$app->mailer->compose([
                        'html' => 'newPaymentOrderHasBeenCreated-ForFerryman-html',
                    ], [
                        'amount' => $this->amount,
                    ])->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNameVictor']])
                    ->setSubject('Предоставьте счет на оплату');

                    if (($this->ferryman->email != null && ($this->ferryman->email) != '') ||
                        ($this->ferryman->email_dir != null && trim($this->ferryman->email_dir) != ''))
                        // если задан хотя бы один Email
                        if ($this->ferryman->email == $this->ferryman->email_dir) {
                            // если они совпадают, то на первый отправляем
                            if ($this->ferryman->email != null && ($this->ferryman->email) != '') $letter->setTo($this->ferryman->email);
                            else $letter->setTo($this->ferryman->email_dir);
                        }
                        else {
                            // если они отличаются, то на оба
                            if ($this->ferryman->email != null && ($this->ferryman->email) != '') $letter->setTo($this->ferryman->email);
                            if ($this->ferryman->email_dir != null && ($this->ferryman->email_dir) != '') $letter->setCc($this->ferryman->email_dir);
                        }

                    if ($letter->send()) $this->emf_sent_at = time();
                }
            }

            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);

        if (isset($changedAttributes['state_id']) && $this->state_id == PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН) {
            // если ордер переведен в статус "Оплачено"
            // ставим в CRM дату оплаты по всем введенным проектам
            $projects = explode(',', $this->projects);
            foreach ($projects as $project) DirectMSSQLQueries::updateProjectsAddOplata($project, $this->payment_date);
        }
    }

    /**
     * Возвращает представление модели.
     * @return string
     */
    public function getModelRep()
    {
        return '№ ' . $this->id . ' от ' . Yii::$app->formatter->asDate($this->created_at, 'php:d.m.Y');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'created_by']);
    }

    /**
     * Возвращает имя создателя записи.
     * @return string
     */
    public function getCreatedByProfileName()
    {
        return $this->createdByProfile != null ? ($this->createdByProfile->name != null ? $this->createdByProfile->name : $this->createdBy->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(PaymentOrdersStates::className(), ['id' => 'state_id']);
    }

    /**
     * Возвращает наименование статуса.
     * @return string
     */
    public function getStateName()
    {
        return $this->state != null ? $this->state->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerryman()
    {
        return $this->hasOne(Ferrymen::className(), ['id' => 'ferryman_id']);
    }

    /**
     * Возвращает наименование перевозчика.
     * @return string
     */
    public function getFerrymanName()
    {
        return $this->ferryman != null ? $this->ferryman->name : '';
    }

    /**
     * Возвращает наименование способа расчетов с перевозчиком.
     * @return string
     */
    public function getPdTypeName()
    {
        if (null === $this->pd_type) {
            return '<не определен>';
        }

        $sourceTable = self::fetchPaymentDestinations();
        $key = array_search($this->pd_type, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentOrdersFiles()
    {
        return $this->hasMany(PaymentOrdersFiles::className(), ['po_id' => 'id']);
    }
}
