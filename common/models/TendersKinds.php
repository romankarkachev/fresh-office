<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "tenders_kinds".
 *
 * @property int $id
 * @property string $name Наименование
 * @property string $keywords Ключевые слова для идентификации
 *
 * @property Tenders[] $tenders
 */
class TendersKinds extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders_kinds';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'keywords'], 'string', 'max' => 255],
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
            'keywords' => 'Ключевые слова для идентификации',
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getTenders()->count() > 0) return true;

        return false;
    }

    /**
     * Рендерит необходимые кнопки для управления формой.
     * @return mixed
     */
    public function renderSubmitButtons()
    {
        $siaButtons = [
            'create' => Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']),
            'save' => Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']),
        ];

        if ($this->isNewRecord) {
            return $siaButtons['create'];
        }
        else {
            $result = $siaButtons['save'];
        }

        return $result;
    }

    /**
     * Делает выборку разновидностей конкурсов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTenders()
    {
        return $this->hasMany(Tenders::className(), ['tk_id' => 'id']);
    }
}
