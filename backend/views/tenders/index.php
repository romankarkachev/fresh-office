<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use backend\controllers\TendersController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TendersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = TendersController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = TendersController::ROOT_LABEL;
?>
<div class="tenders-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'orgName',
            [
                'attribute' => 'date_auction',
                'label' => 'Аукцион',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
                'visible' => Yii::$app->user->can('tenders_manager'),
            ],
            [
                'attribute' => 'responsibleProfileName',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Tenders */
                    /* @var $column \yii\grid\DataColumn */

                    if (!empty($model->{$column->attribute})) {
                        return $model->{$column->attribute};
                    }
                    else {
                        return Html::tag('span', null, ['id' => 'responsibleEmpty' . $model->id]);
                    }
                },
            ],
            [
                'attribute' => 'date_stop',
                'label' => 'Окончание',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'attribute' => 'managerProfileName',
                'visible' => !Yii::$app->user->can('sales_department_manager'), // менеджеру недоступно, он и так видит только свои
            ],
            'stateName',
            'title:ntext',
            //'fo_ca_id',
            'fo_ca_name:ntext:Контрагент',
            //'tp_id',
            //'we',
            //'manager_id',
            'conditions:ntext:Примечание',
            //'date_complete',
            //'date_stop',
            //'ta_id',
            //'is_notary_required',
            //'is_contract_edit',
            //'amount_start',
            //'amount_offer',
            //'deferral',
            //'is_contract_approved',
            //'comment:ntext',
            [
                'class' => 'backend\components\grid\ActionColumn',
                'template' => '{take_over} {update} {delete}',
                'buttons' => [
                    'take_over' => function ($url, $model) {
                        if (empty($model->responsible_id) && (Yii::$app->user->can('tenders_manager') || Yii::$app->user->can('root'))) {
                            return Html::button('<i class="fa fa-hand-paper-o"></i>', ['id' => 'takeOver' . $model->id, 'data-id' => $model->id, 'title' => 'Взять работу на себя', 'class' => 'btn btn-xs btn-default', 'data-loading-text' => '<i class="fa fa-cog fa-spin text-muted"></i>']);
                        }
                    },
                ],
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root'),
                ],
                'options' => ['width' => '130'],
            ],
        ],
    ]); ?>

</div>
<?php
$urlTakeWorkOver = Url::to(TendersController::URL_TAKE_WORK_OVER_AS_ARRAY);

$this->registerJs(<<<JS
// Обработчик щелчков по кнопкам "Взять работу на себя".
//
function takeWorkOverOnClick() {
    if (confirm("Вы действительно хотите взять данный тендер на себя?")) {
        var \$btn = $(this);
        \$btn.button("loading");
        
        id = $(this).attr("data-id");
        if (id) {
            $.post("$urlTakeWorkOver", {id: id}, function(retval) {
                if (retval.result == false) {
                    if (retval.errorMsg) {
                        alert(retval.errorMsg);
                    }
                    else {
                        alert("Не удалось интерактивно изменить исполнителя по тендеру!");
                    }
                }
                else if (retval.result == true) {
                    // больше нельзя взять в работу, запрещаем перехватить тендер
                    \$btn.remove();
                    // также вставляем в соответствующую ячейку имя нового исполнителя
                    $("#responsibleEmpty" + id).html('<strong class="text-success" title="Вы взяли этот тендер на себя">' + retval.responsibleName + '</strong>');
                }
            }).always(function () {
                \$btn.button("reset");
            });
        }
    }

    return false;
} // takeWorkOverOnClick()

$(document).on("click", "button[id ^= 'takeOver']", takeWorkOverOnClick);
JS
, yii\web\View::POS_READY);
?>
