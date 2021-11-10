<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\User */

//$this->title = 'Update User: ' . $model->first_name . " " . $model->last_name;
$this->title = 'Update Customer';
$this->params['breadcrumbs'][] = ['label' => 'All Customers', 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->first_name . " " . $model->last_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update Customer';
?>
<div class="users-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
