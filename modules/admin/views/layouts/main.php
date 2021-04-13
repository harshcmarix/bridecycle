<?php

use yii\bootstrap\{Nav,NavBar};
use yii\helpers\Html;
use app\widgets\Alert;
use \kartik\growl\Growl;
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
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'Home', 'url' => ['/admin/site/index']],
            Yii::$app->user->isGuest ? (
            ['label' => 'Login', 'url' => ['/admin/site/login']]
            ) : (
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
            p($flash_messages);
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
