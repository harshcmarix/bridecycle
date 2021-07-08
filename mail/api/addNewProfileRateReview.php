<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $email string user email */
/* @var $body  string the review */
?>
<div class="user-feedback">
    <p> Hello <?= Html::encode($receiver->first_name) ?> <?= Html::encode($receiver->last_name) ?>,</p>
    <br/>
    <p><?= Html::encode($message) ?></p>
    <br/>

    <p>Thanks,</p>
    <!--    <p>--><?php //echo Html::encode(Yii::$app->name) ?><!--</p>-->
    <p><?= Html::encode("Bride Cycle") ?></p>
</div>
