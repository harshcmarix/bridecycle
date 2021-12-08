<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\SubAdmin;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\SubAdmin */

$this->title = 'View Sub Admin';
$this->params['breadcrumbs'][] = ['label' => 'Sub Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="sub-admin-view">

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [

                    [
                        'attribute' => 'first_name',
                        'value' => function ($model) {
                            $first_name = '';
                            if ($model instanceof SubAdmin) {
                                $first_name = $model->first_name;
                            }
                            return $first_name;
                        },
                    ],
                    [
                        'attribute' => 'last_name',
                        'value' => function ($model) {
                            $last_name = '';
                            if ($model instanceof SubAdmin) {
                                $last_name = $model->last_name;
                            }
                            return $last_name;
                        },
                    ],
                    [
                        'attribute' => 'email',
                        'value' => function ($model) {
                            $email = '';
                            if ($model instanceof SubAdmin) {
                                $email = $model->email;
                            }
                            return $email;
                        },
                    ],
                    [
                        'attribute' => 'mobile',
                        'value' => function ($model) {
                            $mobile = '';
                            if ($model instanceof SubAdmin) {
                                $mobile = $model->mobile;
                            }
                            return $mobile;
                        },
                    ],
                    [
                        'attribute' => 'user_type',
                        'value' => function ($model) {
                            $user_type = '';
                            if ($model instanceof SubAdmin) {
                                $user_type = $model->user_type;
                            }
                            return $user_type;
                        },
                    ],
                ],
            ]) ?>

            <p>
                <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>

        </div>

    </div>
</div>