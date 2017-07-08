<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "transport_requests".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $finished_at
 * @property integer $customer_id
 * @property string $customer_name
 * @property integer $region_id
 * @property integer $city_id
 * @property string $address
 * @property integer $state_id
 * @property string $comment_manager
 * @property string $comment_logist
 * @property integer $our_loading
 * @property integer $periodicity_id
 * @property string $special_conditions
 * @property integer $spec_free
 * @property string $spec_hose
 * @property string $spec_cond
 *
 * @property string $createdByName
 * @property string $stateName
 *
 * @property PeriodicityKinds $periodicity
 * @property Cities $city
 * @property User $createdBy
 * @property Regions $region
 * @property TransportRequestsStates $state
 * @property TransportRequestsFiles[] $transportRequestsFiles
 * @property TransportRequestsTransport[] $transportRequestsTransports
 * @property TransportRequestsWaste[] $transportRequestsWastes
 */
class TransportRequests extends \yii\db\ActiveRecord
{
    /**
     * Булевые значения
     */
    const VALUE_НЕТ = 0;
    const VALUE_ДА = 1;

    /**
     * Табличная часть "Отходы".
     * @var array
     */
    public $tpWaste;

    /**
     * Массив ошибок при заполнении табличной части "Отходы".
     * @var array
     */
    public $tpWasteErrors;

    /**
     * Табличная часть "Транспорт".
     * @var array
     */
    public $tpTransport;

    /**
     * Массив ошибок при заполнении табличной части "Транспорт".
     * @var array
     */
    public $tpTransportErrors;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transport_requests';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'region_id', 'city_id', 'state_id'], 'required'],
            [['created_at', 'created_by', 'finished_at', 'customer_id', 'region_id', 'city_id', 'state_id', 'our_loading', 'periodicity_id', 'spec_free'], 'integer'],
            [['comment_manager', 'comment_logist', 'special_conditions', 'spec_cond'], 'string'],
            [['customer_name', 'address'], 'string', 'max' => 255],
            [['spec_hose'], 'string', 'max' => 50],
            [['periodicity_id'], 'exist', 'skipOnError' => true, 'targetClass' => PeriodicityKinds::className(), 'targetAttribute' => ['periodicity_id' => 'id']],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cities::className(), 'targetAttribute' => ['city_id' => 'city_id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['region_id'], 'exist', 'skipOnError' => true, 'targetClass' => Regions::className(), 'targetAttribute' => ['region_id' => 'region_id']],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransportRequestsStates::className(), 'targetAttribute' => ['state_id' => 'id']],
            // собственные правила валидации
            ['tpWaste', 'validateWaste'],
            ['tpTransport', 'validateTransport'],
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
            'finished_at' => 'Дата и время закрытия заявки',
            'customer_id' => 'Контрагент',
            'customer_name' => 'Контрагент',
            'region_id' => 'Регион',
            'city_id' => 'Город',
            'address' => 'Адрес',
            'state_id' => 'Статус',
            'comment_manager' => 'Комментарий менеджера',
            'comment_logist' => 'Комментарий логиста',
            'our_loading' => 'Необходимость нашей погрузки', // 0 - нет, 1 - да
            'periodicity_id' => 'Периодичность вывоза',
            'special_conditions' => 'Особые условия',
            'spec_free' => 'Наличие свободного подъезда', // 0 - нет, 1 - да
            'spec_hose' => 'Длина шланга',
            'spec_cond' => 'Особые условия',
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
     * Возвращает массив булевых значений.
     * @return array
     */
    public static function fetchBoolean()
    {
        return [
            [
                'id' => self::VALUE_НЕТ,
                'name' => 'Нет',
            ],
            [
                'id' => self::VALUE_ДА,
                'name' => 'Да',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            return true;
        }
        return false;
    }

    /**
     * Удаление связанных объектов перед удалением текущего.
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = TransportFiles::find()->where(['transport_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            // удаляем табличную часть "Отходы"
            TransportRequestsWaste::deleteAll(['tr_id' => $this->id]);
            // удаляем табличную часть "Транспорт"
            TransportRequestsTransport::deleteAll(['tr_id' => $this->id]);

            return true;
        }

        return false;
    }

    /**
     * Собственное правило валидации для табличной части "Отходы".
     */
    public function validateWaste()
    {
        if (count($this->tpWaste) > 0) {
            $row_numbers = [];
            $iterator = 1;
            foreach ($this->tpWaste as $index => $item) {
                $model = new TransportRequestsWaste();
                $model->attributes  = $item;
                if (!$model->validate(['fkko_name', 'unit_id', 'measure'])) {
                    $row_numbers[] = $iterator;
                }
                $iterator++;
            }
            if (count($row_numbers) > 0) $this->addError('tpWasteErrors', 'Не все обязательные поля в табличной части заполнены! Строки: '.implode(', ', $row_numbers).'.');
        }
    }

    /**
     * Собственное правило валидации для табличной части "Транспорт".
     */
    public function validateTransport()
    {
        if (count($this->tpTransport) > 0) {
            $row_numbers = [];
            $iterator = 1;
            foreach ($this->tpTransport as $index => $item) {
                $oa = new TransportRequestsTransport();
                $oa->attributes  = $item;
                if (!$oa->validate(['tt_id'])) {
                    $row_numbers[] = $iterator;
                }
                $iterator++;
            }
            if (count($row_numbers) > 0) $this->addError('tpTransport', 'Не все обязательные поля в табличной части заполнены! Строки: '.implode(', ', $row_numbers).'.');
        }
    }

    /**
     * Превращает данные из массива идентификаторов в массив моделей TransportRequestsWaste.
     * @return array
     */
    public function makeWasteModelsFromPostArray()
    {
        $result = [];
        if (is_array($this->tpWaste)) if (count($this->tpWaste) > 0) {
            foreach ($this->tpWaste as $index => $item) {
                $newPacktingType = trim($item['newPacktingType']);
                // проверим необходимость создания вида упаковки
                if (intval($item['packing_id']) == 0 && $newPacktingType != '') {
                    $packing = new PackingTypes();
                    $packing->name = $newPacktingType;
                    if ($packing->save()) {
                        // меняем значение переименной на текущем шаге, и в общем массиве, потому что это разные данные
                        $this->tpWaste[$index]['packing_id'] = $packing->id;
                        $item['packing_id'] = $packing->id;
                    }
                }

                $dtp = new TransportRequestsWaste();
                $dtp->attributes = $item;
                $dtp->tr_id = $this->id;
                $result[] = $dtp;
            }
        }

        return $result;
    }

    /**
     * Превращает данные из массива идентификаторов в массив моделей TransportRequestsTransport.
     * @return array
     */
    public function makeTransportModelsFromPostArray()
    {
        $result = [];
        if (is_array($this->tpTransport)) if (count($this->tpTransport) > 0) {
            foreach ($this->tpTransport as $index => $item) {
                $dtp = new TransportRequestsTransport();
                $dtp->attributes = $item;
                $dtp->tr_id = $this->id;
                $result[] = $dtp;
            }
        }

        return $result;
    }

    /**
     * Делает выборку булевых значений и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfBooleanForSelect2()
    {
        return ArrayHelper::map(self::fetchBoolean(), 'id', 'name');
    }

    /**
     * Делает выборку городов текущего региона и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public function arrayMapOfCitiesForSelect2()
    {
        return ArrayHelper::map(Cities::find()->select(['id' => 'city_id', 'name'])->where(['region_id' => $this->region_id])->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeriodicity()
    {
        return $this->hasOne(PeriodicityKinds::className(), ['id' => 'periodicity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(Cities::className(), ['city_id' => 'city_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * Возвращает наименование .
     * @return string
     */
    public function getCreatedByName()
    {
        return $this->created_by == null ? '' : ($this->createdBy->profile == null ? $this->createdBy->username : $this->createdBy->profile->name);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Regions::className(), ['region_id' => 'region_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(TransportRequestsStates::className(), ['id' => 'state_id']);
    }

    /**
     * Возвращает наименование статуса запроса.
     * @return string
     */
    public function getStateName()
    {
        return $this->state != null ? $this->state->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportRequestsFiles()
    {
        return $this->hasMany(TransportRequestsFiles::className(), ['tr_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportRequestsTransports()
    {
        return $this->hasMany(TransportRequestsTransport::className(), ['tr_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportRequestsWastes()
    {
        return $this->hasMany(TransportRequestsWaste::className(), ['tr_id' => 'id']);
    }
}
