<?php

namespace common\models;

use yii\base\Model;
use yii\httpclient\Client;

/**
 * Класс для работы с сервисами Яндекса.
*/
class YandexServices extends Model
{
    const API_URL = 'https://geocode-maps.yandex.ru/1.x/';

    /**
     * Отправка на распознавание длинных аудио
     */
    const URL_SPEECHKIT_RECOGNITION_RUN_LONG = 'https://transcribe.api.cloud.yandex.net/speech/stt/v2/longRunningRecognize';

    /**
     * Результаты распознавания
     */
    const URL_SPEECHKIT_RECOGNITION_RESULTS = 'https://operation.api.cloud.yandex.net/operations/';

    /**
     * Выполняет GET-запрос для получения данных по API.
     * Пример ответа:
     *
"GeoObject": {
"metaDataProperty": {
"GeocoderMetaData": {
"kind": "house",
"text": "Россия, Ростов-на-Дону, микрорайон Западный, улица Зорге, 11\/2",
"precision": "exact",
"Address": {
"country_code": "RU",
"formatted": "Ростов-на-Дону, микрорайон Западный, улица Зорге, 11\/2",
"Components": [{
"kind": "country",
"name": "Россия"
}, {
    "kind": "province",
									"name": "Южный федеральный округ"
								}, {
    "kind": "province",
									"name": "Ростовская область"
								}, {
    "kind": "area",
									"name": "городской округ Ростов-на-Дону"
								}, {
    "kind": "locality",
									"name": "Ростов-на-Дону"
								}, {
    "kind": "district",
									"name": "микрорайон Западный"
								}, {
    "kind": "street",
									"name": "улица Зорге"
								}, {
    "kind": "house",
									"name": "11\/2"
								}]
},
"AddressDetails": {
    "Country": {
        "AddressLine": "Ростов-на-Дону, микрорайон Западный, улица Зорге, 11\/2",
									"CountryNameCode": "RU",
									"CountryName": "Россия",
									"AdministrativeArea": {
            "AdministrativeAreaName": "Ростовская область",
										"SubAdministrativeArea": {
                "SubAdministrativeAreaName": "городской округ Ростов-на-Дону",
											"Locality": {
                    "LocalityName": "Ростов-на-Дону",
												"DependentLocality": {
                        "DependentLocalityName": "микрорайон Западный",
													"Thoroughfare": {
                            "ThoroughfareName": "улица Зорге",
														"Premise": {
                                "PremiseNumber": "11\/2"
														}
													}
												}
											}
										}
									}
								}
							}
						}
					},
					"description": "микрорайон Западный, Ростов-на-Дону, Россия",
					"name": "улица Зорге, 11\/2",
					"boundedBy": {
    "Envelope": {
        "lowerCorner": "39.627643 47.216472",
							"upperCorner": "39.635853 47.222066"
						}
					},
					"Point": {
    "pos": "39.631748 47.219269"
					}
				}
     *
     * @param $query string
     * @return array|false
     */
    public static function getRequestToApi($query)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl(self::API_URL)
            ->setData([
                'format' => 'json',
                'geocode' => $query,
            ])
            ->send();

        if ($response->isOk) {
            $data = $response->getData();
            //var_dump($data);
            $featureMember = $data['response']['GeoObjectCollection']['featureMember'];
            //var_dump($featureMember);
            if (!empty($featureMember) && count($featureMember) == 1)
                // если адрес однозначно идентифицирован, то возвращаем массив с его данными
                return ($featureMember[0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']);
        }

        return false;
    }

    /**
     * Возвращает наименование региона по адресу, переданному в параметрах.
     * @param $address
     * @return string|false
     */
    public static function fetchRegionNameFromCustomAddress($address)
    {
        $data = self::getRequestToApi($address);
        if (!empty($data['Components'])) {
            $key = array_search('province', array_column($data['Components'], 'kind'));
            if ($key !== false) {
                if (false !== mb_stripos($data['Components'][$key]['name'], 'округ')) {
                    unset($data['Components'][$key]);
                    unset($key);
                    $data['Components'] = array_values($data['Components']);
                    $key = array_search('province', array_column($data['Components'], 'kind'));
                    if ($key !== false) return $data['Components'][$key]['name'];
                }
                else return $data['Components'][$key]['name'];
            }
            else print '<p>Округ не найден.</p>';
        }

        return false;
    }
}
