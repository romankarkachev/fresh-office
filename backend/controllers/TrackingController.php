<?php

namespace backend\controllers;

use common\models\PostDeliveryKinds;
use Yii;
use yii\web\Controller;

/**
 * Контроллер для ослеживания отправлений Почты России и компании Major Express.
 */
class TrackingController extends Controller
{
    /**
     * Реквизиты доступа к API Почты России.
     */
    const POCHTA_RU_LOGIN = 'TcANqqrYvLNUHm';
    const POCHTA_RU_PASS = '3mlhLBQbogkv';
    const POCHTA_RU_API_URL = 'https://tracking.russianpost.ru/rtm34?wsdl';

    /**
     * Реквизиты доступа к API Major express
     */
    const MAJOR_EXPRESS_LOGIN = '592477';
    const MAJOR_EXPRESS_PASS = '892177';
    const MAJOR_EXPRESS_API_URL = 'https://ltl-ws.major-express.ru/edclients/edclients.asmx?WSDL';

    /**
     * Выполняет проверку вручения или отслеживание отправления. Если передается параметр $full, то будет возвращен список
     * движений по отправлению. Если не передается, то будет выполнена проверка вручения и результат типа bool.
     * @param $track_num string трек-номер отправления
     * @param null $full при наличии значения в переменной будет возвращен полный спиок движений по отправлению
     * @return bool|string
     */
    public static function trackPochtaRu($track_num, $full = null)
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

        try {
            $result = $client2->getOperationHistory(new \SoapParam($params3,'OperationHistoryRequest'));
        }
        catch (\Exception $exception) {
            return false;
        }

        if ($full != null) {
            $tracking = '';
            foreach ($result->OperationHistoryData->historyRecord as $record) {
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
            foreach ($result->OperationHistoryData->historyRecord as $record) {
                if ($record->OperationParameters->OperType->Id == 2) { // 2 - операция Вручение на Почте России
                    return strtotime($record->OperationParameters->OperDate);
                }
            };

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

            if ($result)
                return $result;
            else
                return 'Невозможно загрузить результаты.';
        }

        return false;
    }
}