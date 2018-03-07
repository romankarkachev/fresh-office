<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;

/**
 * @property string $packages_ids
 * @property array $tdPad
 * @property integer $pd_id
 * @property string $track_num
 *
 * @property PostDeliveryKinds $pd
 */
class ComposePackageForm extends Model
{
    /**
     * Идентификаторы проектов.
     * @var string
     */
    public $packages_ids;

    /**
     * Табличная часть предоставленных видов документов.
     * @var array
     */
    public $tpPad;

    /**
     * Способ доставки.
     * @var integer
     */
    public $pd_id;

    /**
     * Трек-номер отправления.
     * @var integer
     */
    public $track_num;

    /**
     * Необходимость заменить табличную часть с видами документов в выбранных пакетах.
     * @var integer
     */
    public $isReplacePad;

    /**
     * Индекс адреса получателя.
     * @var string
     */
    public $zip_code;

    /**
     * Адрес.
     * @var string
     */
    public $address;

    /**
     * Контактное лицо.
     * @var string
     */
    public $contact_person;

    /**
     * Набор контактных лиц (если их несколько).
     * @var array
     */
    public $contactPersons;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['packages_ids', 'pd_id'], 'required'],
            [['pd_id', 'isReplacePad'], 'integer'],
            [['zip_code', 'address', 'contact_person'], 'string'],
            [['packages_ids', 'tpPad', 'contactPersons'], 'safe'],
            [['track_num'], 'string', 'max' => 50],
            [['pd_id'], 'exist', 'skipOnError' => true, 'targetClass' => PostDeliveryKinds::className(), 'targetAttribute' => ['pd_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'packages_ids' => 'Пакеты',
            'tpPad' => 'Виды документов',
            'pd_id' => 'Способ доставки',
            'track_num' => 'Трек-номер',
            'isReplacePad' => 'Заменить в выбранных пакетах табличную часть',
            'zip_code' => 'Индекс',
            'address' => 'Адрес',
            'contact_person' => 'Контактное лицо',
            'contactPersons' => 'Контактное лицо',
        ];
    }

    /**
     * Берет актуальные на момент исполнения виды документов. Проставляет галочки в тех из них, которые отметил
     * пользователь (отмеченные пользователем находятся в виртуальном поле tpPad).
     * @return array
     */
    public function convertPadTableToArray()
    {
        $padKinds = PadKinds::find()->select(['id', 'name', 'name_full', 'is_provided' => new Expression(0)])->orderBy('name_full')->asArray()->all();

        if (is_array($this->tpPad) && count($this->tpPad) > 0)
            foreach ($this->tpPad as $index => $document) {
                $key = array_search($index, array_column($padKinds, 'id'));
                if (false !== $key) $padKinds[$key]['is_provided'] = true;
            }

        return json_encode($padKinds);
    }

    /**
     * @return PostDeliveryKinds
     */
    public function getPd()
    {
        return PostDeliveryKinds::findOne(['id' => 'pd_id']);
    }
}
