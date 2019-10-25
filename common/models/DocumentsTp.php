<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "documents_tp".
 *
 * @property int $id
 * @property int $doc_id Документ
 * @property string $name Наименование
 * @property string $quantity Количество
 * @property int $unit_id Единица измерения
 * @property int $hk_id Вид обращения
 * @property int $dc_id Класс опасности
 * @property int $fkko_id Код ФККО
 * @property string $src_dc Класс опасности
 * @property string $src_unit Единица измерения из источника
 * @property string $src_uw Способ утилизации из источника
 * @property string $src_name Наименование из источника
 * @property int $fo_id Код из Fresh Office
 * @property int $is_printable Выводить на печать
 *
 * @property string $unitName
 * @property string $hkName
 * @property string $dcName
 * @property string $fkkoName
 *
 * @property Documents $doc
 * @property Units $unit
 * @property HandlingKinds $hk
 * @property DangerClasses $dc
 * @property Fkko $fkko
 */
class DocumentsTp extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'documents_tp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['doc_id'], 'required'],
            [['doc_id', 'unit_id', 'hk_id', 'dc_id', 'fkko_id', 'fo_id', 'is_printable'], 'integer'],
            [['quantity'], 'number'],
            [['name', 'src_name'], 'string', 'max' => 255],
            [['src_dc'], 'string', 'max' => 10],
            [['src_unit'], 'string', 'max' => 30],
            [['src_uw'], 'string', 'max' => 50],
            [['name', 'src_dc', 'src_unit', 'src_uw', 'src_name'], 'trim'],
            [['name', 'src_dc', 'src_unit', 'src_uw', 'src_name'], 'default', 'value' => null],
            [['doc_id'], 'exist', 'skipOnError' => true, 'targetClass' => Documents::class, 'targetAttribute' => ['doc_id' => 'id']],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => Units::class, 'targetAttribute' => ['unit_id' => 'id']],
            [['hk_id'], 'exist', 'skipOnError' => true, 'targetClass' => HandlingKinds::class, 'targetAttribute' => ['hk_id' => 'id']],
            [['dc_id'], 'exist', 'skipOnError' => true, 'targetClass' => DangerClasses::class, 'targetAttribute' => ['dc_id' => 'id']],
            [['fkko_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fkko::class, 'targetAttribute' => ['fkko_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'doc_id' => 'Документ',
            'name' => 'Наименование',
            'quantity' => 'Количество',
            'unit_id' => 'Единица измерения',
            'hk_id' => 'Вид обращения',
            'dc_id' => 'Класс опасности',
            'fkko_id' => 'Код ФККО',
            'src_dc' => 'Класс опасности',
            'src_unit' => 'Единица измерения из источника',
            'src_uw' => 'Способ утилизации из источника',
            'src_name' => 'Наименование из источника',
            'fo_id' => 'Код из Fresh Office',
            'is_printable' => 'Выводить на печать',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDoc()
    {
        return $this->hasOne(Documents::class, ['id' => 'doc_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(Units::class, ['id' => 'unit_id']);
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
        return $this->hasOne(HandlingKinds::class, ['id' => 'hk_id']);
    }

    /**
     * Возвращает наименование вида обращения с отходом.
     * @return string
     */
    public function getHkName()
    {
        return !empty($this->hk) ? $this->hk->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDc()
    {
        return $this->hasOne(DangerClasses::class, ['id' => 'dc_id']);
    }

    /**
     * Возвращает наименование класса опасности.
     * @return string
     */
    public function getDcName()
    {
        return !empty($this->dc) ? $this->dc->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkko()
    {
        return $this->hasOne(Fkko::class, ['id' => 'fkko_id']);
    }

    /**
     * Возвращает представление ФККО.
     * @return string
     */
    public function getFkkoRep()
    {
        return !empty($this->fkko) ? (!empty($this->fkko->fkko_code) ? $this->fkko->fkko_code : '') . $this->fkko->fkko_name : '';
    }

    /**
     * Возвращает код ФККО.
     * @return string
     */
    public function getFkkoCode()
    {
        return !empty($this->fkko) ? $this->fkko->fkko_code : '';
    }

    /**
     * Возвращает наименование ФККО.
     * @return string
     */
    public function getFkkoName()
    {
        return !empty($this->fkko) ? $this->fkko->fkko_name : '';
    }
}
