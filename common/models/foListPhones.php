<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_TELEPHONES".
 *
 * @property integer $ID_TELPHONE
 * @property integer $ID_COMPANY
 * @property string $TELEPHONE
 * @property string $FAX
 * @property integer $ID_CONTACT_MAN
 * @property string $ADD_TEL_NUMBER
 * @property integer $ISMAIN
 * @property string $COMMENT
 *
 * @property string $companyName
 * @property string $managerName
 *
 * @property foCompany $company
 * @property foManagers $manager
 */
class foListPhones extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'LIST_TELEPHONES';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_mssql');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID_COMPANY'], 'required'],
            [['ID_COMPANY', 'ID_CONTACT_MAN', 'ISMAIN'], 'integer'],
            [['TELEPHONE', 'FAX', 'ADD_TEL_NUMBER', 'COMMENT'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_TELPHONE' => 'ID',
            'ID_COMPANY' => 'Контрагент',
            'TELEPHONE' => 'Номер телефона',
            'FAX' => 'Fax',
            'ID_CONTACT_MAN' => 'Контактное лицо',
            'ADD_TEL_NUMBER' => 'Доп. номер телефона',
            'ISMAIN' => 'Основной телефон',
            'COMMENT' => 'Примечание',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCompany()
    {
        return $this->hasOne(foCompany::className(), ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(foManagers::className(), ['ID_MANAGER' => 'ID_MANAGER'])->via('company');
    }

    /**
     * Возвращает имя ответственного по контрагенту менеджера.
     * @return string
     */
    public function getManagerName()
    {
        return $this->manager != null ? $this->manager->MANAGER_NAME : '';
    }
}
