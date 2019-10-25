<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "fkko".
 *
 * @property int $id
 * @property string $fkko_code Код по ФККО-2017
 * @property string $fkko_name Наименование
 *
 * @property EdfTp[] $edfTps
 * @property LicensesFkkoPages[] $licensesFkkoPages
 * @property LicensesRequestsFkko[] $licensesRequestsFkkos
 * @property TransportRequestsWaste[] $transportRequestsWastes
 */
class Fkko extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fkko';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fkko_code', 'fkko_name'], 'required'],
            [['fkko_name'], 'string'],
            [['fkko_code'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fkko_code' => 'Код по ФККО-2017',
            'fkko_name' => 'Наименование',
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getEdfTps()->count() > 0 || $this->getLicensesFkkoPages()->count() > 0 ||
            $this->getLicensesRequestsFkkos()->count() > 0 || $this->getTransportRequestsWastes()->count() > 0) return true;

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEdfTps()
    {
        return $this->hasMany(EdfTp::className(), ['fkko_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicensesFkkoPages()
    {
        return $this->hasMany(LicensesFkkoPages::className(), ['fkko_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicensesRequestsFkkos()
    {
        return $this->hasMany(LicensesRequestsFkko::className(), ['fkko_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportRequestsWastes()
    {
        return $this->hasMany(TransportRequestsWaste::className(), ['fkko_id' => 'id']);
    }
}
