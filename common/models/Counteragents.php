<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\httpclient\Client;

/**
 * This is the model class for table "companies".
 *
 * @property integer $id
 * @property string $name_full
 * @property string $inn
 * @property string $kpp
 * @property string $ogrn
 * @property string $bank_an
 * @property string $bank_bik
 * @property string $bank_name
 * @property string $bank_ca
 */
class Counteragents extends Model
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

    public $name;
    public $name_full;
    public $address_j;
    public $address_p;
    public $address_m;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['address_j', 'address_p', 'address_m'], 'string'],
            [['name_full'], 'string', 'max' => 200],
            [['inn'], 'string', 'max' => 12],
            [['kpp'], 'string', 'max' => 9],
            [['ogrn'], 'string', 'max' => 15],
            [['bank_an', 'bank_ca'], 'string', 'max' => 25],
            [['bank_bik'], 'string', 'max' => 10],
            [['bank_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name_full' => 'Полное наименование',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН(ИП)',
            'bank_an' => 'Номер р/с',
            'bank_bik' => 'БИК банка',
            'bank_name' => 'Наименование банка',
            'bank_ca' => 'Корр. счет',
            'address_j' => 'Адрес юридический',
            'address_p' => 'Адрес фактический',
            'address_m' => 'Адрес почтовый',
        ];
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
     * @param $model \common\models\Counteragents
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
     * @param $model \common\models\Counteragents
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
}
