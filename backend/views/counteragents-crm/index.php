<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\CounteragentsCrmController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\foCompanySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = CounteragentsCrmController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = CounteragentsCrmController::ROOT_LABEL;
?>
<div class="fo-company-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?php if (Yii::$app->user->can('root')): ?>
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?php endif; ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'ID_COMPANY',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '30'],
            ],
            //'OKPO',
            //'INN',
            'COMPANY_NAME',
            [
                'attribute' => 'managerName',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '180'],
            ],
            //'ADRES',
            //'CITY',
            //'ID_MANAGER',
            //'ID_OFFICE',
            //'DATA_INPUT',
            //'ID_VID_COMPANY',
            //'ROD_DEYATEL',
            //'id_group_company',
            //'URL_COMPANY:url',
            //'ID_CH',
            //'PUBLIC_COMPANY',
            //'DOP_INF',
            //'YUR_FIZ',
            //'ID_LIST_STATUS_COMPANY',
            //'INFORM_IN_COMPANY',
            //'DR_COMPANY',
            //'PROF_HOLIDAY',
            //'MANAGER_NAME_CREATER_COMPANY',
            //'COUNTRY_COMPANY',
            //'FORM_SOBST_COMPANY',
            //'TRASH',
            //'FAM_FIZ',
            //'NAME_FIZ',
            //'OTCH_FIZ',
            //'FAM_LAT_FIZ',
            //'NAME_LAT_FIZ',
            //'REGION',
            //'MESTO_RABOT_FIZ',
            //'DOLGNOST_RABOT_FIZ',
            //'ADDRESS_RABOT_FIZ',
            //'POL_ANKT',
            //'SEM_POLOJ_ANKT',
            //'M_RJD_ANKT',
            //'COUNTRY_RJD_ANKT',
            //'GRAJD_ANKT',
            //'PASPORT_LOCAL_NUMBER',
            //'PASPORT_LOCAL_SER',
            //'PASPORT_LOCAL_DATE',
            //'PASPORT_LOCAL_KEM',
            //'PASPORT_LOCAL_NUMB_PODRAZDEL',
            //'PASPORT_ZAGRAN_TIP',
            //'PASPORT_ZAGRAN_NUMBER',
            //'PASPORT_ZAGRAN_SER',
            //'PASPORT_ZAGRAN_DATE',
            //'PASPORT_ZAGRAN_FINAL',
            //'PASPORT_ZAGRAN_KEM',
            //'MANAGER_TRASH',
            //'DATE_TRASH',
            //'CODE_1C',
            //'MARKER_ON',
            //'ID_MANAGER_MARKER',
            //'MARKER_DESCRIPTION',
            //'ID_CATEGORY',
            //'ID_AGENT',
            //'ADD_forma_oplati',
            //'ADD_date_finish',
            //'ADD_numb_dogovor',
            //'ADD_crm_control',
            //'ADD_who',
            //'IS_INTERNAL',
            //'ADD_KOD1C_eko_korp',
            //'ADD_KOD1C_new',
            //'ADD_KOD1C_general2',
            //'ADD_KOD1C_logistika',
            //'ADD_KOD1C_cuop',
            //'ADD_dog_orig',
            //'ADD_scan_dog',
            //'ADD_days_post',
            //'ADD_KOD1C_nok',
            //'ADD_KOD1C_tmp',
            //'ADD_KOD_1C_sex',
            //'ADD_ADD_KOD1C_sex',
            //'ADD_KOD1C_sex',
            [
                'class' => 'backend\components\grid\ActionColumn',
                'template' => '{compose-edf} {compose-task} {update} {delete}',
                'buttons' => [
                    'compose-edf' => function ($url, $model) {
                        return Html::a('<i class="fa fa-file-text-o"></i>', $url, ['title' => 'Составить электронный документ на этого контрагента', 'class' => 'btn btn-xs btn-default']);
                    },
                    'compose-task' => function ($url, $model) {
                        return Html::a('<i class="fa fa-clock-o"></i>', $url, ['title' => 'Составить задачу на этого контрагента', 'class' => 'btn btn-xs btn-default']);
                    },
                ],
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root'),
                ],
                'options' => ['width' => '130'],
            ],
        ],
    ]); ?>

</div>
