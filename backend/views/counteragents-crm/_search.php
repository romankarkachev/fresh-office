<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use backend\controllers\CounteragentsCrmController;

/* @var $this yii\web\View */
/* @var $model common\models\foCompanySearch */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="fo-company-search">
    <?php $form = ActiveForm::begin([
        'action' => CounteragentsCrmController::URL_ROOT_AS_ARRAY,
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'ID_MANAGER')->widget(Select2::className(), [
                        'data' => \common\models\foManagers::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-10">
                    <?= $form->field($model, 'searchEntire')->textInput(['placeholder' => 'Введите значение поиска по всем полям']) ?>

                </div>
            </div>


    <?php // echo $form->field($model, 'OKPO') ?>

    <?php // echo $form->field($model, 'INN') ?>

    <?php // echo $form->field($model, 'COMPANY_NAME') ?>

    <?php // echo $form->field($model, 'ADRES') ?>

    <?php // echo $form->field($model, 'CITY') ?>

    <?php // echo $form->field($model, 'ID_MANAGER') ?>

    <?php // echo $form->field($model, 'ID_OFFICE') ?>

    <?php // echo $form->field($model, 'DATA_INPUT') ?>

    <?php // echo $form->field($model, 'ID_VID_COMPANY') ?>

    <?php // echo $form->field($model, 'ROD_DEYATEL') ?>

    <?php // echo $form->field($model, 'id_group_company') ?>

    <?php // echo $form->field($model, 'URL_COMPANY') ?>

    <?php // echo $form->field($model, 'ID_CH') ?>

    <?php // echo $form->field($model, 'PUBLIC_COMPANY') ?>

    <?php // echo $form->field($model, 'DOP_INF') ?>

    <?php // echo $form->field($model, 'YUR_FIZ') ?>

    <?php // echo $form->field($model, 'ID_LIST_STATUS_COMPANY') ?>

    <?php // echo $form->field($model, 'INFORM_IN_COMPANY') ?>

    <?php // echo $form->field($model, 'DR_COMPANY') ?>

    <?php // echo $form->field($model, 'PROF_HOLIDAY') ?>

    <?php // echo $form->field($model, 'MANAGER_NAME_CREATER_COMPANY') ?>

    <?php // echo $form->field($model, 'COUNTRY_COMPANY') ?>

    <?php // echo $form->field($model, 'FORM_SOBST_COMPANY') ?>

    <?php // echo $form->field($model, 'TRASH') ?>

    <?php // echo $form->field($model, 'FAM_FIZ') ?>

    <?php // echo $form->field($model, 'NAME_FIZ') ?>

    <?php // echo $form->field($model, 'OTCH_FIZ') ?>

    <?php // echo $form->field($model, 'FAM_LAT_FIZ') ?>

    <?php // echo $form->field($model, 'NAME_LAT_FIZ') ?>

    <?php // echo $form->field($model, 'REGION') ?>

    <?php // echo $form->field($model, 'MESTO_RABOT_FIZ') ?>

    <?php // echo $form->field($model, 'DOLGNOST_RABOT_FIZ') ?>

    <?php // echo $form->field($model, 'ADDRESS_RABOT_FIZ') ?>

    <?php // echo $form->field($model, 'POL_ANKT') ?>

    <?php // echo $form->field($model, 'SEM_POLOJ_ANKT') ?>

    <?php // echo $form->field($model, 'M_RJD_ANKT') ?>

    <?php // echo $form->field($model, 'COUNTRY_RJD_ANKT') ?>

    <?php // echo $form->field($model, 'GRAJD_ANKT') ?>

    <?php // echo $form->field($model, 'PASPORT_LOCAL_NUMBER') ?>

    <?php // echo $form->field($model, 'PASPORT_LOCAL_SER') ?>

    <?php // echo $form->field($model, 'PASPORT_LOCAL_DATE') ?>

    <?php // echo $form->field($model, 'PASPORT_LOCAL_KEM') ?>

    <?php // echo $form->field($model, 'PASPORT_LOCAL_NUMB_PODRAZDEL') ?>

    <?php // echo $form->field($model, 'PASPORT_ZAGRAN_TIP') ?>

    <?php // echo $form->field($model, 'PASPORT_ZAGRAN_NUMBER') ?>

    <?php // echo $form->field($model, 'PASPORT_ZAGRAN_SER') ?>

    <?php // echo $form->field($model, 'PASPORT_ZAGRAN_DATE') ?>

    <?php // echo $form->field($model, 'PASPORT_ZAGRAN_FINAL') ?>

    <?php // echo $form->field($model, 'PASPORT_ZAGRAN_KEM') ?>

    <?php // echo $form->field($model, 'MANAGER_TRASH') ?>

    <?php // echo $form->field($model, 'DATE_TRASH') ?>

    <?php // echo $form->field($model, 'CODE_1C') ?>

    <?php // echo $form->field($model, 'MARKER_ON') ?>

    <?php // echo $form->field($model, 'ID_MANAGER_MARKER') ?>

    <?php // echo $form->field($model, 'MARKER_DESCRIPTION') ?>

    <?php // echo $form->field($model, 'ID_CATEGORY') ?>

    <?php // echo $form->field($model, 'ID_AGENT') ?>

    <?php // echo $form->field($model, 'ADD_forma_oplati') ?>

    <?php // echo $form->field($model, 'ADD_date_finish') ?>

    <?php // echo $form->field($model, 'ADD_numb_dogovor') ?>

    <?php // echo $form->field($model, 'ADD_crm_control') ?>

    <?php // echo $form->field($model, 'ADD_who') ?>

    <?php // echo $form->field($model, 'IS_INTERNAL') ?>

    <?php // echo $form->field($model, 'ADD_KOD1C_eko_korp') ?>

    <?php // echo $form->field($model, 'ADD_KOD1C_new') ?>

    <?php // echo $form->field($model, 'ADD_KOD1C_general2') ?>

    <?php // echo $form->field($model, 'ADD_KOD1C_logistika') ?>

    <?php // echo $form->field($model, 'ADD_KOD1C_cuop') ?>

    <?php // echo $form->field($model, 'ADD_dog_orig') ?>

    <?php // echo $form->field($model, 'ADD_scan_dog') ?>

    <?php // echo $form->field($model, 'ADD_days_post') ?>

    <?php // echo $form->field($model, 'ADD_KOD1C_nok') ?>

    <?php // echo $form->field($model, 'ADD_KOD1C_tmp') ?>

    <?php // echo $form->field($model, 'ADD_KOD_1C_sex') ?>

    <?php // echo $form->field($model, 'ADD_ADD_KOD1C_sex') ?>

    <?php // echo $form->field($model, 'ADD_KOD1C_sex') ?>

            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', CounteragentsCrmController::URL_ROOT_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
