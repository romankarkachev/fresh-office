<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use common\models\DirectMSSQLQueries;

/* @var $this yii\web\View */
/* @var $model common\models\ReportEmptycustomers */
?>

<div class="emptycustomers-process">
    <div class="panel panel-default">
        <div class="panel-heading">Форма обработки</div>
        <div class="panel-body">
            <div class="form-group">
                <?= Html::a('<i class="fa fa-cog"></i> Выполнить обработку', '#', ['id' => 'btn-execute-process', 'class' => 'btn btn-danger',
                    'data' => [
                        'loading-text' => '<i class="fa fa-cog fa-spin fa-lg text-info"></i> Удаление выбранных контрагентов...',
                        'autocomplete' => 'off',
                    ]
                ]) ?>

            </div>
        </div>
    </div>

</div>
