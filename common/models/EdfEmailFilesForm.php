<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Форма, позволяющая отправить выделенные пользователем файлы документооборота на E-mail, например, заказчика.
 *
 * @property integer $ed_id электронный документ
 * @property array $files файлы к отправке
 * @property string $email_receiver получатель письма
 * @property string $email_sender отправитель файлов
 * @property string $comment произвольный комментарий, который включается в текст письма
 *
 * @property Edf $ed
 */
class EdfEmailFilesForm extends Model
{
    /**
     * @var integer идентификатор электронного документа
     */
    public $ed_id;

    /**
     * @var array файлы
     */
    public $files;

    /**
     * @var string получатель письма
     */
    public $email_receiver;

    /**
     * @var string отправитель файлов
     */
    public $email_sender;

    /**
     * @var string комментарий включается в текст письма
     */
    public $comment;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ed_id', 'files', 'email_receiver', 'email_sender'], 'required'],
            [['ed_id'], 'integer'],
            [['files'], 'safe'],
            [['comment'], 'safe'],
            [['email_receiver', 'email_sender', 'comment'], 'trim'],
            [['email_receiver', 'email_sender'], 'email'],
            [['ed_id'], 'exist', 'skipOnError' => true, 'targetClass' => Edf::class, 'targetAttribute' => ['ed_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ed_id' => 'Документооборот',
            'files' => 'Файлы',
            'email_receiver' => 'E-mail получателя',
            'email_sender' => 'E-mail отправителя',
            'comment' => 'Комментарий',
        ];
    }

    /**
     * @return Edf
     */
    public function getEdf()
    {
        return Edf::findOne(['id' => $this->ed_id]);
    }
}
