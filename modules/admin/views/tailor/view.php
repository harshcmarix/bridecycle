<?php

use yii\helpers\{
    Html,
    url
};
use yii\widgets\DetailView;
use yii\bootstrap\Modal;
use app\models\Tailor;

/* @var $this yii\web\View */
/* @var $model app\models\Tailor */

$this->title = 'View Tailor';
$this->params['breadcrumbs'][] = ['label' => 'Tailors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                [
                    'format' => ['raw'],
                    'enableSorting' => false,
                    'filter' => false,
                    'attribute' => 'shop_image',
                    'value' => function ($model) {
                        $image_path = "";
                        if (!empty($model->shop_image) && file_exists(Yii::getAlias('@tailorShopImageThumbRelativePath') . '/' . $model->shop_image)) {
                            $image_path = Yii::getAlias('@tailorShopImageThumbAbsolutePath') . '/' . $model->shop_image;
                        } else {
                            $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        }
                        Modal::begin([
                            'id' => 'tailorimagemodal_' . $model->id,
                            'header' => '<h3>Tailor Image</h3>',
                            'size' => Modal::SIZE_DEFAULT
                        ]);

                        echo Html::img($image_path, ['width' => '570']);

                        Modal::end();
                        $tailorimagemodal = "tailorimagemodal('" . $model->id . "');";
                        return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $tailorimagemodal, 'height' => '100px', 'width' => '100px']);
                    },
                ],
                [
                    'attribute' => 'name',
                    'value' => function ($model) {
                        $name = '';
                        if ($model instanceof Tailor) {
                            $name = $model->name;
                        }
                        return $name;
                    },
                ],
                [
                    'attribute' => 'shop_name',
                    'value' => function ($model) {
                        $shop_name = '';
                        if ($model instanceof Tailor) {
                            $shop_name = $model->shop_name;
                        }
                        return $shop_name;
                    },
                ],
                [
                    'attribute' => 'address',
                    'value' => function ($model) {
                        $address = '';
                        if ($model instanceof Tailor) {
                            $address = $model->address;
                        }
                        return $address;
                    },
                ],
                [
                    'attribute' => 'mobile',
                    'value' => function ($model) {
                        $mobile = '';
                        if ($model instanceof Tailor) {
                            $mobile = $model->mobile;
                        }
                        return $mobile;
                    },

                ],
            ],
        ]) ?>
    </div>
    <div class="box-footer">
        <p>
            <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        </p>
    </div>
</div>
<script type="text/javascript">
    function tailorimagemodal(id) {
        $('#tailorimagemodal_' + id).modal('show');
    }
</script>
