<?php

namespace customer\models;

use Yii;
use yii\base\Model;
use common\models\Appeals;
use common\models\CustomerInvitations;
use common\models\FreshOfficeAPI;
use common\models\foCompany;
use common\models\foProjects;

/**
 * Форма отправки нескольких видов обращений в нашу компанию.
 *
 * @property integer $type
 * @property string $comment
 *
 * @author Roman Karkachev <post@romankarkachev.ru>
 */
class CustomerRequestForm extends Model
{
    /**
     * Возможные значения для поля "Тип обращения"
     */
    const ТИП_ОБРАЩЕНИЯ_ВЫЗОВ_МЕНЕДЖЕРА = 1;
    const ТИП_ОБРАЩЕНИЯ_ОБРАТНАЯ_СВЯЗЬ = 2;
    const ТИП_ОБРАЩЕНИЯ_ЗАКАЗ = 3;
    const ТИП_ОБРАЩЕНИЯ_ЖАЛОБА = 4;

    /**
     * @var integer тип обращения
     */
    public $type;

    /**
     * @var string произвольный комментарий
     */
    public $comment;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'comment'], 'required'],
            [['type'], 'integer'],
            [['comment'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type' => 'Тип обращения',
            'comment' => 'Комментарий',
        ];
    }

    /**
     * Выполняет отправку обращения клиента в соответствии с его типом.
     * @return bool|string успех или текст сообщения об ошибке
     */
    public function sendRequest()
    {
        $invitation = CustomerInvitations::findOne(['user_id' => Yii::$app->user->id]);
        if (!empty($invitation)) {
            $company = foCompany::findOne(['ID_COMPANY' => $invitation->fo_ca_id]);
            if (!empty($company)) {
                switch ($this->type) {
                    case self::ТИП_ОБРАЩЕНИЯ_ВЫЗОВ_МЕНЕДЖЕРА:
                        // создается задача в срм – тип «встреча», текст – клиент нажал на кнопку приезжайте ко мне
                        $comment = 'Клиент нажал на кнопку «Приезжайте ко мне» в личном кабинете.';
                        if (!empty($this->comment)) $comment .= ' Комментарий заказчика: ' . chr(13) . $this->comment;
                        $foapiResult = Appeals::foapi_createNewTaskForManager(1, 38, $comment, FreshOfficeAPI::TASK_TYPE_ВСТРЕЧА);
                        if ($foapiResult !== false) {
                            if (!is_numeric($foapiResult) && $foapiResult !== true)
                                Yii::$app->session->setFlash('error', $foapiResult);
                            else {
                                Yii::$app->session->setFlash('success', 'Заявка на вызов менеджера успешно принята. Ожидайте звонка.');
                            }
                        }
                        break;
                    case self::ТИП_ОБРАЩЕНИЯ_ОБРАТНАЯ_СВЯЗЬ:
                        // создается задача в срм – тип «связь через ЛК» - текст «клиент нажал кнопку в личном кабинете и просил с ним связаться»
                        $comment = 'Клиент нажал кнопку в личном кабинете и просил с ним связаться.';
                        if (!empty($this->comment)) $comment .= ' Комментарий заказчика: ' . chr(13) . $this->comment;
                        $foapiResult = Appeals::foapi_createNewTaskForManager($invitation->fo_ca_id, $company->ID_MANAGER, $comment, FreshOfficeAPI::TASK_TYPE_СВЯЗЬ_ЧЕРЕЗ_ЛК);
                        if ($foapiResult !== false) {
                            if (!is_numeric($foapiResult) && $foapiResult !== true)
                                Yii::$app->session->setFlash('error', $foapiResult);
                            else {
                                Yii::$app->session->setFlash('success', 'Заявка на обратную связь успешно принята. В ближайшее время с Вами свяжется наш менеджер.');
                            }
                        }
                        break;
                    case self::ТИП_ОБРАЩЕНИЯ_ЗАКАЗ:
                        // создается проект в срм + задача тип «связь через ЛК»
                        $lastProject = foProjects::find()
                            ->where(['ID_COMPANY' => $invitation->fo_ca_id])
                            ->orderBy('DATE_CREATE_PROGECT DESC')->one();

                        if (!empty($lastProject)) {
                            $foapiResult = FreshOfficeAPI::foapi_createNewProject($invitation->fo_ca_id, $company->ID_MANAGER, $lastProject->ID_LIST_SPR_PROJECT);
                            if ($foapiResult !== false) {
                                if (!is_numeric($foapiResult) && $foapiResult !== true)
                                    Yii::$app->session->setFlash('error', $foapiResult);
                                else {
                                    $comment = 'Клиент создал проект из личного кабинета.';
                                    if (!empty($this->comment)) $comment .= ' Комментарий заказчика: ' . chr(13) . $this->comment;
                                    $foapiResult = Appeals::foapi_createNewTaskForManager($invitation->fo_ca_id, $company->ID_MANAGER, $comment, FreshOfficeAPI::TASK_TYPE_СВЯЗЬ_ЧЕРЕЗ_ЛК);
                                    if ($foapiResult !== false) {
                                        if (!is_numeric($foapiResult) && $foapiResult !== true)
                                            Yii::$app->session->setFlash('error', $foapiResult);
                                        else {
                                            // вот здесь идентификатор созданной задачи
                                            // это означает успех
                                            Yii::$app->session->setFlash('success', 'Заявка на вывоз успешно создана!');
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case self::ТИП_ОБРАЩЕНИЯ_ЖАЛОБА:
                        // при нажатии заполняет текст + улетает на почту мне контрагент и текст
                        $letter = Yii::$app->mailer->compose([
                            'html' => 'customersComplains-html',
                        ], [
                            'customerName' => $company->COMPANY_NAME,
                            'userName' => Yii::$app->user->identity->profile->name,
                            'compliantDetails' => $this->comment,
                        ])
                            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderMelania']])
                            ->setTo(Yii::$app->params['receiverEmail'])
                            ->setSubject('Жалоба заказчика, отправленная из его личного кабинета');

                        if ($letter->send()) {
                            Yii::$app->session->setFlash('success', 'Ваше обращение успешно отправлено. Благодарим.');
                            return true;
                        }
                        else {
                            Yii::$app->session->setFlash('error', 'Не удалось отправить Ваше обращение!');
                            return false;
                        }
                        break;
                }
            }
            else return 'Контрагент не обнаружен. Продолжение невозможно.';
        }
        else return 'Приглашение не обнаружено. Продолжение невозможно.';
    }
}
