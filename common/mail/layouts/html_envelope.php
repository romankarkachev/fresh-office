<?php

use yii\helpers\Html;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */
/* @var $model \common\models\PrintEnvelopeForm */

$customerName = '';
$cp = $model->cp;
$customer = \common\models\foCompany::findOne($cp->fo_id_company);
if ($customer) {
    $customerName = $customer->COMPANY_NAME;
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<table>
    <tr>
        <td><?= $model->organisation->name_short ?><br /><?= $model->organisation->address_f ?></td>
        <td></td>
    </tr>
    <tr>
        <td style="padding-top: 600px;"></td>
        <td>
            <?= $customerName ?>
            <?php if (!empty($cp->contact_person)): ?><br /><?= $cp->contact_person ?><?php endif; ?>
            <br /><?= !empty($cp->address->address_m) ? $cp->address->address_m : $cp->address->src_address ?>
            <?php if (!empty($cp->address->zip_code)): ?><br /><?= $cp->address->zip_code ?><?php endif; ?>
        </td>
    </tr>
</table>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
