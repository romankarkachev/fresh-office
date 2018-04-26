<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $loadType array */
/* @var $model common\models\Transport */
?>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>
                                <?= Html::checkbox($model->formName() . '[tpLoadTypes][' . $loadType['id'] . '][is_provided]', $loadType['is_provided'], ['data-id' => $loadType['id']]) ?>

                                <?= $loadType['name'] ?>

                            </label>
                        </div>
                    </div>
