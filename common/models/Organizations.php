<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "organizations".
 *
 * @property int $id
 * @property string $name Наименование
 * @property string $name_short Сокращенное наименование
 * @property string $name_full Полное наименование
 * @property string $inn ИНН
 * @property string $kpp КПП
 * @property string $ogrn ОГРН(ИП)
 * @property string $address_j Адрес юридический
 * @property string $address_f Адрес фактический
 * @property string $address_ttn Адрес для ТТН
 * @property string $doc_num_tmpl Шаблон номера договора
 * @property string $im_num_tmpl Шаблон номера входящей корреспонденции
 * @property string $om_num_tmpl Шаблон номера исходящей корреспонденции
 * @property string $dir_post Должность директора для реквизитов
 * @property string $dir_name ФИО директора полностью
 * @property string $dir_name_short Сокращенные ФИО директора
 * @property string $dir_name_of ФИО директора в родительном падеже
 * @property string $phones Телефоны для реквизитов
 * @property string $email Email для реквизитов
 * @property string $license_req Реквизиты лицензии
 * @property int $fo_dt_id Тип документа из Fresh Office
 *
 * @property Documents[] $documents
 * @property EcoMc[] $ecoMcs
 * @property EcoProjects[] $ecoProjects
 * @property Edf[] $edfs
 * @property IncomingMail[] $incomingMail
 * @property LicensesFiles[] $licensesFiles
 * @property LicensesRequests[] $licensesRequests
 * @property OrganizationsBas[] $bankAccounts
 * @property Edf[] $edf
 * @property Tenders[] $tenders
 */
class Organizations extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'organizations';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'name_short', 'name_full'], 'required'],
            [['address_j', 'address_f', 'address_ttn'], 'string'],
            [['fo_dt_id'], 'integer'],
            [['name', 'name_short', 'name_full', 'dir_post', 'dir_name', 'dir_name_short', 'dir_name_of', 'phones', 'email'], 'string', 'max' => 255],
            [['inn'], 'string', 'max' => 12],
            [['kpp'], 'string', 'max' => 9],
            [['ogrn'], 'string', 'max' => 15],
            [['inn', 'kpp', 'ogrn'], 'default', 'value' => null],
            [['doc_num_tmpl', 'im_num_tmpl', 'om_num_tmpl'], 'string', 'max' => 30],
            [['license_req'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'name_short' => 'Сокращенное наименование',
            'name_full' => 'Полное наименование',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН(ИП)',
            'address_j' => 'Адрес юридический',
            'address_f' => 'Адрес фактический',
            'address_ttn' => 'Адрес для ТТН',
            'doc_num_tmpl' => 'Шаблон номера договора',
            'im_num_tmpl' => 'Шаблон номера входящей корреспонденции',
            'om_num_tmpl' => 'Шаблон номера исходящей корреспонденции',
            'dir_post' => 'Должность директора',
            'dir_name' => 'ФИО директора',
            'dir_name_short' => 'Сокращенные ФИО директора',
            'dir_name_of' => 'ФИО директора в родительном падеже',
            'phones' => 'Телефоны',
            'email' => 'Email',
            'license_req' => 'Реквизиты лицензии',
            'fo_dt_id' => 'Тип документа из Fresh Office',
        ];
    }

    /**
     * Удаление связанных объектов перед удалением текущего.
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // deleteAll не вызывает beforeDelete, поэтому делаем перебор
                $nestedRecords = OrganizationsBas::find()->where(['org_id' => $this->id])->all();
                foreach ($nestedRecords as $record) $record->delete();

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            return true;
        }

        return false;
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getLicensesFiles()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку организаций и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param $exclude int|array|string идентификаторы организаций, которые необходимо исключить из выборки
     * @return array
     */
    public static function arrayMapForSelect2($exclude = null)
    {
        $query = self::find();
        if (!empty($exclude)) {
            $query->where(['not in', 'id', $exclude]);
        }

        return ArrayHelper::map($query->all(), 'id', 'name');
    }

    /**
     * Делает выборку счетов организации и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public function arrayMapOfBankAccountsForSelect2()
    {
        return ArrayHelper::map(OrganizationsBas::find()->select([
            'id',
            'rep' => 'CONCAT(bank_an, " в ", bank_name)',
        ])->where(['org_id' => $this->id])->asArray()->all(), 'id', 'rep');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicensesFiles()
    {
        return $this->hasMany(LicensesFiles::className(), ['organization_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicensesRequests()
    {
        return $this->hasMany(LicensesRequests::className(), ['org_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBankAccounts()
    {
        return $this->hasMany(OrganizationsBas::className(), ['org_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEdfs()
    {
        return $this->hasMany(Edf::class, ['org_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIncomingMail()
    {
        return $this->hasMany(IncomingMail::className(), ['org_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(Documents::className(), ['org_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoMcs()
    {
        return $this->hasMany(EcoMc::className(), ['org_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoProjects()
    {
        return $this->hasMany(EcoProjects::className(), ['org_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTenders()
    {
        return $this->hasMany(Tenders::className(), ['org_id' => 'id']);
    }
}
