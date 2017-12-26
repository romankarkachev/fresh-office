<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use common\models\CorrespondencePackagesSearch;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CorrespondencePackagesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Корреспонденция | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Пакеты корреспонденции';
?>
<div class="correspondence-packages-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-truck"></i> Сформировать пакет', '#', ['class' => 'btn btn-default pull-right', 'id' => 'btnComposePackage', 'title' => 'Выделите несколько пакетов документов, чтобы на них на всех назначить одинаковые параметры']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'gw-packages',
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'options' => ['width' => '30'],
            ],
            'fo_project_id',
            [
                'label' => 'Ожидание отправки',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\CorrespondencePackages */

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

                    return Yii::$app->formatter->asDate($model->sent_at, 'php:d.m.Y');
                },
                'visible' => $searchModel->searchGroupProjectStates == CorrespondencePackagesSearch::CLAUSE_STATE_SENT,
            ],
            [
                'attribute' => 'delivered_at',
                'label' => 'Доставлено',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\CorrespondencePackages */

                    return Yii::$app->formatter->asDate($model->delivered_at, 'php:d.m.Y');
                },
                'visible' => $searchModel->searchGroupProjectStates == CorrespondencePackagesSearch::CLAUSE_STATE_DELIVERED || $searchModel->searchGroupProjectStates == CorrespondencePackagesSearch::CLAUSE_STATE_FINISHED,
            ],
            'customer_name',
            'stateName',
            'typeName',
            [
                'attribute' => 'pad',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\CorrespondencePackages */
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
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'options' => ['width' => '80'],
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
$url_form = Url::to(['/correspondence-packages/compose-package-form']);
$name = 'ComposePackageForm[tpPad]';

$this->registerJs(<<<JS
var checked = false;

// Обработчик щелчка по кнопке "Сформировать пакет".
//
function btnComposePackageFormOnClick() {
    var ids = $("#gw-packages").yiiGridView("getSelectedRows");
    if (ids == "") return false;

    $("#modal_title").text("Формирование отправления из пакетов документов");
    $("#modal_body_compose_addr").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
    $("#mw_compose").modal();
    $("#modal_body_compose_addr").load("$url_form?ids=" + ids);

    return false;
} // btnComposePackageFormOnClick()

// Ообработчик щелчка по кнопке Выполнить в окне формирования пакета.
// Выполняет видов документов, способа доставки, статуса и трек-номера.
//
function composePackageOnClick() {
    $("#frmComposePackage").submit();

    return false;
} // composePackageOnClick()

// Обработчик щелчка по ссылке "Отметить все документы".
//
function checkAllDocumentsOnClick() {
    if (checked) {
        operation = "uncheck";
        checked = false;
    }
    else {
        operation = "check";
        checked = true;
    }

    $("input[name ^= '$name']").iCheck(operation);

    return false;
} // checkAllDocumentsOnClick()

$(document).on("click", "#btnComposePackage", btnComposePackageFormOnClick);
$(document).on("click", "#btn-process", composePackageOnClick);
$(document).on("click", "#checkAllDocuments", checkAllDocumentsOnClick);
JS
, \yii\web\View::POS_READY);
?>
