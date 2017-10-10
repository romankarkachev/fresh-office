<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $document array */
/* @var $iterator integer */
/* @var $model common\models\CorrespondencePackages */
?>
                    <tr>
                        <td>
                            <?= $document['name_full'] ?>

                        </td>
                        <td width="135" class="text-center">
                            <?= Html::checkbox($model->formName() . '[tpPad][' . $document['id'] . '][is_provided]', $document['is_provided'], ['data-id' => $document['id']]) ?>

                        </td>
                    </tr>
