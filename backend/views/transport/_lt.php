<?php

/* @var $this yii\web\View */
/* @var $loadTypes array */
/* @var $model common\models\Transport */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="row">
<?php
                    foreach ($loadTypes as $loadType) {
                        echo $this->render('_row_lt', [
                            'model' => $model,
                            'loadType' => $loadType,
                        ]);
                    }
                    ?>
            </div>
