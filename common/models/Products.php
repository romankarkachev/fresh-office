<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "products".
 *
 * @property int $id
 * @property string $name Наименование
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property int $is_deleted 0 - активен, 1 - пометка удаления
 * @property int $type 0 - отходы, 1 - товары (услуги)
 * @property int $unit_id Единица измерения
 * @property int $hk_id Вид обращения
 * @property int $dc_id Класс опасности
 * @property int $fkko_id Код ФККО
 * @property string $src_unit Единица измерения
 * @property string $src_uw Способ утилизации
 * @property string $src_dc Класс опасности
 * @property string $src_fkko Код по ФККО
 * @property string $fkko_date Дата внесения в ФККО
 * @property int $fo_id Код из Fresh Office
 * @property string $fo_name Наименование из Fresh Office
 * @property string $fo_fkko Код ФККО из Fresh Office
 *
 * @property Fkko $fkko
 * @property User $createdBy
 * @property DangerClasses $dc
 * @property HandlingKinds $hk
 * @property Units $unit
 * @property DocumentsTp[] $documentsTps
 */
class Products extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'fo_name'], 'string'],
            [['created_at', 'created_by', 'is_deleted', 'type', 'unit_id', 'hk_id', 'dc_id', 'fkko_id', 'fo_id'], 'integer'],
            [['fkko_date'], 'safe'],
            [['src_unit'], 'string', 'max' => 30],
            [['src_uw'], 'string', 'max' => 50],
            [['src_dc'], 'string', 'max' => 10],
            [['src_fkko'], 'string', 'max' => 11],
            [['fo_fkko'], 'string', 'max' => 20],
            [['src_unit', 'src_uw', 'src_dc', 'src_fkko', 'fkko_date', 'fo_name', 'fo_fkko'], 'trim'],
            [['src_unit', 'src_uw', 'src_dc', 'src_fkko', 'fkko_date', 'fo_name', 'fo_fkko'], 'default', 'value' => null],
            [['fkko_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fkko::class, 'targetAttribute' => ['fkko_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['dc_id'], 'exist', 'skipOnError' => true, 'targetClass' => DangerClasses::class, 'targetAttribute' => ['dc_id' => 'id']],
            [['hk_id'], 'exist', 'skipOnError' => true, 'targetClass' => HandlingKinds::class, 'targetAttribute' => ['hk_id' => 'id']],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => Units::class, 'targetAttribute' => ['unit_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'created_at' => 'Дата и время создания',
            'created_by' => 'Автор создания',
            'is_deleted' => '0 - активен, 1 - пометка удаления',
            'type' => '0 - отходы, 1 - товары (услуги)',
            'unit_id' => 'Единица измерения',
            'hk_id' => 'Вид обращения',
            'dc_id' => 'Класс опасности',
            'fkko_id' => 'Код ФККО',
            'src_unit' => 'Единица измерения',
            'src_uw' => 'Способ утилизации',
            'src_dc' => 'Класс опасности',
            'src_fkko' => 'Код по ФККО-2014',
            'fkko_date' => 'Дата внесения в ФККО',
            'fo_id' => 'Код из Fresh Office',
            'fo_name' => 'Наименование из Fresh Office',
            'fo_fkko' => 'Код ФККО из Fresh Office',
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
                'preserveNonEmptyValues' => true,
            ],
        ];
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
    public function getDc()
    {
        return $this->hasOne(DangerClasses::class, ['id' => 'dc_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHk()
    {
        return $this->hasOne(HandlingKinds::class, ['id' => 'hk_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(Units::class, ['id' => 'unit_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkko()
    {
        return $this->hasOne(Fkko::class, ['id' => 'fkko_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentsTps()
    {
        return $this->hasMany(DocumentsTp::class, ['product_id' => 'id']);
    }
}
