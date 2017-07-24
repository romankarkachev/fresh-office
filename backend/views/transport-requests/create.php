<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\TransportRequests */
/* @var $waste common\models\TransportRequestsWaste[] */
/* @var $transport common\models\TransportRequestsTransport[] */

$this->title = 'Новый запрос на транспорт | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']];
$this->params['breadcrumbs'][] = 'Новый *';
$this->params['breadcrumbs'][] = Html::a('<i class="fa fa-question-circle"></i> Подсказка', ['#frmHelp'], ['class' => 'btn btn-default btn-xs pull-right', 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frmHelp', 'title' => 'Показать подсказку']);
?>
<div class="transport-requests-create">
    <div id="frmHelp" class="collapse">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= $this->render('_help') ?>

            </div>
        </div>
    </div>
    <?= $this->render('_form', [
        'model' => $model,
        'waste' => $waste,
        'transport' => $transport,
    ]) ?>

    <div id="mw_summary" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-info" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal_title" class="modal-title">Modal title</h4>
                    <small id="modal_title_right" class="form-text"></small>
                </div>
                <div id="modal_body" class="modal-body">
                    <p>One fine body…</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
</div>
