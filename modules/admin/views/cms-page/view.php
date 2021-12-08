<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use app\models\CmsPage;

/* @var $this yii\web\View */
/* @var $model app\models\CmsPage */

$this->title = 'View Cms Page';
$this->params['breadcrumbs'][] = ['label' => 'Cms Pages', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="cms-page-view">

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'attribute' => 'title',
                        'value' => function ($model) {
                            $title = '';
                            if ($model instanceof CmsPage) {
                                $title = $model->title;
                            }
                            return $title;
                        },
                    ],
                    [
                        'attribute' => 'description',
                        'value' => function ($model) {
                            $description = '';
                            if ($model instanceof CmsPage) {
                                $description = $model->description;
                            }
                            return $description;
                        },
                        'format' => ['raw'],
                        'filter' => false,
                    ],
                ],
            ]) ?>

            <p>
                <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>

        </div>
    </div>
</div>