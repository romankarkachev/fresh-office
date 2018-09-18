<?php

namespace backend\models;

use common\models\CustomerInvitations;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use common\models\FerrymenInvitations;
use common\models\foListEmailClient;

/**
 * @property integer $fo_id_company идентификатор контрагента из Fresh Office
 * @property string $email
 */
class CustomerInvitationForm extends Model
{
    /**
     * @var string клиент
     */
    public $fo_id_company;

    /**
     * @var string E-mail получателя приглашения
     */
    public $email;

    /**
     * @var bool признак необходимости использовать собственный почтовый адрес для перенаправления с него приглашения
     */
    public $is_use_gateway;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fo_id_company', 'email'], 'required'],
            [['email'], 'string'],
            [['email'], 'trim'],
            ['is_use_gateway', 'integer'],
            // значение в поле E-mail не должно быть обнаружено в таблице user, то есть пользователь должен быть уникален
            ['email', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fo_id_company' => 'Заказчик',
            'email' => 'E-mail',
            'is_use_gateway' => 'Использовать шлюз',
        ];
    }

    /**
     * Делает выборку статусов клиентов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param $fo_ca_id integer идентификатор заказчика в Fresh Office
     * @return array
     */
    public static function arrayMapOfEmailsForSelect2($fo_ca_id)
    {
        return ArrayHelper::map(foListEmailClient::findAll(['ID_COMPANY' => intval($fo_ca_id)]), 'email', 'email');
    }

    /**
     * Выполняет создание и запись в базу приглашения, а затем отправляет E-mail с пришглашением зарегистрироваться.
     * @return bool
     */
    public function sendInvitation()
    {
        $model = CustomerInvitations::findOne(['fo_ca_id' => $this->fo_id_company]);
        if ($model == null)
            $model = new CustomerInvitations([
                'fo_ca_id' => $this->fo_id_company,
                'email' => $this->email,
            ]);
        else $model->created_at = time();
        $model->token = Yii::$app->security->generateRandomString();
        $model->expires_at = time() + FerrymenInvitations::TOKEN_EXPIRATION_TIME;

        if ($model->save()) {
            // отправляем приглашение на Email
            $letter = Yii::$app->mailer->compose([
                'html' => 'customerInvitation-html',
            ], ['token' => $model->token])
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderCompanyWaste']])
                ->setSubject('Приглашение создать аккаунт в личном кабинете');

            // если установлен признак "Использовать шлюз", то отправляем письмо себе же, с него менеджер перенаправит
            // клиенту, а, зайдя по ссылке, клиент сможет зарегистрироваться на свой ящик (который выбран здесь)
            if ($this->is_use_gateway) {
                $letter->setTo('vip@st77.ru');
            }
            else {
                $letter->setTo($this->email);
            }

            return $letter->send();
        }

        return false;
    }
}
