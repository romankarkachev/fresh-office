<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\httpclient\Client;

/**
 * This is the model class for table "companies".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property string $opfh
 * @property string $brand_name
 * @property string $name
 * @property string $name_full
 * @property string $inn
 * @property string $kpp
 * @property string $ogrn
 * @property string $bank_an
 * @property string $bank_bik
 * @property string $bank_name
 * @property string $bank_ca
 * @property string $email
 * @property string $address_j
 * @property string $address_p
 * @property string $address_m
 * @property string $address_dostavista
 * @property string $phones
 * @property string $okpo
 * @property string $okato
 * @property string $oktmo
 * @property string $okved
 * @property string $director
 * @property string $doc_reason
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property CompaniesContacts[] $companiesContacts
 * @property Contracts[] $contracts
 */
class Companies extends \yii\db\ActiveRecord
{
    /**
     * Типы субъектов предпринимательской деятельности для целей поиска по Единому реестру через механизм API.
     */
    const API_CA_TYPE_ЮРЛИЦО = 1;
    const API_CA_TYPE_ФИЗЛИЦО = 2;

    /**
     * Типы полей для поиска по Единому реестру через механизм API.
     */
    const API_FIELD_ИНН = 1;
    const API_FIELD_ОГРН = 2;
    const API_FIELD_НАИМЕНОВАНИЕ = 3;

    /**
     * Контактные лица компании
     * @var array
     */
    public $contacts;

    /**
     * Массив ошибок при заполнении контактных лиц
     * @var array
     */
    public $contacts_errors;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'companies';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['opfh', 'name', 'name_full'], 'required'],
            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['address_j', 'address_p', 'address_m'], 'string'],
            [['opfh', 'brand_name', 'name'], 'string', 'max' => 100],
            [['name_full'], 'string', 'max' => 200],
            [['inn'], 'string', 'max' => 12],
            [['kpp'], 'string', 'max' => 9],
            [['ogrn'], 'string', 'max' => 15],
            [['bank_an', 'bank_ca'], 'string', 'max' => 25],
            [['bank_bik'], 'string', 'max' => 10],
            [['bank_name', 'email', 'address_dostavista'], 'string', 'max' => 255],
            [['phones', 'director', 'doc_reason'], 'string', 'max' => 50],
            [['okpo', 'okato', 'oktmo'], 'string', 'max' => 30],
            [['okved'], 'string', 'max' => 150],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            // собственные правила валидации
            ['contacts', 'validateContacts'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'created_by' => 'Автор создания',
            'updated_at' => 'Дата и время изменения',
            'updated_by' => 'Автор изменений',
            'opfh' => 'Организационно-правовая форма хозяйствования',
            'brand_name' => 'Бренд',
            'name' => 'Наименование',
            'name_full' => 'Полное наименование',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН(ИП)',
            'bank_an' => 'Номер р/с',
            'bank_bik' => 'БИК банка',
            'bank_name' => 'Наименование банка',
            'bank_ca' => 'Корр. счет',
            'email' => 'E-mail',
            'address_j' => 'Адрес юридический',
            'address_p' => 'Адрес фактический',
            'address_m' => 'Адрес почтовый',
            'address_dostavista' => 'Адрес Достависта',
            'phones' => 'Телефоны',
            'okpo' => 'ОКПО',
            'okato' => 'ОКАТО',
            'oktmo' => 'ОКТМО',
            'okved' => 'ОКВЭД',
            'director' => 'Руководитель',
            'doc_reason' => 'Основание',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_by'],
                ],
            ],
        ];
    }

    /**
     * Собственное правило валидации для контактных лиц.
     */
    public function validateContacts()
    {
        if (count($this->contacts) > 0) {
            $row_numbers = [];
            foreach ($this->contacts as $index => $item) {
                $oa = new CompaniesContacts();
                $oa->attributes  = $item;
                if (!$oa->validate(['name'])) {
                    $row_numbers[] = ($index+1);
                }
            }
            if (count($row_numbers) > 0) $this->addError('contacts_errors', 'Не все обязательные поля заполнены! Строки: '.implode(',', $row_numbers).'.');
        }
    }

    /**
     * Превращает данные из массива идентификаторов в массив моделей CompaniesContacts.
     * @return array
     */
    public function makeContactsModelsFromPostArray()
    {
        // исключаем те строки, которые уже используются в других объектах (договорах)
        $ids = [];
        if (is_array($this->contacts)) if (count($this->contacts) > 0) foreach ($this->contacts as $contact) $ids[] = intval($contact['id']);
        $exclude_ids = ContractsContacts::find()->select('contact_id')->where(['in', 'contact_id', $ids])->asArray()->column();
        $exists_tp = CompaniesContacts::find()->where(['company_id' => $this->id])->all();
        // все остальные строки удаляем из уже привязанных к компании
        foreach ($exists_tp as $contact) {
            /* @var $contact \common\models\CompaniesContacts */
            if ($contact->id != null)
                // если задан идентификатор контактного лица, значит оно уже существовало ранее
                // проверим, не используется ли оно в договорах
                if (!in_array($contact->id, $exclude_ids)) {
                    // если не используется, то удалим ее из компании совсем
                    // оно будет заменена на пришедшее снаружи
                    $contact->delete();
                }
                else {
                    // если оно используется, то удалим его из пришедших снаружи и удаление из компании не производим
                    // таким образом, с ним просто ничего не произойдет
                    $this->contacts = array_values($this->contacts); // магический ритуал сбрасывает индексы ключей после unset
                    if (false !== ($key = array_search($contact->id, array_column($this->contacts, 'id')))) unset($this->contacts[$key]);
                }
        }

        $result = [];
        if (is_array($this->contacts)) if (count($this->contacts) > 0) {
            foreach ($this->contacts as $item) {
                $dtp = new CompaniesContacts();
                $dtp->attributes = $item;
                $dtp->company_id = $this->id;
                $dtp->id = intval($item['id']);
                $result[] = $dtp;
            }
        }

        return $result;
    }

    /**
     * Делает запрос данных контрагента по API.
     * @param $type_id integer тип контрагента (1 - юрлицо, 2 - физлицо)
     * @param $field_id integer поле для поиска данных (1 - инн, 2 - огрн(ип), 3 - наименование)
     * @param $value string значение для поиска
     * @return array массив с данными контрагента
     */
    public static function apiFetchCounteragentsInfo($type_id, $field_id, $value)
    {
        $client = new Client();
        $query = $client->createRequest()->setMethod('get');

        // тип контрагента
        if ($type_id == self::API_CA_TYPE_ЮРЛИЦО) {
            // юридическое лицо
            $query->setUrl('https://xn--c1aubj.xn--80asehdb/интеграция/компании/');
            switch ($field_id) {
                case self::API_FIELD_ИНН:
                    $query->setData(['инн' => $value]);
                    break;
                case self::API_FIELD_ОГРН:
                    $query->setData(['огрн' => $value]);
                    break;
                case self::API_FIELD_НАИМЕНОВАНИЕ:
                    $query->setData(['наименование' => $value]);
                    break;
            }
        }
        else {
            // физическое лицо
            $query->setUrl('https://xn--c1aubj.xn--80asehdb/интеграция/ип/');
            switch ($field_id) {
                case self::API_FIELD_ИНН:
                    $query->setData(['инн' => $value]);
                    break;
                case self::API_FIELD_ОГРН:
                    $query->setData(['огрнип' => $value]);
                    break;
            }
        }

        $response = $query->send();

        if ($response->isOk) {
            $result = $response->data;
            //var_dump($result);
            if (count($result) > 0) {
                if (count($result) == 1) {
                    $details = $response->data[0];
                    if ($type_id == self::API_CA_TYPE_ЮРЛИЦО) {
                        // сразу второй запрос, потому что контрагент-юрлицо идентифицирован однозначно
                        $query->setUrl('https://xn--c1aubj.xn--80asehdb/интеграция/компании/' . $details['id'] . '/');
                        $response = $query->send();
                        if ($response->isOk) return [$response->data];
                    }
                }

                return $response->data;
            }
        }

        return [];
    }

    /**
     * Извлекает наименование, заключенное в кавычки и возвращает результат.
     * @param $name string
     * @return string
     */
    public static function api_extractNameInQuotes($name)
    {
        if (preg_match('~"([^"]*)"~u' , $name , $m)) return $m[1];
        return $name;
    }

    /**
     * Делает заглавными первые буквы во всех словах значения, переданного в параметрах.
     * @param $value string
     * @return string
     */
    public static function api_uppercaseFirstLetters($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'utf-8');
    }

    /**
     * Формирует адрес из параметров массива.
     * @param $address array
     * @return string
     */
    public static function api_composeFullAddress($address)
    {
        $postal_rep = '';
        if (isset($address['postalIndex'])) $postal_rep = $address['postalIndex'];

        $region_rep = '';
        if (isset($address['region']))
            if (intval($address['region']['type']['code']) == 103)
                $region_rep = $address['region']['type']['shortName'] . '. ' . $address['region']['name'];
            else
                $region_rep = $address['region']['fullName'];

        $area_rep = '';
        if (isset($address['area'])) $area_rep = $address['area']['fullName'];

        $place_rep = '';
        if (isset($address['place']))
            $place_rep = $address['place']['type']['shortName'] . '. ' . $address['place']['name'];

        $city_rep = '';
        if (isset($address['city']))
            $city_rep = $address['city']['type']['shortName'] . '. ' . $address['city']['name'];

        $street_rep = '';
        if (isset($address['street'])) $street_rep = $address['street']['typeShortName'] . '. ' . $address['street']['name'];

        $house_rep = '';
        if (isset($address['house'])) $house_rep = $address['house'];

        $building_rep = '';
        if (isset($address['building'])) $building_rep = $address['building'];

        $flat_rep = '';
        if (isset($address['flat'])) $flat_rep = $address['flat'];

        $result = $postal_rep . ' ' . $region_rep;
        $result = trim($result, ', ');

        $result .= ' ' . $area_rep;
        $result = trim($result, ', ');

        $result .= ' ' . $place_rep;
        $result = trim($result, ', ');

        $result .= ', ' . $city_rep;
        $result = trim($result, ', ');

        $result .= ($city_rep != '' ? ', ' : ' ') . $street_rep;
        $result = trim($result, ', ');

        $result .= ', ' . mb_strtolower($house_rep);
        $result = trim($result, ', ');

        $result .= ', ' . mb_strtolower($building_rep);
        $result = trim($result, ', ');

        $result .= ', ' . mb_strtolower($flat_rep);
        $result = trim($result, ', ');

        return $result;
    }

    /**
     * Выполняет заполнение реквизитов юридического лица.
     * @param $model \common\models\Companies
     * @param $details array
     */
    public static function api_fillModelJur($model, $details)
    {
        $model->name = self::api_uppercaseFirstLetters(self::api_extractNameInQuotes($details['shortName']));
        $model->name_full = $details['shortName'];
        $address = '';
        if (isset($details['address'])) $address = self::api_composeFullAddress($details['address']);
        $model->address_j = $address;
        $model->address_p = $model->address_j;
        $model->address_m = $model->address_j;
        $model->kpp = $details['kpp'];
    }

    /**
     * Выполняет заполнение реквизитов физического лица.
     * @param $model \common\models\Companies
     * @param $details array
     */
    public static function api_fillModelPhys($model, $details)
    {
        $addon = ''; if (isset($details['type'])) if ($details['type']['id'] == 1) $addon = 'ИП ';

        $model->name = self::api_uppercaseFirstLetters($details['person']['surName']) . ' ' .
            mb_substr($details['person']['firstName'], 0, 1) . '. ' .
            mb_substr($details['person']['middleName'], 0, 1) . '.';
        $model->name_full = $addon . self::api_uppercaseFirstLetters($details['person']['fullName']);
    }

    /**
     * Функция выполняет очистку массива от закрытых субъектов предпринимательской деятельности.
     * @param $details array
     * @return array
     */
    public static function api_cleanFromClosed($details)
    {
        $array = $details;
        foreach ($array as $index => $subject) {
            if (isset($subject['closeInfo'])) unset($array[$index]);
        }

        return $array;
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getContracts()->count() > 0) return true;

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * Возвращает имя автора-создателя в виде ivan (Иван).
     * @return string
     */
    public function getCreatedByName()
    {
        return $this->created_by == null ? '' : ($this->createdBy->profile == null ? $this->createdBy->username :
            $this->createdBy->username . ' (' . $this->createdBy->profile->name . ')');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * Возвращает имя пользователя, который вносил изменения в запись последним в виде ivan (Иван).
     * @return string
     */
    public function getUpdatedByName()
    {
        return $this->updated_by == null ? '' : ($this->updatedBy->profile == null ? $this->updatedBy->username :
            $this->updatedBy->username . ' (' . $this->createdBy->profile->name . ')');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompaniesContacts()
    {
        return $this->hasMany(CompaniesContacts::className(), ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContracts()
    {
        return $this->hasMany(Contracts::className(), ['company_id' => 'id']);
    }
}
