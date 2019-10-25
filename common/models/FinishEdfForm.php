<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Форма закрытия документооборота и помещения отмеченных пользователем файлов в хранилище.
 * @property Edf $edf
 * @property EdfFiles[] $edfFiles
 */
class FinishEdfForm extends Model
{
    /**
     * @var integer электронный документ
     */
    public $edf_id;

    /**
     * @var array массив файлов, которые возможно поместить в хранилище
     */
    public $files;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['edf_id'], 'required'],
            [['files'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'edf_id' => 'Документооборот',
            'files' => 'Файлы',
        ];
    }

    /**
     * @return Edf
     */
    public function getEdf()
    {
        return Edf::findOne($this->edf_id);
    }

    /**
     * Возвращает файлы, присоединенные к документообороту.
     * @return \yii\data\ActiveDataProvider
     */
    public function getEdfFiles()
    {
        $searchModel = new EdfFilesSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['ed_id' => $this->edf_id]]);
        $dataProvider->sort->defaultOrder = ['uploaded_at' => SORT_DESC];
        $dataProvider->pagination = false;
        return $dataProvider;
    }
}
