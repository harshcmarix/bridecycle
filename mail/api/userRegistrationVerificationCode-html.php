
<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $first_name  string user name*/
/* @var $email string user email */
/* @var $body  string the review */
?>
<div class="user-feedback">
    <p> Hello <?= Html::encode($model->first_name) ?> <?= Html::encode($model->last_name) ?>,</p>
    <br />
    <p>Thank you for registering in <?= Html::encode(Yii::$app->name) ?>.Please enter below your varification code to verify your shop owner profile.</p>
    <br />
    <p><strong>Verification code:</strong> <?= Html::encode($model->verification_code) ?></p>

    <br /><br />
    <p>Thanks,</p>
    <p><?= Html::encode(Yii::$app->name) ?></p>
</div>
