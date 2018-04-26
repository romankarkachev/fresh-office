<?php

namespace common\components\bootstrap;

use \yii\bootstrap\ActiveForm as BaseActiveForm;

/**
 * Расширение класса.
 * @author Roman Karkachev <post@romankarkachev.ru>
 * @since 2.0
 */
class ActiveForm extends BaseActiveForm
{
    public $fieldConfig = ['errorOptions' => ['class' => 'text-danger']];
}
