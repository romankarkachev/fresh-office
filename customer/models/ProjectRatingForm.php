<?php

namespace customer\models;

use Yii;
use yii\base\Model;
use common\models\ProjectsRatings;

/**
 * Форма дополнения произвольным комментарием любой не максимальной оценки сотрудничества по отдельному заказу.
 *
 * @author Roman Karkachev <post@romankarkachev.ru>
 */
class ProjectRatingForm extends Model
{
    /**
     * @var integer идентификатор контрагента
     */
    public $ca_id;

    /**
     * @var integer идентификатор проекта
     */
    public $project_id;

    /**
     * @var double
     */
    public $rate;

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
            [['comment'], 'required'],
            [['ca_id', 'project_id', 'rate'], 'integer'],
            [['comment'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ca_id' => 'Контрагент',
            'project_id' => 'Заказ',
            'rate' => 'Оценка',
            'comment' => 'Комментарий',
        ];
    }

    /**
     * @return bool
     */
    public function rateProject()
    {
        $model = ProjectsRatings::findOne([
            'ca_id' => $this->ca_id,
            'project_id' => $this->project_id,
        ]);
        if (empty($model)) {
            $model = new ProjectsRatings([
                'ca_id' => $this->ca_id,
                'project_id' => $this->project_id,
                'rate' => $this->rate,
                'comment' => $this->comment,
            ]);
            return $model->save();
        }
        else return true;
    }
}
