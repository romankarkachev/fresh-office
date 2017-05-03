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
 * @property integer $ac_id
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
            [['responsible_id', 'ac_id'], 'required'],
            [['responsible_id', 'ac_id'], 'integer'],
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
            'ac_id' => 'Раздел учета', // 1 - утилизация, 2 - экология
            'acName' => 'Раздел учета',
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
     * @param $ac_id integer идентификатор раздела учета (может отсутствовать)
     * @return array
     */
    public static function arrayMapForSelect2($ac_id = null)
    {
        $query = ResponsibleFornewca::find()->orderBy('responsible_name');
        if ($ac_id != null) $query->where(['ac_id' => $ac_id]);
        $array = $query->all();

        return ArrayHelper::map($array, 'responsible_id', 'responsible_name');
    }

    /**
     * Делает выборку ответственных для новых контрагентов по учету утилизации и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfUtilizationSectionForSelect2()
    {
        return ArrayHelper::map(ResponsibleFornewca::find()
            ->where(['ac_id' => Appeals::РАЗДЕЛ_УЧЕТА_УТИЛИЗАЦИЯ])
            ->orderBy('responsible_name')
            ->all(), 'responsible_id', 'responsible_name');
    }

    /**
     * Делает выборку ответственных для новых контрагентов по учету экологии и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfEcologySectionForSelect2()
    {
        return ArrayHelper::map(ResponsibleFornewca::find()
            ->where(['ac_id' => Appeals::РАЗДЕЛ_УЧЕТА_ЭКОЛОГИЯ])
            ->orderBy('responsible_name')
            ->all(), 'responsible_id', 'responsible_name');
    }

    /**
     * Возвращает наименование раздела учета.
     * @return string
     */
    public function getAcName()
    {
        return Appeals::getIndepAccountSectionName($this->ac_id);
    }
}
