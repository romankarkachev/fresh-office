<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "responsible_for_new_ca".
 *
 * @property integer $id
 * @property integer $responsible_id
 * @property string $responsible_name
 */
class ResponsibleFornewca extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'responsible_for_new_ca';
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

    /**
     * Делает выборку ответственных для новых контрагентов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(ResponsibleFornewca::find()->orderBy('responsible_name')->all(), 'responsible_id', 'responsible_name');
    }
}
