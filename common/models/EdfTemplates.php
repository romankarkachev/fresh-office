<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "edf_tmpls".
 *
 * @property integer $id
 * @property integer $type_id
 * @property integer $ct_id
 * @property string $name
 * @property string $ffp
 *
 * @property ContractTypes $ct
 * @property DocumentsTypes $type
 */
class EdfTemplates extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'edf_tmpls';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id', 'name', 'ffp'], 'required'],
            [['type_id', 'ct_id'], 'integer'],
            [['name', 'ffp'], 'string', 'max' => 255],
            [['ct_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContractTypes::className(), 'targetAttribute' => ['ct_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => DocumentsTypes::className(), 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'Тип документа',
            'ct_id' => 'Тип договора',
            'name' => 'Название документа',
            'ffp' => 'Полный путь к шаблону',
        ];
    }

    /**
     * Multi string position detection. Returns the first position of $check found in
     * $str or an associative array of all found positions if $getResults is enabled.
     *
     * Always returns boolean false if no matches are found.
     *
     * @param string $string The string to search
     * @param string|array $check String literal / array of strings to check
     * @param boolean $getResults Return associative array of positions?
     * @return boolean|int|array False if no matches, int|array otherwise
     * @source http://qaru.site/questions/257467/checking-for-multiple-strpos-values
     */
    public function multi_stripos($string, $check, $getResults = false)
    {
        $result = [];
        $check = (array) $check;

        foreach ($check as $s) {
            $pos = mb_stripos($string, $s);

            if ($pos !== false) {
                if ($getResults) {
                    $result[$s] = $pos;
                }
                else {
                    return $pos;
                }
            }
        }

        return empty($result) ? false : $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCt()
    {
        return $this->hasOne(ContractTypes::className(), ['id' => 'ct_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(DocumentsTypes::className(), ['id' => 'type_id']);
    }
}
