<?php

use yii\bootstrap\{Nav,NavBar};
use yii\helpers\Html;
use app\widgets\Alert;
use kartik\growl\Growl;
use yii\widgets\Breadcrumbs;
use app\modules\admin\assets\AdminAsset;

AdminAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?php echo Yii::$app->name ?> - Admin | <?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);

    $isVisible = false;
    if(!Yii::$app->user->isGuest){
           $isVisible = true;
    }
   
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            // ['label' => 'Home', 'url' => ['/admin/site/index']],
            ['label' => 'Sub admin', 'url' => ['/admin/sub-admin'],'visible'=>$isVisible,'options'=>['class'=>(Yii::$app->controller->id =='sub-admin')? 'active' :'']],
            ['label' => 'User', 'url' => ['/admin/user'],'visible'=>$isVisible,'options'=>['class'=>(Yii::$app->controller->id =='user')? 'active' :'']],
            ['label' => 'Category', 'url' => ['/admin/product-category'],'visible'=>$isVisible,'options'=>['class'=>(Yii::$app->controller->id =='product-category')? 'active' :'']],
            ['label' => 'Brand', 'url' => ['/admin/brand'],'visible'=>$isVisible,'options'=>['class'=>(Yii::$app->controller->id =='brand')? 'active' :'']],
            ['label' => 'Product', 'url' => ['/admin/product'],'visible'=>$isVisible,'options'=>['class'=>(Yii::$app->controller->id =='product')? 'active' :'']],
            ['label' => 'Promo code', 'url' => ['/admin/promo-code'],'visible'=>$isVisible,'options'=>['class'=>(Yii::$app->controller->id =='promo-code')? 'active' :'']],
            ['label' => 'Order', 'url' => ['/admin/order'],'visible'=>$isVisible,'options'=>['class'=>(Yii::$app->controller->id =='order')? 'active' :'']],
            ['label' => 'Subscription', 'url' => ['/admin/subscription'],'visible'=>$isVisible,'options'=>['class'=>(Yii::$app->controller->id =='subscription')? 'active' :'']],
            ['label' => 'Content', 'url' => ['/admin/cms-page'],'visible'=>$isVisible,'options'=>['class'=>(Yii::$app->controller->id =='cms-page')? 'active' :'']],
            ['label' => 'Settings', 'url' => ['/admin/setting'],'visible'=>$isVisible,'options'=>['class'=>(Yii::$app->controller->id =='setting')? 'active' :'']],
            ['label' => 'Banner', 'url' => ['/admin/banner'],'visible'=>$isVisible,'options'=>['class'=>(Yii::$app->controller->id =='banner')? 'active' :'']],
            ['label' => 'Tailor', 'url' => ['/admin/tailor'],'visible'=>$isVisible,'options'=>['class'=>(Yii::$app->controller->id =='tailor')? 'active' :'']],
            Yii::$app->user->isGuest ? (
            ['label' => 'Login', 'url' => ['/admin/site/login']]
            ) :
            (
                '<li>'
                . Html::beginForm(['/admin/site/logout'], 'post')
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->first_name . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            )
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?php
        $flash_messages = Yii::$app->session->getAllFlashes();
        if (!empty($flash_messages)) {
            // p($flash_messages);
            foreach ($flash_messages as $flash_message_type => $message) {
                echo Growl::widget([
                    'type' => $flash_message_type,
                    'icon' => 'glyphicon glyphicon-ok-sign',
                    'title' => ($flash_message_type == 'danger') ? ucfirst($flash_message_type = "error") : ucfirst($flash_message_type),
                    'showSeparator' => true,
                    'body' => $message
                ]);
            }
        }
        ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>
        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
