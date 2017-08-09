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
 * @property integer $opfh_id
 * @property integer $tax_kind
 * @property integer $ft_id
 * @property integer $pc_id
 * @property integer $state_id
 * @property string $phone
 * @property string $email
 * @property string $contact_person
 * @property string $post
 * @property string $phone_dir
 * @property string $email_dir
 * @property string $contact_person_dir
 * @property string $post_dir
 *
 * @property Opfh $opfh
 * @property FerrymenTypes $ft
 * @property PaymentConditions $pc
 * @property Drivers[] $drivers
 * @property Transport[] $transport
 * @property FerrymenFiles[] $ferrymenFiles
 */
class Ferrymen extends \yii\db\ActiveRecord
{
    /**
     * Статусы перевозчиков, водителей, транспорта
     */
    const STATE_НЕТ_НАРЕКАНИЙ = 1;
    const STATE_ЕСТЬ_ЗАМЕЧАНИЯ = 2;
    const STATE_ЧЕРНЫЙ_СПИСОК = 3;

    /**
     * Статусы плательщиков НДС
     */
    const TAX_KIND_НЕПЛАТЕЛЬЩИК = 0;
    const TAX_KIND_ПЛАТЕЛЬЩИК = 1;

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
            [['fo_id', 'opfh_id', 'tax_kind', 'ft_id', 'pc_id', 'state_id'], 'integer'],
            [['name', 'email', 'email_dir'], 'string', 'max' => 255],
            [['phone', 'contact_person', 'phone_dir', 'contact_person_dir'], 'string', 'max' => 50],
            [['post', 'post_dir'], 'string', 'max' => 100],
            [['opfh_id'], 'exist', 'skipOnError' => true, 'targetClass' => Opfh::className(), 'targetAttribute' => ['opfh_id' => 'id']],
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
            'opfh_id' => 'ОПФХ',
            'tax_kind' => 'Плательщик НДС', // 0 - нет, 1 - да
            'ft_id' => 'Тип',
            'pc_id' => 'Условия оплаты',
            'state_id' => 'Статус', // 1 - нареканий нет, 2 - есть замечания, 3 - черный список
            'phone' => 'Телефоны',
            'email' => 'E-mail',
            'contact_person' => 'Имя',
            'post' => 'Должность',
            'phone_dir' => 'Телефоны',
            'email_dir' => 'E-mail',
            'contact_person_dir' => 'Имя',
            'post_dir' => 'Должность',
            // для сортировки
            'ftName' => 'Тип',
            'pcName' => 'Условия оплаты',
            'stateName' => 'Статус',
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

        if (isset($changedAttributes['state_id'])) {
            Drivers::updateAll([
                'state_id' => $this->state_id,
            ], [
                'ferryman_id' => $this->id,
            ]);

            Transport::updateAll([
                'state_id' => $this->state_id,
            ], [
                'ferryman_id' => $this->id,
            ]);
        }

        return true;
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
     * Возвращает в виде массива разновидности статусов перевозчиков, водителей, транспорта.
     * @return array
     */
    public static function fetchStates()
    {
        return [
            [
                'id' => self::STATE_НЕТ_НАРЕКАНИЙ,
                'name' => 'Нареканий нет',
            ],
            [
                'id' => self::STATE_ЕСТЬ_ЗАМЕЧАНИЯ,
                'name' => 'Есть замечания',
            ],
            [
                'id' => self::STATE_ЧЕРНЫЙ_СПИСОК,
                'name' => 'Черный список',
            ],
        ];
    }

    /**
     * Возвращает в виде массива разновидности плательщиков НДС.
     * @return array
     */
    public static function fetchTaxKinds()
    {
        return [
            [
                'id' => self::TAX_KIND_НЕПЛАТЕЛЬЩИК,
                'name' => 'Неплательщик',
            ],
            [
                'id' => self::TAX_KIND_ПЛАТЕЛЬЩИК,
                'name' => 'Плательщик',
            ],
        ];
    }

    /**
     * Делает выборку статусов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfStatesForSelect2()
    {
        return ArrayHelper::map(self::fetchStates(), 'id', 'name');
    }

    /**
     * Делает выборку плательщиков НДС и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfTaxKindsForSelect2()
    {
        return ArrayHelper::map(self::fetchTaxKinds(), 'id', 'name');
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
     * Возвращает наименование статуса.
     * @return string
     */
    public function getStateName()
    {
        if (null === $this->state_id) {
            return '<не определен>';
        }

        $sourceTable = self::fetchStates();
        $key = array_search($this->state_id, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '';
    }

    /**
     * Возвращает наименование статуса.
     * @param $state_id integer
     * @return string
     */
    public static function getIndepStateName($state_id)
    {
        if ($state_id != null) {
            $sourceTable = self::fetchStates();
            $key = array_search($state_id, array_column($sourceTable, 'id'));
            if (false !== $key) return $sourceTable[$key]['name'];
        }

        return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOpfh()
    {
        return $this->hasOne(Opfh::className(), ['id' => 'opfh_id']);
    }

    /**
     * Возвращает наименование организационно-правовой формы хозяйствования.
     * @return string
     */
    public function getOpfhName()
    {
        return $this->opfh != null ? $this->opfh->name : '';
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerrymenFiles()
    {
        return $this->hasMany(FerrymenFiles::className(), ['ferryman_id' => 'id']);
    }
}
