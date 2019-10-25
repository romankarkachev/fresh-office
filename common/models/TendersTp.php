<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tenders_tp".
 *
 * @property int $id
 * @property int $tender_id Тендер
 * @property int $fkko_id Код ФККО
 * @property string $fkko_name ФККО
 *
 * @property Fkko $fkko
 * @property Tenders $tender
 */
class TendersTp extends \yii\db\ActiveRecord
{
    /**
     * Идентификаторы элементов страницы
     */
    const DOM_IDS = [
        // блок с отходами
        'BLOCK_ID' => 'block-tpWaste',
        // блоки со значениями для нового отхода тендера (всегда со счетчиком)
        'ROW_ID' => 'waste-row',
        // значок загрузки, появляющийся при добавлении нового отхода
        'PRELOADER' => 'waste-preloader',
        // кнопка "Добавить"
        'ADD_BUTTON' => 'btnNewWaste',
        // кнопки удаления отхода при создании нового договора
        'DELETE_BUTTON' => 'btnDeleteNewWaste',
        // форма для интерактивного добавления отхода
        'PJAX_FORM_ID' => 'frmNewWaste',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders_tp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tender_id', 'fkko_id'], 'integer'],
            [['fkko_name'], 'string', 'max' => 255],
            [['fkko_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fkko::className(), 'targetAttribute' => ['fkko_id' => 'id']],
            [['tender_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tenders::className(), 'targetAttribute' => ['tender_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tender_id' => 'Тендер',
            'fkko_id' => 'Код ФККО',
            'fkko_name' => 'ФККО',
        ];
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
    public function getTender()
    {
        return $this->hasOne(Tenders::className(), ['id' => 'tender_id']);
    }
}
