<?php

namespace common\models;

use Yii;
use common\behaviors\IndexFieldBehavior;

/**
 * This is the model class for table "drivers".
 *
 * @property integer $id
 * @property integer $ferryman_id
 * @property string $surname
 * @property string $name
 * @property string $patronymic
 * @property string $driver_license
 * @property string $dl_issued_at
 * @property string $driver_license_index
 * @property string $phone
 * @property string $pass_serie
 * @property string $pass_num
 * @property string $pass_issued_at
 * @property string $pass_issued_by
 *
 * @property string $ferrymanName
 * @property integer $instrCount
 *
 * @property Ferrymen $ferryman
 * @property DriversInstructings[] $driversInstructings
 */
class Drivers extends \yii\db\ActiveRecord
{
    /**
     * Количество инструктажей, для вложенного подзапроса.
     * Виртуальное поле.
     * @var integer
     */
    public $instrCount;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'drivers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ferryman_id', 'surname', 'name', 'driver_license', 'phone'], 'required'],
            [['ferryman_id'], 'integer'],
            [['dl_issued_at', 'pass_issued_at'], 'safe'],
            [['surname', 'name', 'patronymic'], 'string', 'max' => 50],
            [['driver_license', 'driver_license_index'], 'string', 'max' => 30],
            [['phone'], 'string', 'min' => 15],
            [['pass_serie', 'pass_num'], 'string', 'max' => 10],
            [['pass_issued_by'], 'string', 'max' => 150],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
            // собственные правила валидации
            ['phone', 'validatePhone'],
            ['driver_license', 'validateDriverLicense'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ferryman_id' => 'Перевозчик',
            'surname' => 'Фамилия',
            'name' => 'Имя',
            'patronymic' => 'Отчество',
            'driver_license' => 'Водительское удостоверение',
            'dl_issued_at' => 'ВУ выдано', // Дата выдачи водительского удостоверения
            'phone' => 'Телефон',
            'pass_serie' => 'Серия',
            'pass_num' => 'Номер',
            'pass_issued_at' => 'Дата выдачи',
            'pass_issued_by' => 'Кем выдан',
            // вычисляемые поля
            'ferrymanName' => 'Перевозчик',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'indexField' => [
                'class' => 'common\behaviors\IndexFieldBehavior',
                'in_attribute' => 'driver_license',
                'out_attribute' => 'driver_license_index',
            ]
        ];
    }

    /**
     * Собственное правило валидации для номера телефона.
     */
    public function validatePhone()
    {
        $phone_processed = preg_replace("/[^0-9]/", '', $this->phone);
        if (strlen($phone_processed) < 10)
            $this->addError('phone', 'Номер телефона должен состоять из 10 цифр.');
    }

    /**
     * Собственное правило валидации для номера водительского удостоверения.
     */
    public function validateDriverLicense()
    {
        $query = self::find()->where(['driver_license_index' => IndexFieldBehavior::processValue($this->driver_license)]);
        if ($this->id != null) $query->andWhere(['not in', 'id', $this->id]);
        if ($query->count() > 0)
            $this->addError('driver_license', 'Водитель с таким удостоверением уже существует.');
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением документа

            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = DriversFiles::find()->where(['driver_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            // удаляем инструктажи
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $dis = DriversInstructings::find()->where(['driver_id' => $this->id])->all();
            foreach ($dis as $di) $di->delete();

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
            // убираем из номера телефона все символы кроме цифр
            $this->phone = preg_replace("/[^0-9]/", '', $this->phone);

            return true;
        }
        return false;
    }

    /**
     * Выполняет преобразование номера телефона к удобному для восприятия виду.
     * @param $phone string номер телефона (оригинал)
     * @return string
     */
    public static function normalizePhoneNumber($phone)
    {
        if ($phone != null && $phone != '')
            if (preg_match('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', $phone, $matches))
                return '+7 ('.$matches[1].') '.$matches[2].'-'.$matches[3].'-'.$matches[4];

        // не удалось преобразовать в человеческий вид - отображаем как есть
        return $phone;
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
     * @return \yii\db\ActiveQuery
     */
    public function getDriversInstructings()
    {
        return $this->hasMany(DriversInstructings::className(), ['driver_id' => 'id']);
    }
}
