<?php

namespace backend\models;

use common\models\FerrymenInvitations;
use Yii;
use yii\base\Model;
use common\models\Ferrymen;

/**
 * @property string $ferryman_id
 * @property string $email
 *
 * @property Ferrymen $ferryman
 */
class FerrymanInvitationForm extends Model
{
    /**
     * @var string перевозчик
     */
    public $ferryman_id;

    /**
     * @var string E-mail получателя приглашения
     */
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ferryman_id', 'email'], 'required'],
            [['email'], 'string'],
            [['email'], 'email'],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ferryman_id' => 'Перевозчик',
            'email' => 'E-mail',
        ];
    }

    /**
     * Выполняет создание и запись в базу приглашения, а затем отправляет E-mail с пришглашением зарегистрироваться.
     * @return bool
     */
    public function sendInvitation()
    {
        // либо обновляем существующее приглашение либо же создаем новое
        $model = FerrymenInvitations::findOne(['ferryman_id' => $this->ferryman_id]);
        if ($model == null)
            $model = new FerrymenInvitations([
                'ferryman_id' => $this->ferryman_id,
                'email' => $this->email,
            ]);
        else $model->created_at = time();
        $model->token = Yii::$app->security->generateRandomString();
        $model->expires_at = time() + FerrymenInvitations::TOKEN_EXPIRATION_TIME;

        if ($model->save()) {
            // отправляем приглашение на Email
            $letter = Yii::$app->mailer->compose([
                'html' => 'ferrymanInvitation-html',
            ], ['token' => $model->token])
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderMelania']])
                ->setTo($this->email)
                ->setSubject('Приглашение создать аккаунт в личном кабинете');

            return $letter->send();
        }

        return false;
    }

    /**
     * @return Ferrymen
     */
    public function getFerrymen()
    {
        return Ferrymen::findOne(['id' => 'ferryman_id']);
    }
}
