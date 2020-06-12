<?php

namespace backend\controllers;

use common\models\CorrespondencePackages;
use common\models\PaymentOrders;
use common\models\ProjectsStates;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;
use yii\httpclient\Client;
use common\models\PostDeliveryKinds;
use common\models\DadataAPI;

/**
 * Контроллер для ослеживания отправлений Почты России и компании Major Express.
 */
class TrackingController extends Controller
{
    /**
     * Реквизиты доступа к API Почты России
     */
    const POCHTA_RU_LOGIN = 'TcANqqrYvLNUHm';
    const POCHTA_RU_PASS = 'soTji7KkobWU';
    const POCHTA_RU_API_URL = 'https://tracking.russianpost.ru/rtm34?wsdl';

    /**
     * Реквизиты доступа к API отправлений Почты России
     */
    const POCHTA_RU_SEND_API_TOKEN = 'Ndf4hnjFBH3SlfeFdG0pr2Q0BzQJM86N';
    const POCHTA_RU_SEND_API_AUTHKEY = 'ODgwMDU1NTIxODdAc3Q3Ny5ydTpRd2VydHk5ODc0NTY=';
    const POCHTA_RU_SEND_API_URL_НОРМАЛИЗАЦИЯ_АДРЕСА = 'https://otpravka-api.pochta.ru/1.0/clean/address';
    const POCHTA_RU_SEND_API_URL_СОЗДАНИЕ_ЗАКАЗА = 'https://otpravka-api.pochta.ru/1.0/user/backlog';
    const POCHTA_RU_SEND_API_URL_ПОИСК_ЗАКАЗА = 'https://otpravka-api.pochta.ru/1.0/backlog';
    const POCHTA_RU_URL_ORDER_PRINT = 'https://otpravka.pochta.ru/document/downloadBacklogForms/';

    /**
     * Ответ сервера Почты России должен содержать по одному из каждого массива вариантов
     */
    const POCHTA_RU_SEND_API_RESPONSE_QUALITY_CODES = [
        'GOOD',
        'POSTAL_BOX',
        'ON_DEMAND',
        'UNDEF_05'
    ];
    const POCHTA_RU_SEND_API_RESPONSE_VALIDATION_CODES = [
        'VALIDATED',
        'OVERRIDDEN',
        'CONFIRMED_MANUALLY'
    ];

    /**
     * Реквизиты доступа к API Major express
     */
    const MAJOR_EXPRESS_LOGIN = '592477';
    const MAJOR_EXPRESS_PASS = '892177';
    const MAJOR_EXPRESS_API_URL = 'https://ltl-ws.major-express.ru/edclients/edclients.asmx?WSDL';

    /**
     * Выполняет проверку вручения или отслеживание отправления. Если передается параметр $full, то будет возвращен список
     * движений по отправлению. Если не передается, то будет выполнена проверка вручения и результат типа timestamp.
     * @param $track_num string трек-номер отправления
     * @param $full bool при наличии значения в переменной будет возвращен полный спиок движений по отправлению
     * @param $object CorrespondencePackages|PaymentOrders
     * @return mixed
     * @throws \SoapFault
     * @throws \yii\base\InvalidConfigException
     */
    public static function trackPochtaRu($track_num, $full = null, $object = null)
    {
        $client2 = new \SoapClient(self::POCHTA_RU_API_URL, ['trace' => 1, 'soap_version' => SOAP_1_2]);

        $params3 = [
            'OperationHistoryRequest' => [
                'Barcode' => $track_num, 'MessageType' => '0', 'Language' => 'RUS'
            ],
            'AuthorizationHeader' => [
                'login' => self::POCHTA_RU_LOGIN,
                'password' => self::POCHTA_RU_PASS
            ]
        ];

        $history = null;
        try {
            $result = $client2->getOperationHistory(new \SoapParam($params3,'OperationHistoryRequest'));
            $history = $result->OperationHistoryData->historyRecord;
        }
        catch (\Exception $exception) {
            return false;
        }

        if (!empty($full)) {
            $tracking = '';
            if (is_array($history)) foreach ($history as $record) {
                $tracking .= sprintf("<p><strong>%s</strong></br>  %s, %s: %s</p>",
                    Yii::$app->formatter->asDate($record->OperationParameters->OperDate, 'php:d.m.Y в H:i'),
                    $record->AddressParameters->OperationAddress->Description,
                    $record->OperationParameters->OperType->Name,
                    $record->OperationParameters->OperAttr->Name
                );
            }

            return $tracking;
        }
        else
            if (is_array($history)) foreach ($history as $record) {
                // коды операций: https://tracking.pochta.ru/support/dictionaries/operation_codes
                if (!empty($object)) {
                    if ($record->OperationParameters->OperType->Id == 2) {
                        // 2 - операция Вручение на Почте России
                        return strtotime($record->OperationParameters->OperDate);
                    }

                    if (
                        // 8 - операция Обработка, 2 - Прибыло в место вручения
                        $record->OperationParameters->OperType->Id == 8 &&
                        $record->OperationParameters->OperAttr->Id == 2
                    ) {
                        if (
                            $object instanceof CorrespondencePackages &&
                            $object->hasAttribute('delivery_notified_at') &&
                            empty($object->delivery_notified_at)
                        ) {
                            // это пакет корреспонденции
                            // отправим уведомление контактному лицу о том, что посылка прибыла в почтовое отделение
                            if ($object->sendClientNotification(CorrespondencePackages::NF_ARRIVED)) {
                                // и пометим, что отправили такое уведомление (чтобы это было один раз в жизни)
                                $object->updateAttributes([
                                    'delivery_notified_at' => strtotime($record->OperationParameters->OperDate),
                                ]);
                            }
                        }

                        if ($object instanceof PaymentOrders) {
                            // это платежный ордер
                            $object->updateAttributes([
                                'imt_state' => PaymentOrders::INCOMING_MAIL_STATE_В_ОТДЕЛЕНИИ,
                            ]);
                        }
                    }

                    if (
                        // 4 - Досылка почты, 6 - Передача в невостребованные
                        ($record->OperationParameters->OperType->Id == 4 && $record->OperationParameters->OperAttr->Id == 6) ||
                        // 7 - Временное хранение, 2 - Невостребовано
                        ($record->OperationParameters->OperType->Id == 7 && $record->OperationParameters->OperAttr->Id == 2)
                    ) {
                        // за отправлением никто не пришел
                        if ($object instanceof CorrespondencePackages) {
                            // помечаем его как невостребованное, чтобы исключить из подсчета сроков исполнения в аналитике
                            $object->updateAttributes([
                                'state_id' => ProjectsStates::STATE_НЕВОСТРЕБОВАНО,
                            ]);
                        }
                        elseif ($object instanceof PaymentOrders) {
                            $object->updateAttributes([
                                'imt_state' => PaymentOrders::INCOMING_MAIL_STATE_НЕВОСТРЕБОВАНО,
                            ]);
                        }
                    }
                }
            }

        return false;
    }

    /**
     * Выполняет проверку вручения или отслеживание отправления. Если передается параметр $full, то будет возвращен список
     * движений по отправлению. Если не передается, то будет выполнена проверка вручения и результат типа bool.
     * @param $track_num string трек-номер отправления
     * @param null $full при наличии значения в переменной будет возвращен полный спиок движений по отправлению
     * @return bool|string
     */
    public static function trackMajorExpress($track_num, $full = null)
    {
        $soapClientOptions = [
            'login' => self::MAJOR_EXPRESS_LOGIN,
            'password' => self::MAJOR_EXPRESS_PASS,
        ];

        try {
            $client2 = new \SoapClient(self::MAJOR_EXPRESS_API_URL, $soapClientOptions);
            $result = $client2->History(['WBNumber' => $track_num]);
        }
        catch (\Exception $exception) {
            print "Ошибка работы с SOAP:<br>" . $exception->getMessage()."<br>" . $exception->getTraceAsString();
            return false;
        }

        if (isset($result->HistoryResult->EDWBHistory))
            if ($full != null) {
                // маршрут движения отправления
                $tracking = '';
                foreach ($result->HistoryResult->EDWBHistory as $record) {
                    $tracking .= sprintf("<p><strong>%s</strong></br>%s</p>",
                        Yii::$app->formatter->asDate($record->EventDateTime, 'php:d.m.Y в H:i'),
                        $record->Event
                    );
                }

                return $tracking;
            }
            else {
                // только дата и время доставки
                foreach ($result->HistoryResult->EDWBHistory as $record) {
                    if ($record->EventNum == 24) { // 24 - Груз доставлен получателю
                        return strtotime($record->EventDateTime);
                    }
                };
            }

        return false;
    }

    /**
     * Выполняет нормализацию адреса, переданного в параметрах, при помощи API Почты России.
     * @param $address string
     * @return array|bool
     */
    public static function pochtaRuNormalizeAddress($address)
    {
        $addresses = json_encode([
            [
                'id' => '1',
                'original-address' => $address,
            ]
        ]);

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl(self::POCHTA_RU_SEND_API_URL_НОРМАЛИЗАЦИЯ_АДРЕСА)
            ->setContent($addresses)
            ->setHeaders([
                'Authorization' => 'AccessToken '. self::POCHTA_RU_SEND_API_TOKEN,
                'X-User-Authorization' => 'Basic ' . self::POCHTA_RU_SEND_API_AUTHKEY,
                'Content-Type' => 'application/json;charset=UTF-8',
            ])->send();

        if ($response->isOk) {
            // извлекаем результат, берем первый элемент массива
            $data = $response->getData();
            if (count($data) > 0) {
                $data = $data[0];

                // проверим успех выполнения нормализации
                if (isset($data['quality-code']))
                    if (!in_array($data['quality-code'], self::POCHTA_RU_SEND_API_RESPONSE_QUALITY_CODES))
                        return false;

                // вторая проверка успешного выполнения
                if (isset($data['validation-code']))
                    if (!in_array($data['validation-code'], self::POCHTA_RU_SEND_API_RESPONSE_VALIDATION_CODES))
                        return false;

                return $data;
            }
        }

        return false;
    }

    /**
     * Создает отправление через API Почты России ("заказ в их терминологии). Возвращает id созданного заказа.
     * @param $postIndex integer почтовый индекс
     * @param $address string адрес, куда отправляются документы
     * @return integer|bool
     */
    public static function pochtaRuCreateOrder($postIndex, $address, $contact_person)
    {
        $data = self::pochtaRuNormalizeAddress($address);
        if ($data !== false) {
            $addresses = json_encode([
                [
                    /**
                     * CASHLESS	Безналичный расчет
                     * STAMP	Оплата марками
                     * FRANKING	Франкирование
                     */
                    'payment-method' => 'STAMP',
                    /**
                     * DEFAULT    Стандартный (улица, дом, квартира)
                     * PO_BOX    Абонентский ящик
                     * DEMAND    До востребования
                     */
                    'address-type-to' => 'DEFAULT',
                    /**
                     * SIMPLE    Простое
                     * ORDERED    Заказное
                     * ORDINARY    Обыкновенное
                     * WITH_DECLARED_VALUE    С объявленной ценностью
                     * WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY    С объявленной ценностью и наложенным платежом
                     * COMBINED    Комбинированное
                     */
                    'mail-category' => 'ORDERED',
                    'mail-direct' => 643,
                    /**
                     * POSTAL_PARCEL    Посылка "нестандартная"
                     * ONLINE_PARCEL    Посылка "онлайн"
                     * ONLINE_COURIER    Курьер "онлайн"
                     * EMS    Отправление EMS
                     * EMS_OPTIMAL    EMS оптимальное
                     * LETTER    Письмо
                     * BANDEROL    Бандероль
                     * BUSINESS_COURIER    Бизнес курьер
                     * BUSINESS_COURIER_ES    Бизнес курьер экпресс
                     * PARCEL_CLASS_1    Посылка 1-го класса
                     * COMBINED    Комбинированное
                     */
                    'mail-type' => 'LETTER',
                    'mass' => 2,
                    'index-to' => $postIndex,
                    'place-to' => $data['place'],
                    'region-to' => $data['region'],
                    'street-to' => $data['street'],
                    'house-to' => $data['house'],
                    'building-to' => $data['building'],
                    'recipient-name' => $contact_person,
                ],
            ]);

            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('put')
                ->setUrl(self::POCHTA_RU_SEND_API_URL_СОЗДАНИЕ_ЗАКАЗА)
                ->setContent($addresses)
                ->setHeaders([
                    'Authorization' => 'AccessToken ' . self::POCHTA_RU_SEND_API_TOKEN,
                    'X-User-Authorization' => 'Basic ' . self::POCHTA_RU_SEND_API_AUTHKEY,
                    'Content-Type' => 'application/json;charset=UTF-8',
                ])->send();

            if ($response->isOk) {
                $data = $response->getData();
                if (isset($data['result-ids']) && count($data['result-ids']) == 1) {
                    return $data['result-ids'][0];
                }
            }
        }

        return false;
    }

    /**
     * Извлекает трек-номер из заказа, переданного в параметрах.
     * @param $order_id integer идентификатор заказа в системе Почты России
     * @return string|bool
     */
    public static function pochtaRuExtractTrackNumberFromOrder($order_id)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl(self::POCHTA_RU_SEND_API_URL_ПОИСК_ЗАКАЗА . '/' . $order_id)
            ->setHeaders([
                'Authorization' => 'AccessToken '. self::POCHTA_RU_SEND_API_TOKEN,
                'X-User-Authorization' => 'Basic ' . self::POCHTA_RU_SEND_API_AUTHKEY,
                'Content-Type' => 'application/json;charset=UTF-8',
            ])->send();

        if ($response->isOk) {
            $data = $response->getData();
            if (isset($data['barcode'])) return $data['barcode'];
        }
        else {
            $content = $response->getContent();
            // если заказ не существует:
            if (is_array(Json::decode($content)) && isset($content['code']) && $content['code'] == 1001) return false;
        }

        return false;
    }

    /**
     * Раскладывает ответ Почты России в виде массива в строку.
     * @param $data array входной массив
     * @return string
     */
    public static function implodePochtaRuAnswerToString($data)
    {
        $result = '';
        if (isset($data['region'])) $result = $data['region'] . ', ';
        if (isset($data['place']) && $result != '' && $data['region'] != $data['place']) $result = trim($result) . ' ' . $data['place'] . ', ';
        if (isset($data['street'])) $result = trim($result) . ' ' . $data['street'] . ', ';
        if (isset($data['house'])) $result = trim($result) . ' д.' . $data['house'] . ', ';
        if (isset($data['building'])) $result = trim($result) . ' стр.' . $data['building'];
        $result = trim($result, ', ');

        if ($result == '') $result = $data['original-address'];
        return $result;
    }

    /**
     * Раскладывает ответ сервиса Dadata в виде массива в строку.
     * @param $data array входной массив
     * @return string
     */
    public static function implodeDadataAnswerToString($data)
    {
        $result = '';
        if (isset($data['region_with_type'])) $result = $data['region_with_type'] . ', ';
        if (isset($data['city_with_type']) && $result != '' && $data['region_with_type'] != $data['city_with_type']) $result = trim($result) . ' ' . $data['city_with_type'] . ', ';
        if (isset($data['street'])) $result = trim($result) . ' ' . $data['street_type_full'] . ' ' . $data['street'] . ', ';
        if (isset($data['house'])) $result = trim($result) . ' д.' . $data['house'] . ', ';
        if (isset($data['building'])) $result = trim($result) . ' стр.' . $data['building'];
        $result = trim($result, ', ');

        if ($result == '') $result = $data['original-address'];
        return $result;
    }

    /**
     * @param $pd_id integer способ доставки
     * @param $track_num string трек-номер отправления
     * @return bool|string
     */
    public function actionTrackByNumber($pd_id, $track_num)
    {
        $pd_id = intval($pd_id);
        if ($pd_id > 0) {
            switch ($pd_id) {
                case PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ:
                    $result = self::trackPochtaRu($track_num, true);
                    break;
                case PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS:
                    $result = self::trackMajorExpress($track_num, true);
                    break;

            }

            if (isset($result))
                return $result;
            else
                return 'Невозможно загрузить результаты.';
        }

        return false;
    }

    /**
     * Выполняет нормализацию адреса, переданного в параметрах через Почту России. Возвращает в частности индекс отделения.
     * @param $address string адрес, который необходимо нормализовать
     * @return array|bool
     */
    public function actionNormalizeAddress($address)
    {
        $data = DadataAPI::cleanAddress($address);
        if ($data !== false) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'index' => $data['postal_code'],
                'address' => self::implodeDadataAnswerToString($data),
            ];
        }

        // Почта России (отключено из-за низкого качества оказания услуги)
        /*
        $data = self::pochtaRuNormalizeAddress($address);
        if ($data !== false) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'index' => $data['index'],
                //'address' => $data['original-address'],
                'address' => self::implodePochtaRuAnswerToString($data),
            ];
        }
        */

        return false;
    }
}
