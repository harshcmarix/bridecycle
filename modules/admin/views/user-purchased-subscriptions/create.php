<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\UserPurchasedSubscriptions */

$this->title = 'Create User Purchased Subscriptions';
$this->params['breadcrumbs'][] = ['label' => 'User Purchased Subscriptions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-purchased-subscriptions-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
