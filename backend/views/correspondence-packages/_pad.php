<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $pad array */
/* @var $model common\models\CorrespondencePackages */
?>
<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Документ</th>
                        <th class="text-center"><?= Html::a('Предоставлен', '#', ['id' => 'checkAllDocuments', 'class' => 'link-ajax', 'title' => 'Выделить все']) ?></th>
                    </tr>
                </thead>
                <tbody>
<?php
                    $iterator = 0;
                    foreach ($pad as $document) {
                        echo $this->render('_row_document', [
                            'model' => $model,
                            'document' => $document,
                            'iterator' => $iterator,
                        ]);
                        $iterator++;
                    }
                    ?>
                </tbody>
            </table>
