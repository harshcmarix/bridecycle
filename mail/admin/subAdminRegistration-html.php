
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
    <p>Thank you for registering in <?= Html::encode(Yii::$app->name) ?>.Please find below your credential.</p>
    <br />
    <p><strong>Username:</strong> <?= Html::encode($model->email) ?></p>
    <p><strong>Password:</strong> <?= Html::encode($pwd) ?></p>
    <br /><br />
    <p>Thanks,</p>
    <p><?= Html::encode(Yii::$app->name) ?></p>
</div>
