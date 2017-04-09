<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Модель для данных, отправляемых снаружи.
 *
 * @property string $company
 * @property string $name
 * @property string $region
 * @property string $phone
 * @property string $email
 * @property string $message
 */
class ExternalAppealForm extends Model
{
    /**
     * Компания.
     * @var string
     */
    public $company;

    /**
     * Имя.
     * @var string
     */
    public $name;

    /**
     * Регион.
     * @var string
     */
    public $region;

    /**
     * Номер телефона.
     * @var string
     */
    public $phone;

    /**
     * E-mail.
     * @var string
     */
    public $email;

    /**
     * Текст сообщения.
     * @var string
     */
    public $message;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company', 'name', 'region', 'phone', 'email', 'message'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'company' => 'Компания',
            'name' => 'Имя',
            'region' => 'Регион',
            'phone' => 'Телефон',
            'email' => 'Email',
            'message' => 'Текст сообщения',
        ];
    }
}
