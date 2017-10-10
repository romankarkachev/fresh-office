<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "responsible_for_production".
 *
 * @property integer $id
 * @property integer $type
 * @property string $receiver
 */
class ResponsibleForProduction extends \yii\db\ActiveRecord
{
    /**
     * Значения возможных типов записей.
     */
    const TYPE_ALWAYS = 1;
    const TYPE_MISMATCH = 2;

    /**
     * Наименование типа получателя.
     * @var string
     */
    public $typeName;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'responsible_for_production';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'receiver'], 'required'],
            [['type'], 'integer'],
            [['receiver'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Тип получателя', // 1 - всегда, 2 - при несовпадении
            'receiver' => 'E-mail',
            // вычисляемые поля
            'typeName' => 'Тип получателя',
        ];
    }

    /**
     * Возвращает массив возможных типов получателей.
     * @return array
     */
    public static function fetchTypes()
    {
        return [
            [
                'id' => self::TYPE_ALWAYS,
                'name' => 'Всегда',
            ],
            [
                'id' => self::TYPE_MISMATCH,
                'name' => 'При несовпадении',
            ],
        ];
    }

    /**
     * Делает выборку типов получателей и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::fetchTypes(), 'id', 'name');
    }

    /**
     * Выполняет создание задачи ответственному через API Fresh Office.
     * @param $ca_id integer идентификатор контрагента, который привязывается к задаче
     * @param $receiver_id integer идентификатор менеджера-исполнителя задачи
     * @param $note string текст задачи
     * @return array|integer|bool
     */
    public static function foapi_createNewTaskForManager($ca_id, $receiver_id, $note)
    {
        // для начала необходимо выполнить проверку: не требуется ли замена ответственного на реального человека
        $rs = ResponsibleSubstitutes::find()->select('required_id,substitute_id')->asArray()->all();
        if (count($rs) > 0) {
            // выборка ответственных для подмены успешно выполнена, есть записи
            $key = array_search($receiver_id, array_column($rs, 'required_id'));
            // проверим, не входит ли переданный ответственный в список подменяемых и заменим на реального, если входит
            if (false !== $key) $receiver_id = intval($rs[$key]['substitute_id']);
        };

        $params = [
            'company_id' => $ca_id,
            'user_id' => $receiver_id,
            'category_id' => FreshOfficeAPI::TASK_CATEGORY_СТАНДАРТНАЯ,
            'status_id' => FreshOfficeAPI::TASKS_STATUS_ЗАПЛАНИРОВАН,
            'type_id' => FreshOfficeAPI::TASK_TYPE_НЕСООТВЕТСТВИЕ_ГРУЗА_ТТН,
            'date_from' => date('Y-m-d\TH:i:s.u', time()),
            'date_till' => date('Y-m-d\TH:i:s.u', mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"))),
            'note' => $note,
        ];

        $response = FreshOfficeAPI::makePostRequestToApi('tasks', $params);
        // проанализируем результат, который возвращает API Fresh Office
        $decoded_response = json_decode($response, true);
        if (isset($decoded_response['error'])) {
            $inner_message = '';
            if (isset($decoded_response['error']['innererror']))
                $inner_message = ' ' . $decoded_response['error']['innererror']['message'];
            // возникла ошибка при выполнении
            return 'При создании задачи возникла ошибка: ' . $decoded_response['error']['message']['value'] . $inner_message;
        }
        elseif (isset($decoded_response['d']))
            // фиксируем идентификатор задачи, которая была успешно создана
            return $decoded_response['d']['id'];

        return false;
    }
}
