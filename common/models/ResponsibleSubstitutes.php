<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "responsible_substitutes".
 *
 * @property integer $id
 * @property integer $required_id
 * @property string $required_name
 * @property integer $substitute_id
 * @property string $substitute_name
 */
class ResponsibleSubstitutes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'responsible_substitutes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['required_id', 'substitute_id'], 'required'],
            [['required_id', 'substitute_id'], 'integer'],
            ['required_id', 'unique'],
            [['required_name', 'substitute_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'required_id' => 'Искомый ответственный',
            'required_name' => 'Искомый ответственный',
            'substitute_id' => 'Заменяющий ответственный',
            'substitute_name' => 'Заменяющий ответственный',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // выборка менеджеров из SQL-базы данных
            $managers = Appeals::fetchManagers();

            // заполним наименование искомого ответственного
            $key = array_search($this->required_id, array_column($managers, 'id'));
            if (false !== $key) {
                $this->required_name = $managers[$key]['name'];
            }

            // заполним наименование заменяющего ответственного
            $key = array_search($this->substitute_id, array_column($managers, 'id'));
            if (false !== $key) {
                $this->substitute_name = $managers[$key]['name'];
            }
            return true;
        }
        return false;
    }
}
