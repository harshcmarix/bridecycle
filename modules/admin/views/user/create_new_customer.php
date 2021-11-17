<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\User */

$this->title = 'Create Customer';
$this->params['breadcrumbs'][] = ['label' => 'Customer', 'url' => ['index-new-customer']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-create">

    <?= $this->render('_form_new_customer', [
        'model' => $model,
    ]) ?>

</div>
