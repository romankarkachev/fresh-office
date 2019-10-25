<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "po_properties".
 *
 * @property int $id
 * @property string $name Наименование
 *
 * @property PoEip[] $poEips
 * @property PoValues[] $poValues
 */
class PoProperties extends \yii\db\ActiveRecord
{
    /**
     * @var array
     */
    public $values;

    /**
     * @var array
     */
    public $ei;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'po_properties';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 150],
            [['ei'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            // виртуальные поля
            'ei' => 'Статьи расходов',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением объекта

            $transaction = Yii::$app->db->beginTransaction();

            // удаляем возможные значения свойств
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $records = PoValues::find()->where(['property_id' => $this->id])->all();
            foreach ($records as $record) {
                if (empty($record->delete())) {
                    $transaction->rollBack();
                    return false;
                }
            }

            // удаляем привязки к статьям расходов
            PoEip::deleteAll(['property_id' => $this->id]);

            $transaction->commit();
            return true;
        }
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getPoEips()->count() > 0) return true;

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoEips()
    {
        return $this->hasMany(PoEip::className(), ['property_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoValues()
    {
        return $this->hasMany(PoValues::className(), ['property_id' => 'id']);
    }
}
