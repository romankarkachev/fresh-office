<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "edf_tp".
 *
 * @property int $id
 * @property int $ed_id Электронный документ
 * @property int $fkko_id Код ФККО
 * @property string $fkko_name ФККО
 * @property int $dc_id Класс опасности
 * @property int $unit_id Единица измерения
 * @property string $measure Количество
 * @property int $hk_id Вид обращения
 * @property string $price Цена
 * @property string $amount Стоимость
 *
 * @property string $unitName
 * @property string $hkName
 * @property string $dcName
 *
 * @property DangerClasses $dc
 * @property Edf $ed
 * @property Fkko $fkko
 * @property HandlingKinds $hk
 * @property Units $unit
 */
class EdfTp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'edf_tp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ed_id', 'hk_id'], 'required'],
            [['ed_id', 'fkko_id', 'dc_id', 'unit_id', 'hk_id'], 'integer'],
            [['measure', 'price', 'amount'], 'number'],
            [['fkko_name'], 'string', 'max' => 255],
            [['dc_id'], 'exist', 'skipOnError' => true, 'targetClass' => DangerClasses::className(), 'targetAttribute' => ['dc_id' => 'id']],
            [['hk_id'], 'exist', 'skipOnError' => true, 'targetClass' => HandlingKinds::className(), 'targetAttribute' => ['hk_id' => 'id']],
            [['ed_id'], 'exist', 'skipOnError' => true, 'targetClass' => Edf::className(), 'targetAttribute' => ['ed_id' => 'id']],
            [['fkko_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fkko::className(), 'targetAttribute' => ['fkko_id' => 'id']],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => Units::className(), 'targetAttribute' => ['unit_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ed_id' => 'Электронный документ',
            'fkko_id' => 'Код ФККО',
            'fkko_name' => 'ФККО',
            'dc_id' => 'Класс опасности',
            'unit_id' => 'Единица измерения',
            'measure' => 'Количество',
            'hk_id' => 'Вид обращения',
            'price' => 'Цена',
            'amount' => 'Стоимость',
            // вычисляемые поля
            'fkkoName' => 'Код ФККО',
            'dcName' => 'Класс опасности',
            'unitName' => 'Единица измерения',
            'hkName' => 'Вид обращения',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEd()
    {
        return $this->hasOne(Edf::className(), ['id' => 'ed_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkko()
    {
        return $this->hasOne(Fkko::className(), ['id' => 'fkko_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDc()
    {
        return $this->hasOne(DangerClasses::className(), ['id' => 'dc_id']);
    }

    /**
     * Возвращает наименование класса опасности.
     * @return string
     */
    public function getDcName()
    {
        return $this->dc ? $this->dc->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(Units::className(), ['id' => 'unit_id']);
    }

    /**
     * Возвращает наименование единицы измерения.
     * @return string
     */
    public function getUnitName()
    {
        return !empty($this->unit) ? $this->unit->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHk()
    {
        return $this->hasOne(HandlingKinds::className(), ['id' => 'hk_id']);
    }

    /**
     * Возвращает наименование вида обращения с отходом.
     * @return string
     */
    public function getHkName()
    {
        return !empty($this->hk) ? $this->hk->name : '';
    }
}
