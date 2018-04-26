<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_EMAIL_CLIENT".
 *
 * @property integer $id_email
 * @property string $email
 * @property integer $ID_COMPANY
 * @property integer $ID_CONTACT_MAN
 * @property integer $EMAIL_SUBSCRIBE
 *
 * @property string $companyName
 * @property string $managerName
 *
 * @property foCompany $company
 * @property foManagers $manager
 */
class foListEmailClient extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'LIST_EMAIL_CLIENT';
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
            [['email'], 'string'],
            [['ID_COMPANY'], 'required'],
            [['ID_COMPANY', 'ID_CONTACT_MAN', 'EMAIL_SUBSCRIBE'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_email' => 'Id Email',
            'email' => 'Email',
            'ID_COMPANY' => 'Id  Company',
            'ID_CONTACT_MAN' => 'Id  Contact  Man',
            'EMAIL_SUBSCRIBE' => 'Email  Subscribe',
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
