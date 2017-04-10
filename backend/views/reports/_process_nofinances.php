<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use common\models\DirectMSSQLQueries;

/* @var $this yii\web\View */
/* @var $model common\models\ReportNofinances */
?>

<div class="nofinances-process">
    <div class="panel panel-default">
        <div class="panel-heading">Форма обработки</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group field-reportnofinances-processresponsible">
                        <label class="control-label" for="reportnofinances-processresponsible">Ответственный</label>
                        <?= Select2::widget([
                            'id' => 'process-responsible',
                            'name' => 'ReportNofinances[process_responsible]',
                            'data' => DirectMSSQLQueries::arrayMapOfManagersForSelect2(),
                            'theme' => Select2::THEME_BOOTSTRAP,
                        ]) ?>

                    </div>
                </div>
            </div>
            <div class="form-group">
                <?= Html::a('<i class="fa fa-cog"></i> Выполнить обработку', '#', ['id' => 'btn-execute-process', 'class' => 'btn btn-default', 'data' => [
                    'loading-text' => '<i class="fa fa-cog fa-spin fa-lg text-info"></i> Установка нового менеджера...', 'autocomplete' => 'off'
                ]]) ?>

            </div>
        </div>
    </div>

</div>
