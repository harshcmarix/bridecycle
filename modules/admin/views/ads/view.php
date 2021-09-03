<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Ads */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Ads', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="ads-view">


            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    //'id',
                    'title',
                    [
                        'format' => ['raw'],
                        'attribute' => 'image',
                        'value' => function ($model) {
                            $image_path = "";
                            if (!empty($model->image) && file_exists(Yii::getAlias('@adsImageThumbRelativePath') . '/' . $model->image)) {
                                $image_path = Yii::getAlias('@adsImageThumbAbsolutePath') . '/' . $model->image;
                            } else {
                                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            }
                            \yii\bootstrap\Modal::begin([
                                'id' => 'adsmodal_' . $model->id,
                                'header' => '<h3>Ads Image</h3>',
                                'size' => \yii\bootstrap\Modal::SIZE_DEFAULT
                            ]);

                            echo Html::img($image_path, ['width' => '570']);

                            \yii\bootstrap\Modal::end();
                            $adsmodal = "adsmodal('" . $model->id . "');";
                            return Html::img($image_path, ['alt' => 'some', 'class' => '', 'onclick' => $adsmodal, 'height' => '100px', 'width' => '100px']);
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    'url:ntext',
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            $status = "";
                            if ($model->status == \app\models\Ads::STATUS_INACTIVE) {
                                $status = "Inactive";
                            } elseif ($model->status == \app\models\Ads::STATUS_ACTIVE) {
                                $status = "Active";
                            }
                            return $status;
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
//                    'created_at',
//                    'updated_at',
                ],
            ]) ?>
            <p>
                <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>
        </div>

    </div>
</div>

<script type="text/javascript">
    function adsmodal(id) {
        $('#adsmodal_' + id).modal('show');
    }
</script>