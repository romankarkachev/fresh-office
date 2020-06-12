<?php

namespace common\models;

use Yii;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "tf_vk".
 *
 * @property int $id
 * @property int $variety_id Разновидность
 * @property int $kind_id Форма
 *
 * @property string $varietyName
 * @property string $kindName
 *
 * @property TenderFormsKinds $kind
 * @property TenderFormsVarieties $variety
 */
class TenderFormsVarietiesKinds extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tf_vk';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['variety_id', 'kind_id'], 'integer'],
            [['variety_id', 'kind_id'], 'unique', 'targetAttribute' => ['variety_id', 'kind_id'], 'message' => 'Выбранная форма уже включена в набор.'],
            [['kind_id'], 'exist', 'skipOnError' => true, 'targetClass' => TenderFormsKinds::class, 'targetAttribute' => ['kind_id' => 'id']],
            [['variety_id'], 'exist', 'skipOnError' => true, 'targetClass' => TenderFormsVarieties::class, 'targetAttribute' => ['variety_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'variety_id' => 'Разновидность',
            'kind_id' => 'Форма',
            // вычисляемые поля
            'varietyName' => 'Разновидность',
            'kindName' => 'Форма',
        ];
    }

    /**
     * Возвращает путь к папке, в которую необходимо поместить загружаемые пользователем файлы.
     * Если папка не существует, она будет создана. Если создание провалится, будет возвращено false.
     * @return mixed
     * @throws \yii\base\Exception
     */
    public static function getUploadsFilepath($variety_id = null)
    {
        $filepath = Yii::getAlias('@uploads-export-templates-fs') . 'tenders-forms';
        if (!empty($variety_id)) {
            $filepath .= '/' . $variety_id;
        }

        if (!is_dir($filepath)) {
            if (!FileHelper::createDirectory($filepath, 0775, true)) return false;
        }

        return realpath($filepath);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariety()
    {
        return $this->hasOne(TenderFormsVarieties::class, ['id' => 'variety_id']);
    }

    /**
     * Возвращает наименование разновидности набора форм.
     * @return string
     */
    public function getVarietyName()
    {
        return $this->variety ? $this->variety->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKind()
    {
        return $this->hasOne(TenderFormsKinds::class, ['id' => 'kind_id']);
    }

    /**
     * Возвращает наименование формы.
     * @return string
     */
    public function getKindName()
    {
        return $this->kind ? $this->kind->name : '';
    }
}
