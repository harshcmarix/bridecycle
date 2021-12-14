<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Subscription */

$this->title = 'Create Subscription';
$this->params['breadcrumbs'][] = ['label' => 'Subscriptions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="subscription-create">

    <?= $this->render('_form', [
        'model' => $model,
        'subscription_status' => $subscription_status,
    ]) ?>

</div>
