<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use dektrium\user\models\Profile as BaseProfile;

/**
 * Модель для таблицы "profile".
 *
 * @property integer $user_id
 * @property string $name
 * @property string $fo_id идентификатор в системе Fresh Office
 * @property integer $limit_cp_me лимит отправок через Major Express
 *
 * @property User $user
 */

class Profile extends BaseProfile
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['fo_id', 'limit_cp_me'], 'integer'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'fo_id' => 'Пользователь Fresh Office',
            'limit_cp_me' => 'Лимит отправок Major Express',
        ]);
    }
}
