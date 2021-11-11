<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\User */

$this->title = 'Update New Customer';
$this->params['breadcrumbs'][] = ['label' => 'New Customers', 'url' => ['index-new-customer']];
$this->params['breadcrumbs'][] = 'Update New Customer';
?>

<div class="users-update">

    <?= $this->render('_form_new_customer', [
        'model' => $model,
    ]) ?>

</div>
