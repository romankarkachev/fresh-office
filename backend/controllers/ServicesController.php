<?php

namespace backend\controllers;

use common\models\CargosStates;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use common\models\Currencies;
use common\models\CurrenciesCrosses;
use common\models\Settings;
use common\models\Transactions;
use common\models\TransactionsStates;

class ServicesController extends Controller
{
    /**
     * Запрашивает и в случае успеха сохраняет курсы валют с сайта Центробанка РФ.
     * Сохранение производится в специальное поле таблицы currencies и в отдельную таблицу (на дату).
     * Выбираются для обновления только те валюты, у которых установлен соответствующий признак в true (is_fetch_cross).
     */
    public function actionFetchCbrCrosses()
    {
        $xml = new \DOMDocument();
        $url = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=' . date('d.m.Y');

        if (@$xml->load($url)) {
            $result = [];

            $root = $xml->documentElement;
            $items = $root->getElementsByTagName('Valute');

            foreach ($items as $item) {
                $code = $item->getElementsByTagName('CharCode')->item(0)->nodeValue;
                $rate = $item->getElementsByTagName('Nominal')->item(0)->nodeValue;
                $curs = $item->getElementsByTagName('Value')->item(0)->nodeValue;
                $result[$code] = floatval(str_replace(',', '.', $curs) / $rate);
            }

            if (count($result) > 0) {
                $currencies = Currencies::find()->where(['is_fetch_cross' => true])->all();
                foreach ($currencies as $currency) {
                    /* @var $currency \common\models\Currencies */
                    if (isset($result[$currency->name])) {
                        $current_cross = $result[$currency->name];

                        $currency->actual_cross = $current_cross;
                        $currency->save();

                        $cc = CurrenciesCrosses::find()->where(['currency_id' => $currency->id, 'date' => date('Y-m-d', time())])->one();
                        if ($cc != null) {
                            // обновление записи за сегодня
                            $cc->value = $current_cross;
                            $cc->save();
                        } else {
                            // создание новой записи за сегодня
                            $cc = new CurrenciesCrosses();
                            $cc->currency_id = $currency->id;
                            $cc->date = date('Y-m-d', time());
                            $cc->value = $current_cross;
                            $cc->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Выполняет выборку незакрытых сделок за последние семь и менее от текущей даты дней.
     * Если , то.
     */
    public function actionNotifyManagersAboutUnclosedTransactions()
    {
        $termin = 7;
        // если поле в базе в формате date
        //$days_ago_timestamp = date('Y-m-d', (mktime()-$termin*24*3600)).' 00:00:00';
        // если поле в базе в формате integer
        $days_ago_timestamp = strtotime(date('Y-m-d', (mktime()-$termin*24*3600)).' 23:59:59');
        $transactions = Transactions::find()
            ->where(['<=', '`created_at`', $days_ago_timestamp])
            ->andWhere(['`state_id`' => TransactionsStates::TS_OPENED])
            ->andWhere(['`cs_id`' => CargosStates::CS_DELIVERED_CLOSED])
            ->orderBy('`manager_id`, `created_at` DESC')
            ->all();

        $deals_common = '';
        $deals = '';
        $current_mid = -1;
        foreach ($transactions as $transaction) {
            /* @var $transaction \common\models\Transactions */
            if ($transaction->manager_id != $current_mid) {
                if ($current_mid != -1) {
                    Transactions::notifyUserAboutEvent($current_mid, 'transactionsAreOutdated', $deals);
                    $deals = '';
                }
            }

            $message_body = '<p>Сделка <strong>№ ' . $transaction->id . ' от ' . date('d.m.Y', $transaction->created_at) . '</strong> '.Html::a('Перейти', Url::to(['/transactions/update', 'id' => $transaction->id], true)).'</p>';
            $deals .= $message_body;
            $deals_common .= $message_body;

            $current_mid = $transaction->manager_id;
        }

        Transactions::notifyUserAboutEvent($current_mid, 'transactionsAreOutdated', $deals);

        // уведомление на почтовый ящик CRM
        $params = ['user_name' => 'Администратор'];
        $params['notifications'] = $deals_common;
        Yii::$app->mailer->compose([
            'html' => 'transactionsAreOutdated-html',
        ], $params)
            ->setFrom(Yii::$app->params['adminEmail'])
            ->setTo(Yii::$app->params['CRMEmail'])
            ->setSubject('Уведомление о сделках, статус которых продолжительное время не меняется')
            ->send();
    }
}