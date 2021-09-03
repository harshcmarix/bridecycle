<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model app\models\DressType */

$this->title = "View Dress Type";
$this->params['breadcrumbs'][] = ['label' => 'Dress Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="box box-default">

    <div class="box-header"></div>
    <div class="box-body">

        <!--    <p>-->
        <!--        --><?php //echo Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <!--        --><?php //echo Html::a('Delete', ['delete', 'id' => $model->id], [
        //            'class' => 'btn btn-danger',
        //            'data' => [
        //                'confirm' => 'Are you sure you want to delete this item?',
        //                'method' => 'post',
        //            ],
        //        ]) ?>
        <!--    </p>-->

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'name',
                [
                    'format' => ['raw'],
                    'attribute' => 'image',
                    'value' => function ($model) {
                        $image_path = "";
                        if (!empty($model->image) && file_exists(Yii::getAlias('@dressTypeImageThumbRelativePath') . '/' . $model->image)) {
                            $image_path = Yii::getAlias('@dressTypeImageThumbAbsolutePath') . '/' . $model->image;
                        } else {
                            $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        }
                        Modal::begin([
                            'id' => 'dresstypemodal_' . $model->id,
                            'header' => '<h3>Dress Type Icon</h3>',
                            'size' => Modal::SIZE_SMALL
                        ]);

                        echo Html::img($image_path, ['width' => '50']);

                        Modal::end();
                        $dresstypemodal = "dresstypemodal('" . $model->id . "');";
                        return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $dresstypemodal, 'height' => '50px', 'width' => '50px']);
                    },
                    'header' => '',
                    'headerOptions' => ['class' => 'kartik-sheet-style']
                ],
            ],
        ]) ?>
    </div>
    <div class="box-footer">
        <p>
            <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
        </p>
    </div>
</div>

<script type="text/javascript">
    function dresstypemodal(id) {
        $('#dresstypemodal_' + id).modal('show');
    }
</script>
