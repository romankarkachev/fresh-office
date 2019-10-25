<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\components\TotalsColumn;
use backend\controllers\EcoProjectsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\EcoProjectsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */
/* @var $progressFilterApplied bool */
/* @var $searchProgresses array */

$this->title = EcoProjectsController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = EcoProjectsController::ROOT_LABEL;
?>
<div class="eco-projects-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?php // Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

    </p>
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied, 'searchProgresses' => $searchProgresses]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'showFooter' => true,
        'footerRowOptions' => ['class' => 'text-right'],
        'columns' => [
            [
                'attribute' => 'id',
                'options' => ['width' => '40'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'options' => ['width' => '130'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'responsibleProfileName',
                'visible' => Yii::$app->user->can('root') || Yii::$app->user->can('ecologist_head'),
            ],
            [
                'attribute' => 'typeName',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '100'],
            ],
            [
                'attribute' => 'customerName',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoProjects */
                    /* @var $column \yii\grid\DataColumn */

                    if (!empty(trim($model->comment))) {
                        return Html::tag('abbr', $model->{$column->attribute}, ['title' => $model->comment]);
                    }
                    else {
                        return $model->{$column->attribute};
                    }
                },
            ],
            [
                'label' => 'Этап',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoProjects */
                    /* @var $column \yii\grid\DataColumn */

                    $prependText = 'Все ';
                    $appendText = ' завершены ';
                    $text = \common\models\foProjects::declension($model->totalMilestonesCount, ['этап','этапа','этапов']);
                    if (empty($model->closed_at)) {
                        $prependText = '';
                        $appendText = '';
                        $text = $model->currentMilestoneName . ' (' . ($model->milestonesDoneCount+1) . ' / ' . $model->totalMilestonesCount . ')';

                        // если этап еще не закрыт, то проверим, не просрочены ли сроки его выполнения
                        if (!empty($model->currentMilestoneDatePlan) && strtotime($model->currentMilestoneDatePlan) <= time()) {
                            $appendText = ' <i class="fa fa-exclamation-triangle text-warning" aria-hidden="true" title="Данный этап просрочен с ' . Yii::$app->formatter->asDate($model->currentMilestoneDatePlan, 'php:d.m.Y г.') . '"></i>';
                        }
                    }

                    return $prependText . Html::a($text, '#', [
                        'class' => 'link-ajax',
                        'id' => 'showDetailedInfo' . $model->id,
                        'data-id' => $model->id,
                        'title' => 'Показать в модальном окне более подробную информацию по этапам этого проекта'
                    ]) . $appendText;
                },
            ],
            [
                'label' => 'Сроки',
                'format' => 'html',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoProjects */
                    /* @var $column \yii\grid\DataColumn */

                    $result = Yii::$app->formatter->asDate($model->date_start, 'php:d F Y г.') . ' &rarr; ';

                    if (!empty($model->closed_at)) {
                        if (Yii::$app->formatter->asDate($model->date_close_plan, 'php:Y-m-d 00:00:00') == Yii::$app->formatter->asDate($model->closed_at, 'php:Y-m-d 00:00:00'))
                            // не уверен, что есть смысл еще одну галочку выводить, если совпали сроки:
                            //$isMatches = ' <i class="fa fa-check text-success" aria-hidden="true" title="Планируемый срок совпал с фактическим"></i>';
                            $isMatches = '';
                        else
                            $isMatches = ' (план ' . Yii::$app->formatter->asDate($model->date_close_plan, 'php:d F Y г.') . ')';

                        $result .= Yii::$app->formatter->asDate($model->closed_at, 'php:d F Y г.') . $isMatches;
                    }
                    else {
                        $result .= Yii::$app->formatter->asDate($model->date_close_plan, 'php:d F Y г.');
                    }

                    return $result;
                },
            ],
            [
                'label' => 'Состояние',
                'format' => 'html',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoProjectsMilestones */
                    /* @var $column \yii\grid\DataColumn */

                    if (!empty($model->closed_at))
                        return '<i class="fa fa-check-circle text-success" aria-hidden="true" title="Проект завершен"></i>';
                    else
                        return '<small class="text-muted"><em>в работе...</em></small>';
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'visible' => !$progressFilterApplied,
            ],
            [
                'attribute' => 'date_finish_contract',
                'format' => 'date',
                'label' => 'Дата договор',
                'footer' => '<strong>Итого:</strong>',
                'footerOptions' => ['class' => 'text-right'],
                'contentOptions' => ['class' => 'text-center'],
                'headerOptions' => ['class' => 'text-center'],
            ],
            [
                'class' => TotalsColumn::className(),
                'attribute' => 'contract_amount',
                'format' => 'currency',
                'footerOptions' => ['style' => 'white-space:nowrap;'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-right'],
                'visible' => Yii::$app->user->can('root') || Yii::$app->user->can('ecologist_head'),
            ],
            //'comment:ntext',
            [
                'class' => 'backend\components\grid\ActionColumn',
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root') || Yii::$app->user->can('ecologist_head'),
                ],
            ],
        ],
    ]); ?>

    <div id="modalWindow" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-info" role="document">
            <div class="modal-content">
                <div class="modal-header"><h4 id="modalTitle" class="modal-title">Modal title</h4></div>
                <div id="modalBody" class="modal-body"><p>One fine body…</p></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$urlModalProjectsMilestones = \yii\helpers\Url::to(['/eco-projects/modal-projects-milestones']);

// лайфхачек для увеличения размеров модального окошечечка
$this->registerCss(<<<CSS
@media (min-width: 768px) {
  .modal-xl {
    width: 90%;
   max-width:1200px;
  }
}
CSS
);

$this->registerJs(<<<JS
// Обработчик щелчка по одной из ссылок "Подробная информация об этапах проекта".
//
function showMilestonesDetailedInfoOnClick() {
    id = $(this).attr("data-id");
    if (id != "" && id != undefined) {
        $("#modalTitle").text("Этапы проекта");
        $("#modalBody").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#modalWindow").modal();
        $("#modalBody").load("$urlModalProjectsMilestones?id=" + id);
    }

    return false;
} // showMilestonesDetailedInfoOnClick()

$(document).on("click", "a[id ^= 'showDetailedInfo']", showMilestonesDetailedInfoOnClick);
JS
, \yii\web\View::POS_READY);
?>
