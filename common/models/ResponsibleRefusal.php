<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "responsible_refusal".
 *
 * @property integer $id
 * @property integer $responsible_id
 * @property string $responsible_name
 */
class ResponsibleRefusal extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'responsible_refusal';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['responsible_id'], 'required'],
            [['responsible_id'], 'integer'],
            [['responsible_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'responsible_id' => 'Ответственный',
            'responsible_name' => 'Ответственный',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // выборка менеджеров из SQL-базы данных
            $managers = DirectMSSQLQueries::fetchManagers();

            // заполним наименование ответственного
            $key = array_search($this->responsible_id, array_column($managers, 'id'));
            if (false !== $key) {
                $this->responsible_name = $managers[$key]['name'];
            }
            return true;
        }
        return false;
    }
}
