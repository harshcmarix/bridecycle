<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Subscription */

$this->title = 'Update Subscription';
$this->params['breadcrumbs'][] = ['label' => 'Subscriptions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="subscription-update">

    <?= $this->render('_form', [
        'model' => $model,
        'subscription_status'=>$subscription_status,
    ]) ?>

</div>
