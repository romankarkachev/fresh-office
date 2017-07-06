<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ferrymen".
 *
 * @property integer $id
 * @property integer $fo_id
 * @property string $name
 * @property integer $ft_id
 * @property integer $pc_id
 * @property string $phone
 * @property string $email
 * @property string $contact_person
 *
 * @property PaymentConditions $pc
 * @property FerrymenTypes $ft
 * @property Drivers[] $drivers
 * @property Transport[] $transport
 */
class Ferrymen extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ferrymen';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'ft_id', 'pc_id'], 'required'],
            [['fo_id', 'ft_id', 'pc_id'], 'integer'],
            [['name', 'email'], 'string', 'max' => 255],
            [['phone', 'contact_person'], 'string', 'max' => 50],
            [['pc_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentConditions::className(), 'targetAttribute' => ['pc_id' => 'id']],
            [['ft_id'], 'exist', 'skipOnError' => true, 'targetClass' => FerrymenTypes::className(), 'targetAttribute' => ['ft_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fo_id' => 'Идентификатор в Fresh Office',
            'name' => 'Наименование',
            'ft_id' => 'Тип',
            'pc_id' => 'Условия оплаты',
            'phone' => 'Телефоны',
            'email' => 'E-mail',
            'contact_person' => 'Контактное лицо',
            // для сортировки
            'ftName' => 'Тип',
            'pcName' => 'Условия оплаты',
        ];
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
            $files = FerrymenFiles::find()->where(['ferryman_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            // удаляем водителей
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $drivers = Drivers::find()->where(['ferryman_id' => $this->id])->all();
            foreach ($drivers as $driver) $driver->delete();

            // удаляем транспорт
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $transports = Transport::find()->where(['ferryman_id' => $this->id])->all();
            foreach ($transports as $transport) $transport->delete();

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);

        //return true;
    }

    /**
     * Форматирует номер телефона, переданный в параметрах как число и возвращает в виде +7 (ххх) ххх-хх-хх.
     * @param $phone string
     * @return string
     */
    public static function normalizePhone($phone)
    {
        $result = '<нет номера телефона>';
        if ($phone != null && $phone != '')
            if (preg_match('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', $phone, $matches)) {
                $result = '+7 (' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3] . '-' . $matches[4];
            }
            else
                // не удалось преобразовать в человеческий вид - отображаем как есть
                $result = $phone;
        return $result;
    }

    /**
     * Делает выборку перевозчиков и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->orderBy('name')->all(), 'id', 'name');
    }

    /**
     * Делает выборку водителей и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public function arrayMapOfDriversForSelect2()
    {
        return ArrayHelper::map(Drivers::find()->select(['id', 'name' => 'CONCAT(surname, " ", name, " ", patronymic)'])->where(['ferryman_id' => $this->id])->all(), 'id', 'name');
    }

    /**
     * Делает выборку автомобилей и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public function arrayMapOfTransportForSelect2()
    {
        return ArrayHelper::map(Transport::find()->where(['ferryman_id' => $this->id])->all(), 'id', 'representation');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFt()
    {
        return $this->hasOne(FerrymenTypes::className(), ['id' => 'ft_id']);
    }

    /**
     * Возвращает наименование типа перевозчика.
     * @return string
     */
    public function getFtName()
    {
        return $this->ft != null ? $this->ft->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPc()
    {
        return $this->hasOne(PaymentConditions::className(), ['id' => 'pc_id']);
    }

    /**
     * Возвращает наименование условия оплаты.
     * @return string
     */
    public function getPcName()
    {
        return $this->pc != null ? $this->pc->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDrivers()
    {
        return $this->hasMany(Drivers::className(), ['ferryman_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransport()
    {
        return $this->hasMany(Transport::className(), ['ferryman_id' => 'id']);
    }
}
