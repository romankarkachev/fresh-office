<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use common\models\CorrespondencePackagesSearch;
use common\models\CorrespondencePackagesStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CorrespondencePackagesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Корреспонденция | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Пакеты корреспонденции';

$canRoot = Yii::$app->user->can('root');
$canOperatorHead = Yii::$app->user->can('operator_head');
$urlComposePackage = Url::to(['/correspondence-packages/compose-package-by-selection']);

$gridViewId = 'gw-packages';

$btnDeleteFewPrompt = '<i class="fa fa-trash-o" aria-hidden="true"></i> удалить выбранные';
$btnDeleteFewId = 'deleteSelected';
$btnDeleteFew = '';
if ($canRoot || $canOperatorHead) {
    $btnDeleteFew = Html::a($btnDeleteFewPrompt, '#', ['id' => $btnDeleteFewId, 'class' => 'btn btn-danger btn-xs', 'title' => 'Удалить выделенные пакеты корреспонденции']);
}
?>
<div class="correspondence-packages-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php if ($canRoot || $canOperatorHead): ?>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-truck"></i> Сформировать пакет', $urlComposePackage, [
            'class' => 'btn btn-default pull-right',
            'id' => 'btnComposePackage',
            'title' => 'Выделите несколько пакетов документов, чтобы на них на всех назначить одинаковые параметры',
        ]) ?>

        <?php endif; ?>
    </p>
    <?= GridView::widget([
        'id' => $gridViewId,
        'dataProvider' => $dataProvider,
        'layout' => "<div style=\"position: relative; min-height: 20px;\"><small class=\"pull-right form-text text-muted\" style=\"position: absolute; bottom: 0; right: 0;\">{summary}</small></div>\n{items}\n<div class=\"row\"><div class=\"col-md-6\">$btnDeleteFew</div><div class=\"col-md-6\"><small class=\"pull-right form-text text-muted\">{summary}</small></div></div>\n{pager}",
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'rowOptions' => function ($model, $key, $index, $grid) {
            /* @var $model \common\models\CorrespondencePackages */
            /* @var $column \yii\grid\DataColumn */

            switch ($model->cps_id) {
                case CorrespondencePackagesStates::STATE_ЧЕРНОВИК:
                    return ['class' => 'warning'];
                    break;
                case CorrespondencePackagesStates::STATE_ОТКАЗ:
                    return ['class' => 'danger'];
                    break;
                case CorrespondencePackagesStates::STATE_УТВЕРЖДЕН:
                    return ['class' => 'success'];
                    break;
            }
        },
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'options' => ['width' => '30'],
                'visible' => $canRoot || $canOperatorHead,
            ],
            [
                'attribute' => 'fo_project_id',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\CorrespondencePackages */
                    /* @var $column \yii\grid\DataColumn */

                    $addon = '';
                    if ($model->is_manual) $addon = ' <i class="fa fa-hand-paper-o text-info" aria-hidden="true"></i>';

                    return $model->{$column->attribute} . $addon;
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'label' => 'Ожидание отправки',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\CorrespondencePackages */
                    /* @var $column \yii\grid\DataColumn */

                    $border = time();
                    // было раньше так:
                    //if ($model->sent_at != null) $border = $model->sent_at;
                    if ($model->ready_at != null) $border = $model->ready_at;
                    return \common\models\foProjects::downcounter($model->created_at, $border);
                },
                'visible' => $searchModel->searchGroupProjectStates != CorrespondencePackagesSearch::CLAUSE_STATE_SENT,
            ],
            [
                'attribute' => 'sent_at',
                'label' => 'Отправлено',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\CorrespondencePackages */
                    /* @var $column \yii\grid\DataColumn */

                    return Yii::$app->formatter->asDate($model->sent_at, 'php:d.m.Y');
                },
                'visible' => $searchModel->searchGroupProjectStates == CorrespondencePackagesSearch::CLAUSE_STATE_SENT,
            ],
            [
                'attribute' => 'delivered_at',
                'label' => 'Доставлено',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\CorrespondencePackages */
                    /* @var $column \yii\grid\DataColumn */

                    return Yii::$app->formatter->asDate($model->delivered_at, 'php:d.m.Y');
                },
                'visible' => $searchModel->searchGroupProjectStates == CorrespondencePackagesSearch::CLAUSE_STATE_DELIVERED || $searchModel->searchGroupProjectStates == CorrespondencePackagesSearch::CLAUSE_STATE_FINISHED,
            ],
            [
                'attribute' => 'customer_name',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\CorrespondencePackages */
                    /* @var $column \yii\grid\DataColumn */

                    // присоединяем контактное лицо
                    $contactPerson = '';
                    if (!empty($model->contact_person)) $contactPerson = ' [' . $model->contact_person . ']';

                    // присоединяем ответственного
                    $managerName = '';
                    if (!empty($model->managerProfileName)) $managerName = '<br /><small class="text-muted"><em> ' . $model->managerProfileName . '</em></small>';

                    return $model->{$column->attribute} . $contactPerson . $managerName;
                },
            ],
            [
                'attribute' => 'cpsName',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\CorrespondencePackages */
                    /* @var $column \yii\grid\DataColumn */

                    $result = $model->{$column->attribute};
                    if (!empty($model->lastCpsChangedAt)) {
                        $result = Html::tag('abbr', $result, ['title' => 'Статус приобретен ' . Yii::$app->formatter->asDate($model->lastCpsChangedAt, 'php:d.m.Y в H:i')]);
                    }

                    return $result;
                },
                'visible' => $canOperatorHead || Yii::$app->user->can('sales_department_manager'),
            ],
            [
                'attribute' => 'stateName',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) use ($canRoot) {
                    /* @var $model \common\models\CorrespondencePackages */
                    /* @var $column \yii\grid\DataColumn */

                    $cpsName = '';
                    if ($model->is_manual && $canRoot) {
                        $cpsName = $model->cpsName;
                        if (!empty($model->lastCpsChangedAt)) {
                            $cpsName = Html::tag('abbr', $cpsName, ['title' => 'Статус приобретен ' . Yii::$app->formatter->asDate($model->lastCpsChangedAt, 'php:d.m.Y в H:i')]);
                        }
                        $cpsName = ' <small class="text-muted"><em>' . $cpsName . '</em></small>';
                    }

                    return $model->{$column->attribute} . $cpsName;
                },
            ],
            'typeName',
            [
                'attribute' => 'pad',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\CorrespondencePackages */
                    /* @var $column \yii\grid\DataColumn */

                    $result = '';

                    $pad = json_decode($model->{$column->attribute}, true);
                    if (is_array($pad))
                        foreach ($pad as $document)
                            if ($document['is_provided'] == false)
                                $result .= '<span class="text-muted">' . $document['name'] . '</span> ';
                            else
                                $result .= '<strong>' . $document['name'] . '</strong> ';
                            //$result .= '<span class="' . ($document['is_provided'] == false ? 'text-muted' : 'text-success') . '">' . $document['name'] . '</span> ';

                    return $result;
                },
            ],
            'pdName',
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                //'template' => '{compose-envelope} {update} {edf} {pochtaRuPrint} {delete}',
                'template' => '{update} {compose-envelope} {edf} {pochtaRuPrint} {delete}',
                'buttons' => [
                    'compose-envelope' => function ($url, $model) {
                        return Html::a('<i class="fa fa-envelope-open-o"></i>', ['/correspondence-packages/compose-envelope', 'id' => $model->id], ['title' => 'Вывести на печать конверт', 'class' => 'btn btn-xs btn-default']);
                    },
                    'pochtaRuPrint' => function ($url, $model) {
                        if ($model->pd_id == \common\models\PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ && $model->pochta_ru_order_id != null)
                            return Html::a('<i class="fa fa-barcode"></i>', \backend\controllers\TrackingController::POCHTA_RU_URL_ORDER_PRINT . $model->pochta_ru_order_id, ['title' => 'Печать конверта', 'class' => 'btn btn-xs btn-default', 'target' => '_blank']);
                        else
                            return '';
                    },
                    'edf' => function ($url, $model) {
                        if (!empty($model->edf))
                            return Html::a('<i class="fa fa-file-word-o"></i>', ['/edf/update', 'id' => $model->edf->id], ['title' => 'Открыть электронный документ', 'class' => 'btn btn-xs btn-default']);
                        else
                            return '';
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'visibleButtons' => [
                    'delete' => $canRoot || $canOperatorHead,
                ],
                'options' => ['width' => '120'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
<div id="mw_compose" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modal_title" class="modal-title">Modal title</h4>
            </div>
            <div id="modal_body_compose_addr" class="modal-body">
                <p>One fine body…</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btn-process"><i class="fa fa-cog"></i> Выполнить</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php
$name = 'ComposePackageForm[tpPad]';

$urlDeleteFew = Url::to(\backend\controllers\CorrespondencePackagesController::URL_DELETE_SELECTED_AS_ARRAY);

$this->registerJs(<<<JS
var checked = false;
$("input[type='checkbox']").iCheck({checkboxClass: 'icheckbox_square-green'});

// Выполняет пересчет количества выделенных пользователем файлов и подставляет отличное от нуля значение в текст кнопки.
//
function recountSelected() {
    var count = $("input[name ^= 'selection[]']:checked").length;
    var prompt = "";
    var promptDelete = '$btnDeleteFewPrompt';
    if (count > 0) {
        prompt = " <strong>(" + count + ")</strong>";
        promptDelete += prompt;
    }

    $("#$btnDeleteFewId").html(promptDelete);
} // recountSelected()

// Обработчик щелчка по ссылке "Отметить все".
//
function checkAllOnClick() {
    if (checked) {
    operation = "uncheck";
    checked = false;
    }
    else {
        operation = "check";
        checked = true;
    }

    $("input[name ^= 'selection[]']").iCheck(operation);
    recountSelected();

    return false;
} // checkAllOnClick()

// Обработчик щелчка по ссылке "Удалить выделенные файлы".
//
function deleteSelectedOnClick() {
    var ids = $("#$gridViewId").yiiGridView("getSelectedRows");
    if (ids == "") return false;

    if (confirm("Вы действительно хотите удалить выделенные пакеты корреспонденции безвозвратно?")) {
        $.post("$urlDeleteFew", {ids: ids}, function() {});
    }

    return false;
} // deleteSelectedOnClick()

// Обработчик щелчка по кнопке "Сформировать пакет".
//
function btnComposePackageFormOnClick() {
    var ids = $("#gw-packages").yiiGridView("getSelectedRows");
    if (ids == "") return false;

    $.get("$urlComposePackage?ids=" + ids);

    return false;
} // btnComposePackageFormOnClick()

// Ообработчик щелчка по кнопке Выполнить в окне формирования пакета.
// Выполняет видов документов, способа доставки, статуса и трек-номера.
//
function composePackageOnClick() {
    $("#frmComposePackage").submit();

    return false;
} // composePackageOnClick()

$("input[name ^= 'selection[]']").on("ifChanged", recountSelected);
$(".select-on-check-all").on("ifClicked", checkAllOnClick);
$(document).on("click", "#btnComposePackage", btnComposePackageFormOnClick);
$(document).on("click", "#btn-process", composePackageOnClick);
$(document).on("click", "#$btnDeleteFewId", deleteSelectedOnClick);
JS
, \yii\web\View::POS_READY);
?>
