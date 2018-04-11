<?php

namespace common\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;

/**
 * Класс для работы с API dadata.ru.
 * Пример ответа:
array(1) {
    ["suggestions"]=>
array(1) {
        [0]=>
array(3) {
            ["value"]=>
string(23) "ООО "ПАРТНЁР""
            ["unrestricted_value"]=>
string(23) "ООО "ПАРТНЁР""
            ["data"]=>
array(27) {
                ["kpp"]=>
string(9) "622901001"
                ["capital"]=>
NULL
["management"]=>
array(2) {
                    ["name"]=>
string(46) "Петенин Антон Викторович"
                    ["post"]=>
string(39) "Генеральный директор"
}
["founders"]=>
        NULL
        ["managers"]=>
        NULL
        ["branch_type"]=>
        string(4) "MAIN"
                ["branch_count"]=>
        int(0)
        ["source"]=>
        NULL
        ["qc"]=>
        NULL
        ["hid"]=>
        string(64) "743ad3d41703b1f4c3e91e8e72382bd505bbb289fcef078b93e71f09f8cd0af6"
                ["type"]=>
        string(5) "LEGAL"
                ["state"]=>
        array(4) {
                    ["status"]=>
          string(6) "ACTIVE"
                    ["actuality_date"]=>
          int(1514764800000)
          ["registration_date"]=>
          int(1248825600000)
          ["liquidation_date"]=>
          NULL
        }
        ["opf"]=>
        array(4) {
                    ["type"]=>
          string(4) "2014"
                    ["code"]=>
          string(5) "12300"
                    ["full"]=>
          string(77) "Общество с ограниченной ответственностью"
                    ["short"]=>
          string(6) "ООО"
        }
        ["name"]=>
        array(5) {
                    ["full_with_opf"]=>
          string(94) "ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ПАРТНЁР""
                    ["short_with_opf"]=>
          string(23) "ООО "ПАРТНЁР""
                    ["latin"]=>
          NULL
          ["full"]=>
          string(14) "ПАРТНЁР"
                    ["short"]=>
          string(14) "ПАРТНЁР"
        }
        ["inn"]=>
        string(10) "6229067207"
                ["ogrn"]=>
        string(13) "1096229002440"
                ["okpo"]=>
        NULL
        ["okved"]=>
        string(5) "52.29"
                ["okveds"]=>
        NULL
        ["authorities"]=>
        NULL
        ["documents"]=>
        NULL
        ["licenses"]=>
        NULL
        ["address"]=>
        array(3) {
                    ["value"]=>
          string(56) "г Рязань, ул Молодцова, д 10, оф 95"
                    ["unrestricted_value"]=>
          string(83) "Рязанская обл, г Рязань, ул Молодцова, д 10, оф 95"
                    ["data"]=>
          array(77) {
                        ["postal_code"]=>
            string(6) "390042"
                        ["country"]=>
            string(12) "Россия"
                        ["region_fias_id"]=>
            string(36) "963073ee-4dfc-48bd-9a70-d2dfc6bd1f31"
                        ["region_kladr_id"]=>
            string(13) "6200000000000"
                        ["region_with_type"]=>
            string(25) "Рязанская обл"
                        ["region_type"]=>
            string(6) "обл"
                        ["region_type_full"]=>
            string(14) "область"
                        ["region"]=>
            string(18) "Рязанская"
                        ["area_fias_id"]=>
            NULL
            ["area_kladr_id"]=>
            NULL
            ["area_with_type"]=>
            NULL
            ["area_type"]=>
            NULL
            ["area_type_full"]=>
            NULL
            ["area"]=>
            NULL
            ["city_fias_id"]=>
            string(36) "86e5bae4-ef58-4031-b34f-5e9ff914cd55"
                        ["city_kladr_id"]=>
            string(13) "6200000100000"
                        ["city_with_type"]=>
            string(15) "г Рязань"
                        ["city_type"]=>
            string(2) "г"
                        ["city_type_full"]=>
            string(10) "город"
                        ["city"]=>
            string(12) "Рязань"
                        ["city_area"]=>
            NULL
            ["city_district_fias_id"]=>
            NULL
            ["city_district_kladr_id"]=>
            NULL
            ["city_district_with_type"]=>
            NULL
            ["city_district_type"]=>
            NULL
            ["city_district_type_full"]=>
            NULL
            ["city_district"]=>
            NULL
            ["settlement_fias_id"]=>
            NULL
            ["settlement_kladr_id"]=>
            NULL
            ["settlement_with_type"]=>
            NULL
            ["settlement_type"]=>
            NULL
            ["settlement_type_full"]=>
            NULL
            ["settlement"]=>
            NULL
            ["street_fias_id"]=>
            string(36) "69ccd156-098e-4205-8f4b-e189c5db98b4"
                        ["street_kladr_id"]=>
            string(17) "62000001000029200"
                        ["street_with_type"]=>
            string(23) "ул Молодцова"
                        ["street_type"]=>
            string(4) "ул"
                        ["street_type_full"]=>
            string(10) "улица"
                        ["street"]=>
            string(18) "Молодцова"
                        ["house_fias_id"]=>
            string(36) "8ba9c720-d0dc-4072-9815-db38727492e2"
                        ["house_kladr_id"]=>
            string(19) "6200000100002920001"
                        ["house_type"]=>
            string(2) "д"
                        ["house_type_full"]=>
            string(6) "дом"
                        ["house"]=>
            string(2) "10"
                        ["block_type"]=>
            NULL
            ["block_type_full"]=>
            NULL
            ["block"]=>
            NULL
            ["flat_type"]=>
            string(4) "оф"
                        ["flat_type_full"]=>
            string(8) "офис"
                        ["flat"]=>
            string(2) "95"
                        ["flat_area"]=>
            NULL
            ["square_meter_price"]=>
            NULL
            ["flat_price"]=>
            NULL
            ["postal_box"]=>
            NULL
            ["fias_id"]=>
            string(36) "8ba9c720-d0dc-4072-9815-db38727492e2"
                        ["fias_code"]=>
            string(23) "62000001000000002920001"
                        ["fias_level"]=>
            string(1) "8"
                        ["fias_actuality_state"]=>
            string(1) "0"
                        ["kladr_id"]=>
            string(19) "6200000100002920001"
                        ["capital_marker"]=>
            string(1) "2"
                        ["okato"]=>
            string(11) "61401000000"
                        ["oktmo"]=>
            string(11) "61701000001"
                        ["tax_office"]=>
            string(4) "6229"
                        ["tax_office_legal"]=>
            string(4) "6229"
                        ["timezone"]=>
            NULL
            ["geo_lat"]=>
            string(10) "54.6642521"
                        ["geo_lon"]=>
            string(10) "39.6488471"
                        ["beltway_hit"]=>
            NULL
            ["beltway_distance"]=>
            NULL
            ["metro"]=>
            NULL
            ["qc_geo"]=>
            string(1) "2"
                        ["qc_complete"]=>
            NULL
            ["qc_house"]=>
            NULL
            ["history_values"]=>
            NULL
            ["unparsed_parts"]=>
            NULL
            ["source"]=>
            string(105) "390042, ОБЛАСТЬ РЯЗАНСКАЯ, ГОРОД РЯЗАНЬ, УЛИЦА МОЛОДЦОВА, 10, 95"
                        ["qc"]=>
            NULL
          }
        }
        ["phones"]=>
        NULL
        ["emails"]=>
        NULL
        ["ogrn_date"]=>
        int(1248825600000)
        ["okved_type"]=>
        string(4) "2014"
      }
    }
  }
}
*/
class DadataAPI extends Model
{
    const API_TOKEN = '3523cabe059250cc360ce05b32b3795b0742ecd6';
    const API_URL = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party';

    /**
     * Выполняет POST-запрос для получения данных по API.
     * @param $query string
     * @return array|false
     */
    public static function postRequestToApi($query)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl(self::API_URL)
            ->setContent(Json::encode(['query' => $query]))
            ->setHeaders([
                'Authorization' => 'Token ' . self::API_TOKEN,
                'Content-Type' => 'application/json;charset=utf-8',
                'Accept' => 'application/json',
            ])->send();

        if ($response->isOk) {
            $data = $response->getData();
            if (isset($data['suggestions']) && count($data['suggestions']) > 0) {
                // очистим от уже закрытых предприятий
                $result = array_values(self::cleanFromLiquidated($data['suggestions']));

                // если контрагент однозначно идентифицирован, то возвращаем массив с его данными
                if (count($result) == 1) return $result[0]['data'];
            }
        }

        return false;
    }

    /**
     * Функция выполняет очистку массива от закрытых субъектов предпринимательской деятельности.
     * @param $details array
     * @return array
     */
    public static function cleanFromLiquidated($details)
    {
        $array = $details;
        foreach ($array as $index => $subject) {
            if (isset($subject['data']['state']['status']) && $subject['data']['state']['liquidation_date'] != null) unset($array[$index]);
        }

        return $array;
    }
}
