<?php

namespace backend\models;

use common\models\foCompany;
use common\models\foListPhones;
use Yii;
use yii\base\Model;
use common\models\pbxCalls;

/**
 * @property integer $call_id
 * @property integer $fo_ca_id
 *
 * @property pbxCalls $call
 */
class pbxIdentifyCounteragentForm extends Model
{
    /**
     * Подпись для кнопки отправки формы идентификации вручную
     */
    const BUTTON_SUBMIT_IDENTIFICATION_LABEL = 'Добавить номер к выбранному контрагенту';

    /**
     * @var integer идентификатор звонка
     */
    public $call_id;

    /**
     * @var string номер телефона
     */
    public $phone;

    /**
     * @var string список контрагентов, у которых встречается данный номер телефона
     */
    public $ambiguous;

    /**
     * @var integer идентификатор контрагента
     */
    public $fo_ca_id;

    /**
     * @var string наименование выбранного контрагента
     */
    public $fo_ca_name;

    /**
     * @var bool признак, определяющий необходимость выполнить замену во всех подобных звонках
     */
    public $is_process_other_calls;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['call_id', 'fo_ca_id'], 'required'],
            [['fo_ca_id', 'is_process_other_calls'], 'integer'],
            [['phone'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'call_id' => 'Звонок',
            'phone' => 'Номер телефона',
            'fo_ca_id' => 'Контрагент',
            'is_process_other_calls' => 'Идентифицировать другие звонки от этого абонента',
        ];
    }

    /**
     * Выполняет идентификацию контрагента в звонках.
     * @return bool
     */
    public function applyIdentification()
    {
        $call = $this->call;

        // если контрагент вообше не был идентифицирован, то добавим телефон в базу к выбранному контрагенту, чтобы
        // в будущем идентификация проходила
        if ($call->fo_ca_id == pbxCalls::ПРИЗНАК_КОНТРАГЕНТ_ВООБЩЕ_НЕ_ИДЕНТИФИЦИРОВАН) {
            (new foListPhones([
                'ID_COMPANY' => $this->fo_ca_id,
                'TELEPHONE' => $call->src,
                'COMMENT' => 'Добавлен из веб-приложения ' . Yii::$app->formatter->asDate(time(), 'php:d.m.Y в H:i') . ' пользователем ' . Yii::$app->user->identity->profile->name . ' для целей идентификации звонков.',
            ]))->save();
        }

        if ($this->is_process_other_calls) {
            // пользователь возжелал все звонки с этого номера идентифицировать выбранным контрагентом
            pbxCalls::updateAll([
                'fo_ca_id' => $this->fo_ca_id,
            ], [
                'src' => $call->src,
                'fo_ca_id' => $call->fo_ca_id,
            ]);
        }
        else {
            // идентифицируется только данный звонок
            $call->updateAttributes(['fo_ca_id' => $this->fo_ca_id]);
        }

        // отдаем обратно скрипту наименование контрагента для подстановки в строках таблицы
        $this->fo_ca_name = foCompany::findOne($this->fo_ca_id)->COMPANY_NAME;

        return true;
    }

    /**
     * @return pbxCalls
     */
    public function getCall()
    {
        return pbxCalls::findOne($this->call_id);
    }
}
